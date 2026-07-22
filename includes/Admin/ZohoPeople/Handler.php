<?php

namespace BitCode\WELZP\Admin\ZohoPeople;

use BitCode\WELZP\Core\Util\HttpHelper;
use BitCode\WELZP\Core\Database\IntegrationModel;
use BitCode\WELZP\Core\Database\ZohoPeoplesEmployeesModel;
use BitCode\WELZP\Core\Database\FormDetailsModel;

final class Handler
{
    private static $_integrationModel;
    private static $_zohoPeoplesEmployeesModel;
    private static $_formDetailsModel;
    private static $data = null;

    const ANALYTICS_ROWS_URL = 'https://analyticsapi.zoho.com/restapi/v2/workspaces/1660248000000929001/views/1660248000012298002/rows';
    const ANALYTICS_ORG_ID   = '663268259';

    public function __construct()
    {
        self::$_integrationModel = new IntegrationModel();
        self::$_zohoPeoplesEmployeesModel = new ZohoPeoplesEmployeesModel();
        self::$_formDetailsModel = new FormDetailsModel();
        $authResponse = $this->getAuthDetails();
        if (count((array) $authResponse) > 0) {
            self::$data = json_decode($authResponse->auth_details);
        }
    }

    //Generate Zoho Analytics access token
    public function analyticsGenerateToken()
    {
        $requestParams = [
            'grant_type'    => 'refresh_token',
            'client_id'     => '1000.51OLRVT0A1KT041TJGYG39CC3PW6FA',
            'client_secret' => 'f0e0c77ec75569b17ede4b7ee61b9a6b00faf520bc',
            'refresh_token' => '1000.e89678bfb146d4bd59bf96e795c3f616.89d50371767b0ad325045a2d0a8f7fc6',
        ];
        $refreshToken = HttpHelper::post('https://accounts.zoho.com/oauth/v2/token', $requestParams);
        return $refreshToken;
    }


    protected static function refreshAccessToken($apiData)
    {
        if (
            empty($apiData->dataCenter)
            || empty($apiData->clientId)
            || empty($apiData->clientSecret)
            || empty($apiData->tokenDetails)
        ) {
            return false;
        }
        $tokenDetails = $apiData->tokenDetails;

        $dataCenter = $apiData->dataCenter;
        $apiEndpoint = "https://accounts.zoho.{$dataCenter}/oauth/v2/token";
        $requestParams = [
            'grant_type'    => 'refresh_token',
            'client_id'     => $apiData->clientId,
            'client_secret' => $apiData->clientSecret,
            'refresh_token' => $tokenDetails->refresh_token,
        ];

        $apiResponse = HttpHelper::post($apiEndpoint, $requestParams);
        if (is_wp_error($apiResponse) || !empty($apiResponse->error)) {
            return false;
        }
        $tokenDetails->generates_on = \time();
        $tokenDetails->access_token = $apiResponse->access_token;
        return $tokenDetails;
    }

    protected static function saveRefreshedToken($integrationID, $data)
    {
        if (empty($integrationID)) {
            return;
        }
        $result = static::$_integrationModel->update(
            [
                'auth_details' => wp_json_encode($data)
            ],
            [
                'id' => $integrationID
            ]
        );
        return $result;
    }

    //Ensure the Zoho access token is valid (refresh + persist when expired) and return the auth header
    private function authHeader()
    {
        $requestData = self::$data;

        if (!is_object($requestData) || empty($requestData->tokenDetails)) {
            wp_send_json_error(
                __('Zoho authorization details not found', 'bitwelzp'),
                400
            );
        }

        if ((intval($requestData->tokenDetails->generates_on) + (55 * 60)) < time()) {
            $refreshedToken = $this::refreshAccessToken($requestData);
            if ($refreshedToken) {
                $requestData->tokenDetails = $refreshedToken;
                if (!empty($requestData->integrationId)) {
                    $this::saveRefreshedToken($requestData->integrationId, $requestData);
                }
            } else {
                wp_send_json_error(
                    __('Failed to refresh access token', 'bitwelzp'),
                    400
                );
            }
        }

        return ['Authorization' => "Zoho-oauthtoken {$requestData->tokenDetails->access_token}"];
    }

