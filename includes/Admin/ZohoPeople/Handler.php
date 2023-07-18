<?php

namespace BitCode\WELZP\Admin\ZohoPeople;

use BitCode\WELZP\Core\Util\HttpHelper;
use BitCode\WELZP\Core\Database\IntegrationModel;
use BitCode\WELZP\Core\Database\ZohoPeoplesEmployeesModel;
use BitCode\WELZP\Core\Database\FormDetailsModel;
use WP_Error;
use wpdb;

final class Handler
{
    private static $_integrationModel;
    private static $_zohoPeoplesEmployeesModel;
    private static $_formDetailsModel;
    private static $data = '';

    public function __construct()
    {
        self::$_integrationModel = new IntegrationModel();
        self::$_zohoPeoplesEmployeesModel = new ZohoPeoplesEmployeesModel();
        self::$_formDetailsModel = new FormDetailsModel();
        $result = $this->get_auth_details();
        if (count((array)$result) > 0) {
            self::$data = json_decode($result->auth_details);
        }
    }

    public function analyticsGenerateToken()
    {
        $requestParams = [
            'grant_type'    => 'refresh_token',
            'client_id'     => '1000.WQOJA0E726OCJ4966AEHAG47RW3J0G',
            'client_secret' => '0bb35b82c29700d3051aecc679246527c35247adaf',
            'refresh_token' => '1000.fce138b2f9b0e944368a551c10e3162f.c27696c57732d6d64e029fcdceea61c5',
        ];
        $refreshToken = HttpHelper::post('https://accounts.zoho.com/oauth/v2/token', $requestParams);
        return $refreshToken;
    }

    public function insertReviewIntoAnalytics($requestData, $type)
    {
        $lastReviewId = static::$_formDetailsModel->get('id', [], 1, null, 'id', 'DESC');
        $refreshToken = $this->analyticsGenerateToken();
        $data = [
            'Employee Id'       => $requestData->employee_id,
            'Star'              => $requestData->star,
            'First Name'        => $requestData->fname,
            'Last Name'         => $requestData->lname,
            'Phrases'           => implode(', ', $requestData->phrases),
            'Title'             => $requestData->title,
            'Title Description' => $requestData->desc,
            'Age Range'         => $requestData->age,
            'Gender'            => $requestData->gender,
            'Status'            => $requestData->status,
            'Empathetic'        => $requestData->empathetic,
            'Review Id'         => $type === 'insert' ? $lastReviewId[0]->id : $requestData->editRowId,
            'Created At'        => date('d M,Y h:i:s'),
        ];

        if ($refreshToken) {
            $apiEndpoint = 'https://analyticsapi.zoho.com/api/dschwam@wellqor.com/Developer_space/Patient Review Data?ZOHO_ACTION=ADDROW&ZOHO_OUTPUT_FORMAT=JSON&ZOHO_ERROR_FORMAT=JSON&ZOHO_API_VERSION=1.0';
            $authorizationHeader['Authorization'] = 'Zoho-oauthtoken ' . $refreshToken->access_token;
            $apiResponse = HttpHelper::post($apiEndpoint, $data, $authorizationHeader);
        }
        return $apiResponse;
    }

    public function updateReviewIntoAnalytics($requestData)
    {
        $data = [
            'Employee Id'       => $requestData->employee_id,
            'Star'              => $requestData->star,
            'First Name'        => $requestData->fname,
            'Last Name'         => $requestData->lname,
            'Phrases'           => implode(', ', $requestData->phrases),
            'Title'             => $requestData->title,
            'Title Description' => $requestData->desc,
            'Age Range'         => $requestData->age,
            'Gender'            => $requestData->gender,
            'Status'            => $requestData->status,
            'Empathetic'        => $requestData->empathetic,
            'Updated At'        => date('d M,Y h:i:s'),
        ];

        $criteria = "(\"Review Id\"='$requestData->editRowId')";
        $refreshToken = $this->analyticsGenerateToken();
        if ($refreshToken) {
            $apiEndpoint = "https://analyticsapi.zoho.com/api/dschwam@wellqor.com/Developer_space/Patient Review Data?ZOHO_ACTION=UPDATE&ZOHO_OUTPUT_FORMAT=JSON&ZOHO_ERROR_FORMAT=JSON&ZOHO_API_VERSION=1.0&ZOHO_CRITERIA={$criteria}";
            $authorizationHeader['Authorization'] = 'Zoho-oauthtoken ' . $refreshToken->access_token;
            $apiResponse = HttpHelper::post($apiEndpoint, $data, $authorizationHeader);
        }
        return $apiResponse;
    }