    //Exchange the OAuth authorization code for Zoho access/refresh tokens
    public function generateToken($data)
    {
        $requestsParams = $data;
        if (
            empty($requestsParams->{'accounts-server'})
            || empty($requestsParams->dataCenter)
            || empty($requestsParams->clientId)
            || empty($requestsParams->clientSecret)
            || empty($requestsParams->redirectURI)
            || empty($requestsParams->code)
        ) {
            wp_send_json_error(
                __(
                    'Requested parameter is empty',
                    'bitforms'
                ),
                400
            );
        }
        $apiEndpoint = \urldecode($requestsParams->{'accounts-server'}) . '/oauth/v2/token';
        $requestParams = [

            'grant_type'    => 'authorization_code',
            'client_id'     => $requestsParams->clientId,
            'client_secret' => $requestsParams->clientSecret,
            'redirect_uri'  => \urldecode($requestsParams->redirectURI),
            'code'          => $requestsParams->code
        ];
        $apiResponse = HttpHelper::post($apiEndpoint, $requestParams);
        if (is_wp_error($apiResponse) || !empty($apiResponse->error)) {
            wp_send_json_error(
                empty($apiResponse->error) ? 'Unknown' : $apiResponse->error,
                400
            );
        }
        $apiResponse->generates_on = \time();
        wp_send_json_success($apiResponse, 200);
    }

    //Shared review fields sent to the Zoho Analytics (Patient Review Data) table
    private function analyticsBaseRow($requestData)
    {
        $phrases = isset($requestData->phrases) ? (array) $requestData->phrases : [];

        return [
            'Employee Id'       => $requestData->employee_id ?? '',
            'Zoho Id'           => $requestData->zoho_id ?? '',
            'Star'              => $requestData->star ?? '',
            'First Name'        => $requestData->fname ?? '',
            'Last Name'         => $requestData->lname ?? '',
            'Phrases'           => implode(', ', $phrases),
            'Title'             => $requestData->title ?? '',
            'Title Description' => $requestData->desc ?? '',
            'Age Range'         => $requestData->age ?? '',
            'Gender'            => $requestData->gender ?? '',
            'Status'            => $requestData->status ?? '',
            //Fill from the standalone field when present, otherwise from the phrases list
            'Empathetic'        => $requestData->empathetic ?? (in_array('Empathetic', $phrases, true) ? 'Empathetic' : ''),
        ];
    }

    //Send a row payload to the Analytics rows endpoint (POST to add, PUT to update)
    private function postAnalyticsRow($columns, $accessToken, $method = 'POST')
    {
        $apiEndpoint = self::ANALYTICS_ROWS_URL . '?CONFIG=' . json_encode($columns);
        $authorizationHeader['Authorization'] = 'Zoho-oauthtoken ' . $accessToken;
        $authorizationHeader['ZANALYTICS-ORGID'] = self::ANALYTICS_ORG_ID;

        if ($method === 'PUT') {
            return HttpHelper::request($apiEndpoint, 'PUT', null, $authorizationHeader);
        }
        return HttpHelper::post($apiEndpoint, null, $authorizationHeader);
    }

    //Push a patient review into Zoho Analytics (Patient Review Data)
    public function insertReviewIntoAnalytics($requestData, $type)
    {
        $lastReviewId = static::$_formDetailsModel->get('id', [], 1, null, 'id', 'DESC');
        $refreshToken = $this->analyticsGenerateToken();

        $data = $this->analyticsBaseRow($requestData);
        $data['Review Id'] = $type === 'insert' ? $lastReviewId[0]->id : $requestData->editRowId;
        $data['Created At'] = date('d M,Y h:i:s');

        $apiResponse = null;
        if ($refreshToken) {
            $apiResponse = $this->postAnalyticsRow(['columns' => $data], $refreshToken->access_token, 'POST');
        }
        return $apiResponse;
    }

    //Sync a plugin-side review edit into Zoho Analytics (Patient Review Data)
    public function updateReviewIntoAnalytics($requestData)
    {
        $data = $this->analyticsBaseRow($requestData);
        $data['Updated At'] = date('d M,Y h:i:s');

        $refreshToken = $this->analyticsGenerateToken();
        $apiResponse = null;
        if ($refreshToken) {
            $columns = ['columns' => $data];
            $columns['criteria'] = "(\"Review Id\"='$requestData->editRowId')";
            $apiResponse = $this->postAnalyticsRow($columns, $refreshToken->access_token, 'PUT');
        }
        return $apiResponse;
    }

    //Save Zoho authorization details to the database
    public function integrationSave($data)
    {
        $result = static::$_integrationModel->insert(
            [
                'auth_details' => wp_json_encode($data)
            ]
        );
        if (is_wp_error($result)) {
            wp_send_json_error('Saving Failed');
        }
        wp_send_json_success($result, 200);
    }


    //Update Zoho authorization details
    public function integrationUpdate($data)
    {
        $result = static::$_integrationModel->update(
            [
                'auth_details' => wp_json_encode($data)
            ],
            [
                'id' => $data->integrationId
            ]
        );
        if (is_wp_error($result)) {
            wp_send_json_error('Updating Failed');
        }
        wp_send_json_success($result, 200);
    }

    //Fetch Zoho authorization details from the database
    public function getAuthDetails()
    {
        $auth_details = static::$_integrationModel->get();
        if (is_wp_error($auth_details)) {
            return (object) [];
        }
        return $auth_details[0];
    }

    //Update Profile_URL / Review_URL fields in Zoho People
    public function updateZohoPeoplesFields($recordId, $profileUrl, $reviewUrl)
    {
        $_apiDomain = "https://people.zoho.com/people/api/forms/json/employee/updateRecord?inputData={Profile_URL:'$profileUrl', Review_URL:'$reviewUrl'}&recordId=$recordId";
        $_defaultHeader = $this->authHeader();

        HttpHelper::get($_apiDomain, [], $_defaultHeader);
    }

    //AJAX: run the sync, return the refreshed clinician list
    public function getPeoplesForms()
    {
        try {
            $this->syncEmployees();

            $all_employees = $this->getAllEmployees();
            wp_send_json_success($all_employees, 200);
        } catch (\Throwable $e) {
            error_log('WELZP: employee sync failed: ' . $e->getMessage());
            wp_send_json_error('Unknown', 400);
        }
    }

    //Fetch + persist clinicians and their profile pages. No JSON output (cron/queue-safe); throws on fetch failure.
    public function syncEmployees()
    {
        $upload_dir = wp_upload_dir();
        $_defaultHeader = $this->authHeader();

        $apiResponse = [];
        $totalEmployees = [];

        while (true) {
            $sIndex = count($totalEmployees) + 1;
            $apiEndpoint = 'https://people.zoho.com/people/api/forms/employee/getRecords?sIndex=' . $sIndex . '&limit=100';
            $apiResponse = HttpHelper::get($apiEndpoint, [], $_defaultHeader);

            if (is_wp_error($apiResponse)) {
                throw new \Exception('Employee records request failed: ' . $apiResponse->get_error_message());
            }

            if (isset($apiResponse->response->errors)) {
                break;
            }

            $pageResult = isset($apiResponse->response->result) ? $apiResponse->response->result : [];

            //Empty page means every record has been read; stop paginating
            if (empty($pageResult)) {
                break;
            }

            $totalEmployees = array_merge($totalEmployees, $pageResult);
        }

        $getAllRiviews = $this->allReviewsQuery();
        $recordId = '';
        $profileUrl = '';
        $reviewUrl = '';

        $employee_details = static::$_zohoPeoplesEmployeesModel->get();
        $cliniciansZohoIds = [];

        if (count($totalEmployees)) {
            if (is_array($employee_details) && count($employee_details)) {
                foreach ($employee_details as $employee) {
                    array_push($cliniciansZohoIds, $employee->zoho_id);
                }
            }

            foreach ($totalEmployees as  $data) {
                foreach ((array) $data as  $employee) {
                    if ($this::isEmployeeActive($employee[0])) {
                        $recordId = $employee[0]->Zoho_ID;
                        $profileUrl = 'https://wellqor.com/' . $employee[0]->FirstName[0] . '' . str_replace(" ", "-", $employee[0]->LastName) . '';
                        $reviewUrl = 'https://wellqor.com/therapist-review-form/?zoho_id=' . $employee[0]->Zoho_ID . '';
                        $headshot_url = $employee[0]->Headshot_downloadUrl;
                        //Zoho supplies the filename; sanitize before it reaches the uploads path
                        $fileName = sanitize_file_name($employee[0]->Headshot);

                        if (!empty($headshot_url) && !empty($fileName)) {
                            $headshot_response = HttpHelper::get($headshot_url, [], $_defaultHeader);

                            if (!is_wp_error($headshot_response) && !empty($headshot_response) && !is_object($headshot_response)) {
                                file_put_contents($upload_dir['basedir'] . '/' . $fileName, $headshot_response);
                            } else {
                                error_log('WELZP: headshot download failed for Zoho_ID ' . $employee[0]->Zoho_ID);
                                $fileName = '';
                            }
                        } else {
                            $fileName = '';
                        }

                        $arraValues = static::getClinicianFormData($employee, $_defaultHeader);
                        $insertData = [
                                'email_Id'                                  => $employee[0]->EmailID,
                                'zoho_id'                                   => $employee[0]->Zoho_ID,
                                'employee_id'                               => $employee[0]->EmployeeID,
                                'headshot_download_url'                     => $fileName,
                                'employee_status'                           => $employee[0]->Employeestatus,
                                'fname'                                     => $employee[0]->FirstName,
                                'lname'                                     => $employee[0]->LastName,
                                'preferred_name_nickname'                   => $employee[0]->Preferred_Name_Nickname,
                                'clinical_title'                            => $employee[0]->Clinical_Title,
                                'medical_qualification'                     => $employee[0]->Degree1,
                                'designation'                               => $employee[0]->Designation,
                                'skills'                                    => !empty($arraValues) ? $arraValues->Clinical_Competencies : '',
                                'advanced_degree_from'                      => $employee[0]->Advanced_Degree_from,
                                'languages'                                 => !empty($arraValues) ? $arraValues->Languages : '',
                                'certifications'                            => !empty($arraValues) ? $arraValues->Clinician_Profile_Treatment_Modalities : '',
                                'cultural_competency'                       => !empty($arraValues) ? $arraValues->Cultural_Competencies1 : '',
                                'public_bio'                                => !empty($arraValues) ? $arraValues->Public_Bio : '',
                                'licensed_in'                               => $employee[0]->Licensed_In,
                                'allow_telehealth_access'                   => $employee[0]->Allow_Telehealth_Access,
                        ];

                        //Update the clinician's row if it exists, otherwise insert a new one
                        if (in_array($employee[0]->Zoho_ID, $cliniciansZohoIds)) {
                            static::$_zohoPeoplesEmployeesModel->update(
                                $insertData,
                                ['zoho_id' => $employee[0]->Zoho_ID]
                            );
                        } else {
                            static::$_zohoPeoplesEmployeesModel->insert($insertData);
                            $cliniciansZohoIds[] = $employee[0]->Zoho_ID;
                        }

                        $post_id = $this->getPostIdRowByZohoId($employee[0]->Zoho_ID);

                        $this::createClinicianProfilePage(
                            $insertData,
                            ($post_id !== null && !empty($post_id->post_id)) ? $post_id->post_id : '',
                            $getAllRiviews
                        );

                        $this->updateZohoPeoplesFields($recordId, $profileUrl, $reviewUrl);
                    }
                }
            };
        }
    }