    public function generate_token($data)
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

    public function integration_save($data)
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

    public function review_data_save($request)
    {
        $result = static::$_formDetailsModel->insert(
            [
                'form_details' => wp_json_encode($request),
                'created_at'   => date('Y-m-d:h:i:sa')
            ]
        );
        $this->insertReviewIntoAnalytics($request, 'insert');
        $this->get_peoples_forms();
        if (is_wp_error($result)) {
            wp_send_json_error('Data Insertion Failed');
        }
        wp_send_json_success($result, 200);
    }

    public function integration_update($data)
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

    public function get_auth_details()
    {
        $auth_details = static::$_integrationModel->get();
        if (is_wp_error($auth_details)) {
            return (object) [];
        }
        return $auth_details[0];
    }

    public function get_employee_data()
    {
        $employee_data = static::$_zohoPeoplesEmployeesModel->get('*', [], null, null, 'id', 'DESC');
        if (is_wp_error($employee_data)) {
            wp_send_json_error('Failed to fetch');
        }
        wp_send_json_success($employee_data, 200);
    }

    public function updateZohoPeoplesFields($recordId, $profileUrl, $reviewUrl)
    {
        $requestData = self::$data;
        $isTokenExpired = false;
        $_apiDomain = "https://people.zoho.com/people/api/forms/json/employee/updateRecord?inputData={Profile_URL:'$profileUrl', Review_URL:'$reviewUrl'}&recordId=$recordId";
        if ((intval($requestData->tokenDetails->generates_on) + (55 * 60)) < time()) {
            $refreshedToken = $this::_refreshAccessToken($requestData);
            if ($refreshedToken) {
                $isTokenExpired = true;
                $requestData->tokenDetails = $refreshedToken;
            } else {
                wp_send_json_error(
                    __('Failed to refresh access token', 'bitwelzp'),
                    400
                );
            }
        }
        $_defaultHeader['Authorization'] = "Zoho-oauthtoken {$requestData->tokenDetails->access_token}";
        if ($isTokenExpired && !empty($requestData->integrationId)) {
            $this::_saveRefreshedToken($requestData->integrationId, $requestData);
        }
        HttpHelper::get($_apiDomain, [], $_defaultHeader);
    }