    //Is the clinician active in Zoho People
    public static function isEmployeeActive($data)
    {
        if ($data->Employeestatus === 'Active' && ($data->Designation === 'Clinical Therapist' || $data->Designation === 'Clinical Director') && $data->Allow_Telehealth_Access === 'true') {
            return true;
        }

        return false;
    }

    //Enrich from the Clinician_Profile form: competencies, languages, modalities, cultural, bio
    public static function getClinicianFormData($employeeData, $_defaultHeader)
    {
        //EmployeeID is embedded in Clinician_Name (e.g. "David - Giella - 1002"); search by it
        $clinicianFormParams = [
            'searchField'   => 'Clinician_Name',
            'searchOperator' => 'Contains',
            'searchText'    => $employeeData[0]->EmployeeID
        ];
        $clinicianFormResponse = HttpHelper::get('https://people.zoho.com/people/api/forms/Clinician_Profile/getRecords?searchParams=' . json_encode($clinicianFormParams) . '', [], $_defaultHeader);
        $arraValues = '';
        $employeeId = $employeeData[0]->EmployeeID;

        if (is_wp_error($clinicianFormResponse)) {
            error_log('WELZP: Clinician_Profile request failed for EmployeeID ' . $employeeId . ': ' . $clinicianFormResponse->get_error_message());
            return $arraValues;
        }

        if (!empty($clinicianFormResponse->response->errors)) {
            error_log('WELZP: Clinician_Profile API error for EmployeeID ' . $employeeId . ': ' . wp_json_encode($clinicianFormResponse->response->errors));
            return $arraValues;
        }

        if (empty($clinicianFormResponse->response->result)) {
            error_log('WELZP: no Clinician_Profile record found for EmployeeID ' . $employeeId);
            return $arraValues;
        }

        //Contains search can mis-match short numeric IDs; prefer an exact Clinician_Name match first
        foreach ($clinicianFormResponse->response->result as $resultRow) {
            $responseData = (array) $resultRow;
            $record = array_values($responseData)[0][0];
            if (isset($record->Clinician_Name) && static::clinicianNameMatchesEmployeeId($record->Clinician_Name, $employeeId)) {
                return $record;
            }
        }

        //No exact match: trust a lone result, but skip when multiple rows collide (avoid wrong clinician's data)
        if (count($clinicianFormResponse->response->result) === 1) {
            error_log('WELZP: no exact Clinician_Name match for EmployeeID ' . $employeeId . ', using single search result');
            $responseData = (array) $clinicianFormResponse->response->result[0];
            return array_values($responseData)[0][0];
        }

        error_log('WELZP: ambiguous Clinician_Profile match for EmployeeID ' . $employeeId . ' (' . count($clinicianFormResponse->response->result) . ' results, none exact), skipping enrichment');
        return $arraValues;
    }

    //Does Clinician_Name (e.g. "David - Giella - 1002") contain this EmployeeID as a part
    private static function clinicianNameMatchesEmployeeId($clinicianName, $employeeId)
    {
        $parts = array_map('trim', explode('-', $clinicianName));
        return in_array((string) $employeeId, $parts, true);
    }

    //Query condition for active clinicians (Active + Clinical Therapist/Director + telehealth)
    private function activeClinicianCondition()
    {
        return [
            'employee_status'         => 'Active',
            'designation'             => ['Clinical Therapist', 'Clinical Director'],
            'allow_telehealth_access' => 'true',
        ];
    }

    //Look up the employee_info row (post_id) for a Zoho record id
    private function getPostIdRowByZohoId($zohoId)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'bitwelzp_zoho_people_employee_info';
        return $wpdb->get_row($wpdb->prepare("SELECT post_id FROM {$table} WHERE zoho_id = %s", $zohoId));
    }

    //Rebuild a single clinician's profile page from the current DB row + reviews
    private function refreshClinicianPage($zohoId)
    {
        $employee = static::$_zohoPeoplesEmployeesModel->get('*', ['zoho_id' => $zohoId]);
        if (is_wp_error($employee) || empty($employee)) {
            return;
        }

        $postIdRow = $this->getPostIdRowByZohoId($zohoId);
        static::createClinicianProfilePage(
            (array) $employee[0],
            ($postIdRow !== null && !empty($postIdRow->post_id)) ? $postIdRow->post_id : '',
            $this->allReviewsQuery()
        );
    }

    //All reviews, newest first (raw model result; callers handle any WP_Error)
    private function allReviewsQuery()
    {
        return static::$_formDetailsModel->get('*', [], null, null, 'id', 'DESC');
    }

    //Fetch clinicians from DB for the frontend
    public function getAllEmployees()
    {

        $all_employees = static::$_zohoPeoplesEmployeesModel->get('*', $this->activeClinicianCondition(), null, null, 'id', 'DESC');

        if (is_wp_error($all_employees)) {
            return  [];
        }

        return $all_employees;
    }

    //Delete clinician permanently from the database, along with their generated profile page
    public function deleteEmployees($Ids)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'bitwelzp_zoho_people_employee_info';
        $deleted = 0;

        foreach ((array) $Ids as $id) {
            $id = absint($id);
            if (!$id) {
                continue;
            }

            $row = $wpdb->get_row($wpdb->prepare("SELECT post_id FROM {$table} WHERE id = %d", $id));
            if ($row !== null && !empty($row->post_id)) {
                wp_delete_post((int) $row->post_id, true);
            }

            if ($wpdb->delete($table, ['id' => $id], ['%d'])) {
                $deleted++;
            }
        }

        wp_send_json_success($deleted);
    }

    //Save patient review data in the database
    public function saveReviews($request)
    {
        if (!is_object($request)) {
            wp_send_json_error('Invalid review payload', 400);
        }

        $request->status = 'pending';

        $result = static::$_formDetailsModel->insert(
            [
                'form_details' => wp_json_encode($request),
                'created_at'   => date('Y-m-d:h:i:sa')
            ]
        );

        $this->insertReviewIntoAnalytics($request, 'insert');

        if (is_wp_error($result)) {
            wp_send_json_error('Data Insertion Failed');
        }

        wp_send_json_success($result, 200);
    }

    //Delete patient review permanently from the database
    public function deleteReviews($Ids)
    {
        global $wpdb;
        $result = '';
        foreach ($Ids as $id) {
            $result = $wpdb->delete($wpdb->prefix . 'bitwelzp_form_details', ['id' => $id]);
        }

        wp_send_json_success($result);
    }

    //Approve pending patient review request
    public function approveReview($id)
    {
        $get_form_details = static::$_formDetailsModel->get('*', ['id' => $id]);
        if (is_wp_error($get_form_details) || empty($get_form_details)) {
            wp_send_json_error('Review not found', 404);
        }
        $new_form_details = json_decode($get_form_details[0]->form_details);

        if ($new_form_details->status === 'pending') {
            $new_form_details->status = 'approved';
        } else {
            $new_form_details->status = 'pending';
        }

        $result = static::$_formDetailsModel->update(
            [
                'form_details' => wp_json_encode($new_form_details),
                'updated_at'   => date('Y-m-d:h:i:sa')
            ],
            [
                'id' => $id
            ]
        );

        if (is_wp_error($result)) {
            wp_send_json_error('Updating Failed');
        }

        if (count($get_form_details)) {
            $new_form_details->editRowId = $id;
            $res = $this->updateReviewIntoAnalytics($new_form_details);
        }

        //Approving/unapproving changes the review counts and highlights baked into the page markup
        if (!empty($new_form_details->zoho_id)) {
            $this->refreshClinicianPage($new_form_details->zoho_id);
        }

        $get_updated_form_details = $this->allReviewsQuery();
        wp_send_json_success($get_updated_form_details, 200);
    }

    //Update patient review
    public function updateReview($requestData)
    {
        $employee_data_by_id = static::$_zohoPeoplesEmployeesModel->get('*', ['zoho_id' => $requestData->inputData->zoho_id], null, null, 'id', 'DESC');
        $employee_name = '';

        if (!is_wp_error($employee_data_by_id)) {
            $employee_name = $employee_data_by_id[0]->fname . ' ' . $employee_data_by_id[0]->lname;
        }

        $requestData->inputData->employee_name = $employee_name;
        $result = static::$_formDetailsModel->update(
            [
                'form_details' => wp_json_encode($requestData->inputData),
                'updated_at'   => date('Y-m-d:h:i:sa')

            ],
            [
                'id' => $requestData->editRowId
            ]
        );


        if (is_wp_error($result)) {
            wp_send_json_error('Updating Failed');
        } else {
            $form_details = $this->allReviewsQuery();
            $requestData->inputData->editRowId = $requestData->editRowId;
            $updateReview = $this->updateReviewIntoAnalytics($requestData->inputData);

            if (isset($updateReview->status) && $updateReview->status !== 'success') {
                $this->insertReviewIntoAnalytics($requestData->inputData, 'update');
            }

            wp_send_json_success($form_details, 200);
        }
    }

    //Fetch reviews from DB for the frontend
    public function get_form_details()
    {
        $all_reviews = $this->allReviewsQuery();
        if (is_wp_error($all_reviews)) {
            return  [];
        }
        return $all_reviews;
    }

    //Persist table column order & visibility, keyed by table name
    public function saveTableColumns($data)
    {
        if (empty($data->table) || !isset($data->columns) || !is_array($data->columns)) {
            wp_send_json_error('Invalid column data', 400);
        }

        $table   = sanitize_key($data->table);
        $columns = [];
        foreach ($data->columns as $col) {
            if (empty($col->id)) {
                continue;
            }
            $columns[] = [
                'id'     => sanitize_text_field($col->id),
                'hidden' => !empty($col->hidden),
            ];
        }

        $saved = get_option('bitwelzp_table_columns', []);
        if (!is_array($saved)) {
            $saved = [];
        }
        $saved[$table] = $columns;
        update_option('bitwelzp_table_columns', $saved, false);

        wp_send_json_success($columns);
    }

    //Handle clinician profile page status
    public function handlePageStatus($id)
    {
        $employee_data_by_id = static::$_zohoPeoplesEmployeesModel->get('*', ['id' => $id], null, null, 'id', 'DESC');
        if (is_wp_error($employee_data_by_id) || empty($employee_data_by_id)) {
            wp_send_json_error('Clinician not found', 404);
        }
        $zoho_id = $employee_data_by_id[0]->zoho_id;
        $status = '';
        $post_id = $this->getPostIdRowByZohoId($zoho_id);

        if ($post_id === null || empty($post_id->post_id)) {
            wp_send_json_error('No profile page exists for this clinician yet', 404);
        }

        if ($employee_data_by_id[0]->page_status === 'inactive' || $employee_data_by_id[0]->page_status === null) {
            $status = 'active';
            wp_update_post(
                [
                    'ID'             => $post_id->post_id,
                    'post_status'    => 'publish',
                ]
            );
        } else {
            $status = 'inactive';
            wp_update_post(
                [
                    'ID'             => $post_id->post_id,
                    'post_status'    => 'draft',
                ]
            );
        }

        $result = static::$_zohoPeoplesEmployeesModel->update(
            ['page_status' => $status],
            ['id' => $id]
        );

        if (is_wp_error($result)) {
            wp_send_json_error('Updating Failed');
        }

        $employee_data = static::$_zohoPeoplesEmployeesModel->get('*', $this->activeClinicianCondition(), null, null, 'id', 'DESC');
        wp_send_json_success($employee_data, 200);
    }

    //Create or update a clinician profile page
    public static function createClinicianProfilePage($data, $id, $getAllReviews)
    {

        global $wpdb;
        $upload_dir = wp_upload_dir();
        $zoho_id = $data['zoho_id'];
        $fname = $data['fname'];
        $lname = $data['lname'];
        $preferred_name_nickname = $data['preferred_name_nickname'];
        $medical_qualification = $data['medical_qualification'];
        $clinical_title = $data['clinical_title'];
        $skills = $data['skills'];
        $advanced_degree_from = $data['advanced_degree_from'];
        $languages = $data['languages'];
        $certifications = $data['certifications'];
        $cultural_competency = $data['cultural_competency'];
        $public_bio = $data['public_bio'];
        $licensed_in = $data['licensed_in'];
        $headshot_download_url = $data['headshot_download_url'];
        $new_headshot_download_url = '';
        if ($headshot_download_url == '') {
            $new_headshot_download_url = 'https://wellqor.com/wp-content/uploads/2021/11/bioPicplaceholder.jpg';
        } else {
            $new_headshot_download_url = $upload_dir['baseurl'] . '/' . $headshot_download_url;
        }
        $skillArray = explode(';', $skills);
        $languagesArray = explode(';', $languages);
        $certificationsArray = explode(';', $certifications);
        $culturalCompetencyArray = explode(';', $cultural_competency);
        $licensedArray = explode(';', $licensed_in);

        $map = function ($a, $f) {
            return join("\n", array_map($f, $a, array_keys($a)));
        };

        $author_id = 1;
        $firstCharfName = substr($fname, 0, 1);
        $slug = $firstCharfName . $lname;
        $title = $fname . ' ' . $lname . ' ' . 'info';

        $reviewsData = [];
        $phrasesArray = [];

        //Treat a failed reviews query as no reviews
        if (is_wp_error($getAllReviews) || !is_array($getAllReviews)) {
            $getAllReviews = [];
        }

        foreach ($getAllReviews as $review) {
            $form_details = json_decode($review->form_details);
            if (!is_object($form_details) || !isset($form_details->zoho_id, $form_details->status)) {
                continue;
            }

            if ($zoho_id == $form_details->zoho_id && $form_details->status == 'approved') {
                $form_details->created_at = $review->created_at;
                $reviewsData[] = $form_details;
                //Cast handles reviews with a missing phrases key
                $phrasesArray = array_merge($phrasesArray, (array) ($form_details->phrases ?? []));
            }
        }

        $arr_freq = array_count_values($phrasesArray);
        arsort($arr_freq);
        $show_phrases = array_keys($arr_freq);
        $totalVerifiedReviews = count($reviewsData);

        //Render up to four most-frequent phrases
        $reviewHighlights = '';
        foreach (array_slice($show_phrases, 0, 4) as $topPhrase) {
            $reviewHighlights .= '<span>' . esc_html($topPhrase) . '</span>';
        }

        $page_status = $wpdb->get_row($wpdb->prepare("SELECT page_status FROM {$wpdb->prefix}bitwelzp_zoho_people_employee_info WHERE zoho_id = %s", $zoho_id));

        if ($page_status == null) {
            static::$_zohoPeoplesEmployeesModel->update(
                ['page_status' => 'active'],
                ['zoho_id' => $zoho_id]
            );
        }

        //Inactive pages stay as drafts; everything else publishes
        $currentPageStatus = ($page_status !== null && isset($page_status->page_status)) ? $page_status->page_status : 'active';
        $postStatus = $currentPageStatus === 'inactive' ? 'draft' : 'publish';

        $showAllReviewsBtn = '';

        if ($totalVerifiedReviews > 0) {
            $showAllReviewsBtn = "   <div class='all-reviews' id='show-all-reviews-btn'>
<a href='https://wellqor.com/show-all-reviews?zoho_id={$zoho_id}' >Read More</a>
                     </div>";
        }

        //You can find the Style in the Code Snippets(plugin) Footer
        $content = <<<HTML
          <div class="employee-details">
<div class="profile-heading">
<div class="container">
    <div class="profile-img">
        <img src='https://wellqor.com/wp-content/uploads/2023/11/animated_petal_bulletsArtboard-2-copy-7.svg' alt='flower background'>
        <img alt='$preferred_name_nickname $lname' src='$new_headshot_download_url'>
    </div>
    <div class="title">
        <div class="name">
            <h1><span>$preferred_name_nickname</span> <span>$lname, </span><span>$medical_qualification</span></h1>
        </div>
        <div class="designation">
        $clinical_title
        </div>
    </div>
</div>
</div>
		  

    <div class="profile-content">
		<div class="container">
    <div class="left">
        <div class="specialities">
            <h4>Specialities</h4>
            <ul>
            	       {$map($skillArray, function ($skill) {
            return "
                <li>$skill</li>
 ";
        })}
            </ul>
        </div>

        <div class="certifications">
            <h4>Treatment Modalities</h4>
            <ul>
             {$map($certificationsArray, function ($certification) {
            return "
                <li>$certification</li>
 ";
        })}
            </ul>
        </div>
		
       
        <div class="cultural-competencies">
            <h4>Cultural Competencies</h4>
            <ul>
                 {$map($culturalCompetencyArray, function ($culturalCompetency) {
            return "
                <li>$culturalCompetency</li>
 ";
        })}
            </ul>
        </div>
		
        <div class="language-spoken">
            <h4>Languages Spoken</h4>
            <ul>
               {$map($languagesArray, function ($language) {
            return "
                <li>$language</li>
 ";
        })}
            </ul>
        </div>
		

    </div>
    <div class="right">
        <div class="professional-bio">
            <h4>Professional Bio</h4>
                      <span>$public_bio</span>

        </div>

        <div class="education-license">
            <div class="education">
                <h4>Education</h4>
               <span>$advanced_degree_from</span>
            </div>
            <div class="license">
                <h4>Licensed in</h4>
           {$map($licensedArray, function ($license_in) {
            return "
            <span>$license_in</span>
 ";
        })}
            </div>
        </div>
        <div class="review-section">
  <div class="patient-satisfaction">
<div class="verified-reviews">
<h4>Patient Satisfaction</span></h4>
<span> $totalVerifiedReviews Verified Reviews</span>
<div class="stars">
<img src="https://wellqor.com/wp-content/uploads/2023/11/rating.png" />
</div>
</div>
<div class="highlights">

<h4><span>Review Highlights</span></h4>
$reviewHighlights
</div>
</div>
<div class="line"></div>
<div class="featured-FPO">
<div class="featured-content-FPO">
<h4><span>Featured Reviews</span></h4>
{$map($reviewsData, function ($reviews) {
            return "
           <a href='https://wellqor.com/show-all-reviews?zoho_id={$reviews->zoho_id}'>
                             <div class='reviews-list'>
                                <div class='reviews-accordion'> 
                                    <div class='reviewer-info'>
                                        <span>$reviews->gender</span>, <span>$reviews->age (Verified) on $reviews->created_at<span>

                                    </div>
                                        
                                        <div class='title'>
                                            <img src='https://wellqor.com/wp-content/uploads/2023/11/rating.png' />
                                            <span> $reviews->title</span>
                                        </div>
                                </div>
                    
                            </div>
</a>

                         
";
        })}
        
</div>
        
 $showAllReviewsBtn
        
 

</div>
</div>
</div>
</div>
</div>

      <div class="clinical-profile-get-started">
      <h2>Let’s get started!</h2>
      <div class="clinical-profile-get-started-content">
        <div class="clinical-profile-get-started-card">
          <img
            src="https://wellqor.com/wp-content/uploads/2024/11/Group-18141.svg"
            alt="Wellqor get started heart"
          />
          <div class="clinical-profile-title">
            <h3>Tell us about you!</h3>
          </div>
          <div class="clinical-profile-get-started-desc">
            <span>We’ll send you therapist profiles, including their clinical experience, approach, and patient reviews.</span>
          </div>
        </div>

        <div class="clinical-profile-get-started-card">
          <img
            src="https://wellqor.com/wp-content/uploads/2024/11/Group-18142.svg"
            alt="Wellqor get started heart"
          />
          <div class="clinical-profile-title">
            <h3>Get options in a few hours</h3>
          </div>
          <div class="clinical-profile-get-started-desc">
<p>If you prefer providing information over the phone, call (646) 687-4646.</p>
          </div>
        </div> 

        <div class="clinical-profile-get-started-card">
          <img
            src="https://wellqor.com/wp-content/uploads/2024/11/Group-18143.svg"
            alt="Wellqor get started heart"
          />
          <div class="clinical-profile-title">
            <h3>Pick your therapist & time.</h3>
          </div>
          <div class="clinical-profile-get-started-desc">
            <span>Let us know which therapist you like and what appointment slot you prefer.</span>
          </div>
        </div>
      </div>
      <div class="clinical-profile-get-started-form-lik">
        <a href="https://wellqor.com/lets-get-started-form/">Get Started</a>
      </div>
    </div>

			</div>
HTML;
        //Clear a stored post_id whose page no longer exists so a fresh page is created
        if ($id !== '' && $id !== null && get_post($id) === null) {
            $id = '';
        }

        if ($id === '' || $id === null) {
            $post_id = wp_insert_post(
                [
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                    'post_author'    => $author_id,
                    'post_name'      => $slug,
                    'post_title'     => $title,
                    'post_status'    => $postStatus,
                    'post_type'      => 'page',
                    'post_content'   => $content,
                ]
            );

            if (is_wp_error($post_id) || empty($post_id)) {
                error_log('WELZP: profile page creation failed for Zoho_ID ' . $zoho_id);
                return;
            }

            //Link the new page to the clinician row
            static::$_zohoPeoplesEmployeesModel->update(
                ['post_id' => $post_id],
                ['zoho_id' => $zoho_id]
            );
        } else {
            wp_update_post(
                [
                    'ID'             => $id,
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                    'post_author'    => $author_id,
                    'post_name'      => $slug,
                    'post_title'     => $title,
                    'post_status'    => $postStatus,
                    'post_type'      => 'page',
                    'post_content'   => $content,
                ]
            );
        }
    }
}