    public function get_peoples_forms()
    {
        $upload_dir = wp_upload_dir();
        global $wpdb;
        $requestData = self::$data;
        $isTokenExpired = false;
        $_apiDomain = 'https://people.zoho.com/people/api/forms/employee/getRecords';
        if ((intval($requestData->tokenDetails->generates_on) + (55 * 60)) < time()) {
            $refreshedToken = $this::_refreshAccessToken($requestData);
            if ($refreshedToken) {
                $isTokenExpired = true;
                $requestData->tokenDetails = $refreshedToken;
            } else {
                wp_send_json_error(
                    __('Failed to refresh access token', 'bitwelzp'),
                    400
                );
            }
        }
        $_defaultHeader['Authorization'] = "Zoho-oauthtoken {$requestData->tokenDetails->access_token}";
        if ($isTokenExpired && !empty($requestData->integrationId)) {
            $this::_saveRefreshedToken($requestData->integrationId, $requestData);
        }

        $apiResponse = HttpHelper::get($_apiDomain, [], $_defaultHeader);

        $getAllRiviews = static::$_formDetailsModel->get('*', [], null, null, 'id', 'DESC');
        $recordId = '';
        $profileUrl = '';
        $reviewUrl = '';
        if (!$apiResponse->error) {
            $employee_details = static::$_zohoPeoplesEmployeesModel->get();
            $allEmployesId = [];
            if (is_wp_error($employee_details)) {
                foreach ($apiResponse->response->result as  $data) {
                    foreach ((array)$data as  $employee) {
                        $recordId = $employee[0]->Zoho_ID;
                        $profileUrl = 'https://wellqor.com/' . $employee[0]->fname[0] . '' . $employee[0]->lname . '';
                        $reviewUrl = 'https://wellqor.com/therapist-review-form/?employee_id=' . $employee[0]->employee_id . '';
                        $headshot_url = $employee[0]->Headshot_downloadUrl;
                        $headshot_response = HttpHelper::get($headshot_url, [], $_defaultHeader);
                        $fileName = $employee[0]->Headshot;
                        file_put_contents($upload_dir['basedir'] . '/' . $fileName, $headshot_response);

                        $arraValues = static::getClinicianFormData($employee, $_defaultHeader);
                        $insertData = [
                            'email_Id'                                  => $employee[0]->EmailID,
                            'employee_id'                               => $employee[0]->EmployeeID,
                            'headshot_download_url'                     => $fileName,
                            'employee_status'                           => $employee[0]->Employeestatus,
                            'fname'                                     => $employee[0]->FirstName,
                            'lname'                                     => $employee[0]->LastName,
                            'preferred_name_nickname'                   => $employee[0]->Preferred_Name_Nickname,
                            'clinical_title'                            => $employee[0]->Clinical_Title,
                            'medical_qualification'                     => $employee[0]->Cultural_Competency,
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

                        static::$_zohoPeoplesEmployeesModel->insert(
                            $insertData
                        );

                        if ($employee[0]->Employeestatus === 'Active' && ($employee[0]->Designation === 'Clinical Therapist' || $employee[0]->Designation === 'Clinical Director') && $employee[0]->Allow_Telehealth_Access === 'true') {
                            $this::programmatically_create_post(
                                $insertData,
                                '',
                                $getAllRiviews
                            );
                        }
                    }
                };
            } else {
                foreach ($employee_details as $employee) {
                    array_push($allEmployesId, $employee->employee_id);
                }
                foreach ($apiResponse->response->result as  $data) {
                    foreach ((array)$data as  $employee) {

                        $recordId = $employee[0]->Zoho_ID;
                        $profileUrl = 'https://wellqor.com/' . $employee[0]->FirstName[0] . '' . $employee[0]->LastName . '';
                        $reviewUrl = 'https://wellqor.com/therapist-review-form/?employee_id=' . $employee[0]->EmployeeID . '';
                        $headshot_url = $employee[0]->Headshot_downloadUrl;
                        $headshot_response = HttpHelper::get($headshot_url, [], $_defaultHeader);
                        $fileName = $employee[0]->Headshot;
                        file_put_contents($upload_dir['basedir'] . '/' . $fileName, $headshot_response);

                        $arraValues = static::getClinicianFormData($employee, $_defaultHeader);
                        $updateData = [
                            'email_Id'                                  => $employee[0]->EmailID,
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
                        if (in_array($employee[0]->EmployeeID, $allEmployesId)) {
                            static::$_zohoPeoplesEmployeesModel->update(
                                $updateData,
                                ['employee_id' => $employee[0]->EmployeeID]
                            );

                            $queryId = $employee[0]->EmployeeID;

                            $post_id = $wpdb->get_row("SELECT post_id FROM wp_bitwelzp_zoho_people_employee_info WHERE employee_id ='$queryId'");
                            if ($employee[0]->Employeestatus === 'Active' && ($employee[0]->Designation === 'Clinical Therapist' || $employee[0]->Designation === 'Clinical Director') && $employee[0]->Allow_Telehealth_Access === 'true') {
                                $this::programmatically_create_post(
                                    $updateData,
                                    $post_id !== null ? $post_id->post_id : '',
                                    $getAllRiviews
                                );
                                if ($employee[0]->Profile_URL === '' || $employee[0]->Review_URL === '') {
                                    $this->updateZohoPeoplesFields($recordId, $profileUrl, $reviewUrl);
                                }
                            }
                        } else {
                            static::$_zohoPeoplesEmployeesModel->insert(
                                $updateData
                            );
                            $queryId = $employee[0]->EmployeeID;


                            $post_id = $wpdb->get_row("SELECT post_id FROM wp_bitwelzp_zoho_people_employee_info WHERE employee_id ='$queryId'");
                            if ($employee[0]->Employeestatus === 'Active' && ($employee[0]->Designation === 'Clinical Therapist' || $employee[0]->Designation === 'Clinical Director') && $employee[0]->Allow_Telehealth_Access === 'true') {
                                $this::programmatically_create_post(
                                    $updateData,
                                    $post_id !== null ? $post_id->post_id : '',
                                    $getAllRiviews
                                );
                                if ($employee[0]->Profile_URL === '' || $employee[0]->Review_URL === '') {
                                    $this->updateZohoPeoplesFields($recordId, $profileUrl, $reviewUrl);
                                }
                            }
                        }
                    }
                };
            }

            $all_employees = $this->get_all_employees();
            wp_send_json_success($all_employees, 200);
        } else {
            wp_send_json_error(
                empty($apiResponse->error) ? 'Unknown' : $apiResponse->error,
                400
            );
        }
    }

    public static function getClinicianFormData($employeeData, $_defaultHeader)
    {
        $clinicialFormParams = [
            'searchField'   => 'Clinician_Name',
            'searchOperator'=> 'Contains',
            'searchText'    => $employeeData[0]->EmployeeID
        ];
        $clinicialFormResponse = HttpHelper::get('https://people.zoho.com/people/api/forms/Clinician_Profile/getRecords?searchParams=' . json_encode($clinicialFormParams) . '', [], $_defaultHeader);
        $arraValues = '';
        if (isset($clinicialFormResponse->response->result)) {
            $responseData = (array) $clinicialFormResponse->response->result[0];
            $arraValues = array_values($responseData)[0][0];
        }
        return $arraValues;
    }

    protected static function _refreshAccessToken($apiData)
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

    protected static function _saveRefreshedToken($integrationID, $data)
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

    public function get_all_employees()
    {

        // global $wpdb;
        // $wpdb->query("ALTER TABLE wp_bitwelzp_zoho_people_employee_info ADD preferred_name_nickname varchar(255) DEFAULT NULL After lname");
        $all_employees = static::$_zohoPeoplesEmployeesModel->get('*', ['employee_status' => 'Active', 'designation' => ['Clinical Therapist', 'Clinical Director'], 'allow_telehealth_access' => 'true'], null, null, 'id', 'DESC');
        if (is_wp_error($all_employees)) {
            return  [];
        }
        return $all_employees;
    }

    public function delete_employees($Ids)
    {
        global $wpdb;
        $result = '';
        foreach ($Ids as $id) {
            $result = $wpdb->delete($wpdb->prefix . 'bitwelzp_zoho_people_employee_info', ['id' => $id]);
        }
        wp_send_json_success($result);
    }

    public function delete_form_details($Ids)
    {
        global $wpdb;
        $result = '';
        foreach ($Ids as $id) {
            $result = $wpdb->delete($wpdb->prefix . 'bitwelzp_form_details', ['id' => $id]);
        }

        wp_send_json_success($result);
    }

    public function review_approve($id)
    {
        $get_form_details = static::$_formDetailsModel->get('*', ['id' => $id]);
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
            $this->updateReviewIntoAnalytics($new_form_details);
        }
        $get_updated_form_details = static::$_formDetailsModel->get('*', [], null, null, 'id', 'DESC');
        wp_send_json_success($get_updated_form_details, 200);
    }

    public function review_update($requestData)
    {
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
            $form_details = static::$_formDetailsModel->get('*', [], null, null, 'id', 'DESC');
            $requestData->inputData->editRowId = $requestData->editRowId;
            $updateReview = $this->updateReviewIntoAnalytics($requestData->inputData);
            $updateReview = json_decode(preg_replace("/\\\'/", "'", $updateReview));

            if ($updateReview->response->result->updatedRows === '0') {
                $this->insertReviewIntoAnalytics($requestData->inputData, 'update');
            }
            wp_send_json_success($form_details, 200);
        }
    }

    public function get_form_details()
    {
        $all_reviews = static::$_formDetailsModel->get('*', [], null, null, 'id', 'DESC');
        if (is_wp_error($all_reviews)) {
            return  [];
        }
        return $all_reviews;
    }

    public function page_active($id)
    {
        global $wpdb;
        $employee_data_by_id = static::$_zohoPeoplesEmployeesModel->get('*', ['id' => $id], null, null, 'id', 'DESC');
        $status = '';
        if ($employee_data_by_id[0]->page_status === 'inactive') {
            $status = 'active';
            $term_id = $wpdb->get_row("SELECT term_id FROM wp_terms WHERE name ='Active Profile Page'");
            if ($term_id) {
                $wpdb->update('wp_term_relationships', ['term_taxonomy_id' => $term_id->term_id], ['object_id' => $employee_data_by_id[0]->post_id]);
            }
        } else {
            $status = 'inactive';
            $term_id = $wpdb->get_row("SELECT term_id FROM wp_terms WHERE name ='InActive Profile Page'");
            if ($term_id) {
                $wpdb->update('wp_term_relationships', ['term_taxonomy_id' => $term_id->term_id], ['object_id' => $employee_data_by_id[0]->post_id]);
            }
        }

        $result = static::$_zohoPeoplesEmployeesModel->update(
            ['page_status' => $status],
            ['id' => $id]
        );
        if (is_wp_error($result)) {
            wp_send_json_error('Updating Failed');
        }
        $employee_data = static::$_zohoPeoplesEmployeesModel->get('*', ['employee_status' => 'Active', 'designation' => ['Clinical Therapist', 'Clinical Director'], 'allow_telehealth_access' => 'true'], null, null, 'id', 'DESC');
        wp_send_json_success($employee_data, 200);
    }

    public static function programmatically_create_post($data, $id, $getAllReviews)
    {
        global $wpdb;
        $upload_dir = wp_upload_dir();
        $employee_id = $data['employee_id'];
        //         $page_status = $data['page_status'];
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
        $totalStars = 0;
        foreach ($getAllReviews as $review) {
            $form_details = json_decode($review->form_details);
            if ($employee_id == $form_details->employee_id && $form_details->status == 'approved') {
                $form_details->created_at = $review->created_at;
                array_push($reviewsData, $form_details);
                $tempArray = $phrasesArray;
                $phrasesArray = array_merge($tempArray, $form_details->phrases);
                $totalStars = $totalStars + $form_details->star;
            }
        }
        $arr_freq = array_count_values($phrasesArray);
        arsort($arr_freq);
        $show_phrases = array_keys($arr_freq);
        $totalVerifiedReviews = count($reviewsData);

        $page_status = $wpdb->get_row("SELECT page_status FROM wp_bitwelzp_zoho_people_employee_info WHERE employee_id ='$employee_id'");
        if ($page_status == null) {
            static::$_zohoPeoplesEmployeesModel->update(
                ['page_status' => 'active'],
                ['employee_id' => $employee_id]
            );
        }
        //Style in Code Snippets Footer
        $content = <<<HTML
          <div class="employee-details">
<div class="profile-heading" style="">
<div class="container" style="">
    <div class="profile-img">
        <img src='$new_headshot_download_url' style="">
    </div>
    <div class="title" style="">
        <div class="name">
            <h2 style=""><span>$preferred_name_nickname</span> <span>$lname, </span><span>$medical_qualification</span></h2>
        </div>
        <div class="designation">
        $clinical_title
        </div>
    </div>
</div>
</div>
		  

    <div class="profile-content">
		<div class="container" style="">
    <div class="left" style="">
        <div class="specialities" style="">
            <h4>Specialities</h4>
            <ul>
            	       {$map($skillArray, function ($skill) {
            return "
                <li>$skill</li>
 ";
        })}
            </ul>
        </div>
        <div class="line"></div>

        <div class="certifications" style="">
            <h4>Treatment Modalities</h4>
            <ul>
             {$map($certificationsArray, function ($certification) {
            return "
                <li>$certification</li>
 ";
        })}
            </ul>
        </div>
		
       
        <div class="line"></div>
        <div class="cultural-competencies" style="">
            <h4>Cultural Competencies</h4>
            <ul style="">
                 {$map($culturalCompetencyArray, function ($culturalCompetency) {
            return "
                <li>$culturalCompetency</li>
 ";
        })}
            </ul>
        </div>
        <div class="line"></div>
		
        <div class="language-spoken" style="">
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
    <div class="right" style="">
        <div class="professional-bio" style="">
            <h4>Professional Bio</h4>
                      <span>$public_bio</span>

        </div>
        <div class="line" style="height: 1.5px;
        background-color: #e9e1e1;margin-bottom: 20px;"></div>
        <div class="education-license" style="">
            <div class="education" style="">
                <h4>Education</h4>
               <span>$advanced_degree_from</span>
            </div>
            <div class="line" style="
        background-color: #e9e1e1;
        width: 1.5px;"></div>
            <div class="license" style="">
                <h4 style="">Licensed in</h4>
           {$map($licensedArray, function ($license_in) {
            return "
            <span>$license_in</span>
 ";
        })}
            </div>
        </div>
        <div class="line" style="height: 1px;
        background-color: #e9e1e1;margin-bottom: 20px;margin-top: 20px;"></div>
  <div class="patient-satisfaction" style="">
<div class="education">
<div class="verified-reviews">
<h4 style="">Patient Satisfaction</span></h4>
<span style=""> $totalVerifiedReviews Verified Reviews</span>
<div class="stars"><img class="" src="https://wellqor.com/wp-content/uploads/2021/11/stars.png" width="137" height="26" /></div>
</div>
<div class="line" style="background-color: #e9e1e1; width: 1.5px;"></div>
<div class="highlights">
<h4><span style="">Review Highlights</span></h4>
<span>$show_phrases[0]</span>
<span>$show_phrases[1]</span>
<span>$show_phrases[2]</span>
<span>$show_phrases[3]</span>
</div>
</div>
<div class="featured-FPO" style="">
<div class="featured-content-FPO" style="">
<h4><span>Featured Reviews</span></h4>
{$map($reviewsData, function ($reviews, $index) {
            return "
           <a href='https://wellqor.com/show-all-reviews?employee_id={$reviews->employee_id}'>
                             <div class='reviews-list'>
                                <div class='reviews-accordion'> 
                                    <div class='reviewer-info'>
                                        <span>$reviews->gender</span>, <span>$reviews->age (Verified) on $reviews->created_at<span>

                                    </div>
                                        
                                        <div class='title'>
                                            <img class='' src='https://wellqor.com/wp-content/uploads/2021/11/stars.png' width='137' height='26' />
                                            <span> $reviews->title</span>
                                        </div>
                                    </div>
                    
                            </div>
</a>

                         
";
        })}
        
        

        
      <div class='all-reviews' id='show-all-reviews-btn'>
       <a href='https://wellqor.com/show-all-reviews?employee_id={$employee_id}' >Read All $totalVerifiedReviews Reviews</a>
                            </div>

</div>
</div>
</div>
</div>
</div>
HTML;
        if ($id === '' || $id === null) {
            $post_id = wp_insert_post(
                [
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                    'post_author'    => $author_id,
                    'post_name'      => $slug,
                    'post_title'     => $title,
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'post_content'   => $content,
                ]
            );
            $term_id = $wpdb->get_row("SELECT term_id FROM wp_terms WHERE name ='Active Profile Page'");
            if ($term_id) {
                $exist_object = $wpdb->get_row("SELECT * FROM wp_term_relationships where object_id=$post_id");
                if ($exist_object) {
                    $wpdb->update('wp_term_relationships', ['term_taxonomy_id' => $term_id->term_id], ['object_id' => $post_id]);
                } else {
                    $wpdb->insert('wp_term_relationships', ['term_taxonomy_id' => $term_id->term_id, 'object_id' => $post_id]);
                }
            }
            $data['post_id'] = $post_id;
            static::$_zohoPeoplesEmployeesModel->update(
                $data,
                ['employee_id' => $employee_id]
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
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'post_content'   => $content,
                ]
            );

            $term_id = $wpdb->get_row("SELECT term_id FROM wp_terms WHERE name ='Active Profile Page'");
            if ($term_id) {
                $exist_object = $wpdb->get_row("SELECT * FROM wp_term_relationships where object_id=$id");
                if ($exist_object) {
                    $wpdb->update('wp_term_relationships', ['term_taxonomy_id' => $term_id->term_id], ['object_id' => $id]);
                } else {
                    $wpdb->insert('wp_term_relationships', ['term_taxonomy_id' => $term_id->term_id, 'object_id' => $id]);
                }
            }
        }
    }
}
