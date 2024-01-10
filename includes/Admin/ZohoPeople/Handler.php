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
    private static $data = '';

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

    //New access token generate for Zoho Analytics authorization
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

    //Zoho authentication generate toekn
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

    //Clinician patient review is added to Zoho Analytics (Patient Review Data) table via API
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

    //When clinician patient review is updated in Zoho People plugin, the review also updated in the Zoho Analytics (Patient Review Data) table via API

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

    //Update (clinician profile link and review link) fields in Zoho People from Zoho People plugin via API
    public function updateZohoPeoplesFields($recordId, $profileUrl, $reviewUrl)
    {
        $requestData = self::$data;
        $isTokenExpired = false;
        $_apiDomain = "https://people.zoho.com/people/api/forms/json/employee/updateRecord?inputData={Profile_URL:'$profileUrl', Review_URL:'$reviewUrl'}&recordId=$recordId";

        if ((intval($requestData->tokenDetails->generates_on) + (55 * 60)) < time()) {
            $refreshedToken = $this::refreshAccessToken($requestData);
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
            $this::saveRefreshedToken($requestData->integrationId, $requestData);
        }

        HttpHelper::get($_apiDomain, [], $_defaultHeader);
    }

    //Fetch Clinician information from Zoho People
    public function getPeoplesForms()
    {
        global $wpdb;
        $upload_dir = wp_upload_dir();
        $requestData = self::$data;
        $isTokenExpired = false;

        if ((intval($requestData->tokenDetails->generates_on) + (55 * 60)) < time()) {
            $refreshedToken = $this::refreshAccessToken($requestData);
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
            $this::saveRefreshedToken($requestData->integrationId, $requestData);
        }

        $apiResponse = [] ;
        $totalEmployees = [];
        try {
            while (!isset($apiResponse->response->errors)) {
                $sIndex = count($totalEmployees) > 0 ? count($totalEmployees) + 1 : 1;
                $apiEndpoint = 'https://people.zoho.com/people/api/forms/employee/getRecords?sIndex=' . $sIndex . '&limit=100';
                $apiResponse = HttpHelper::get($apiEndpoint, [], $_defaultHeader);

                if (!isset($apiResponse->response->errors)) {
                    if (count($totalEmployees) > 0) {
                        $totalEmployees = array_merge($totalEmployees, $apiResponse->response->result);
                    } else {
                        $totalEmployees = $apiResponse->response->result;
                    }
                }
            }

            $getAllRiviews = static::$_formDetailsModel->get('*', [], null, null, 'id', 'DESC');
            $recordId = '';
            $profileUrl = '';
            $reviewUrl = '';

            $employee_details = static::$_zohoPeoplesEmployeesModel->get();
            $allEmployesId = [];

            if (count($totalEmployees)) {
                if (is_array($employee_details) && count($employee_details)) {
                    foreach ($employee_details as $employee) {
                        array_push($allEmployesId, $employee->employee_id);
                    }
                }

                foreach ($totalEmployees as  $data) {
                    foreach ((array) $data as  $employee) {

                        if ($this::isEmployeeActive($employee[0])) {
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
                                'zoho_Id'                                   => $employee[0]->Zoho_ID,
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

                            if (is_array($employee_details) && count($employee_details)) {
                                if (in_array($employee[0]->EmployeeID, $allEmployesId)) {
                                    static::$_zohoPeoplesEmployeesModel->update(
                                        $insertData,
                                        ['employee_id' => $employee[0]->EmployeeID]
                                    );

                                    $queryId = $employee[0]->EmployeeID;

                                    $post_id = $wpdb->get_row("SELECT post_id FROM wp_bitwelzp_zoho_people_employee_info WHERE employee_id ='$queryId'");
                                    $this::createClinicianProfilePage(
                                        $insertData,
                                        $post_id !== null ? $post_id->post_id : '',
                                        $getAllRiviews
                                    );
                                    if ($employee[0]->Profile_URL === '' || $employee[0]->Review_URL === '') {
                                        $this->updateZohoPeoplesFields($recordId, $profileUrl, $reviewUrl);
                                    }
                                } else {
                                    static::$_zohoPeoplesEmployeesModel->insert(
                                        $insertData
                                    );

                                    $queryId = $employee[0]->EmployeeID;
                                    $post_id = $wpdb->get_row("SELECT post_id FROM wp_bitwelzp_zoho_people_employee_info WHERE employee_id ='$queryId'");

                                    $this::createClinicianProfilePage(
                                        $insertData,
                                        $post_id !== null ? $post_id->post_id : '',
                                        $getAllRiviews
                                    );

                                    if ($employee[0]->Profile_URL === '' || $employee[0]->Review_URL === '') {
                                        $this->updateZohoPeoplesFields($recordId, $profileUrl, $reviewUrl);
                                    }

                                }
                            } else {
                                static::$_zohoPeoplesEmployeesModel->insert(
                                    $insertData
                                );

                                $this::createClinicianProfilePage(
                                    $insertData,
                                    '',
                                    $getAllRiviews
                                );
                            }
                        }
                    }
                };
            }

            $all_employees = $this->getAllEmployees();
            wp_send_json_success($all_employees, 200);

        } catch (\Throwable $e) {
            wp_send_json_error(
                empty($apiResponse->response->errors) ? 'Unknown' : $apiResponse->response->errors,
                400
            );
        }

    }

    //Check Whether clinician status is active or not in Zoho People
    public static function isEmployeeActive($data)
    {
        if ($data->Employeestatus === 'Active' && ($data->Designation === 'Clinical Therapist' || $data->Designation === 'Clinical Director') && $data->Allow_Telehealth_Access === 'true') {
            return true;
        }

        return false;
    }

    public static function getClinicianFormData($employeeData, $_defaultHeader)
    {
        $clinicianFormParams = [
            'searchField'   => 'Clinician_Name',
            'searchOperator' => 'Contains',
            'searchText'    => $employeeData[0]->EmployeeID
        ];
        $clinicianFormResponse = HttpHelper::get('https://people.zoho.com/people/api/forms/Clinician_Profile/getRecords?searchParams=' . json_encode($clinicianFormParams) . '', [], $_defaultHeader);
        $arraValues = '';

        if (isset($clinicianFormResponse->response->result)) {
            $responseData = (array) $clinicianFormResponse->response->result[0];
            $arraValues = array_values($responseData)[0][0];
        }

        return $arraValues;
    }


    //Fetch all clinicans data from the database to show on the frontend
    public function getAllEmployees()
    {

        $all_employees = static::$_zohoPeoplesEmployeesModel->get('*', ['employee_status' => 'Active', 'designation' => ['Clinical Therapist', 'Clinical Director'], 'allow_telehealth_access' => 'true'], null, null, 'id', 'DESC');

        if (is_wp_error($all_employees)) {
            return  [];
        }

        return $all_employees;
    }

    //Delete clinician permanently from the database
    public function deleteEmployees($Ids)
    {
        global $wpdb;
        $result = '';

        foreach ($Ids as $id) {
            $result = $wpdb->delete($wpdb->prefix . 'bitwelzp_zoho_people_employee_info', ['id' => $id]);
        }

        wp_send_json_success($result);
    }

    //Save patient review data in the database
    public function saveReviews($request)
    {
        $result = static::$_formDetailsModel->insert(
            [
                'form_details' => wp_json_encode($request),
                'created_at'   => date('Y-m-d:h:i:sa')
            ]
        );

        $this->insertReviewIntoAnalytics($request, 'insert');
        $this->getPeoplesForms();

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

    //Update patient review
    public function updateReview($requestData)
    {
        $employee_data_by_id = static::$_zohoPeoplesEmployeesModel->get('*', ['employee_id' => $requestData->inputData->employee_id], null, null, 'id', 'DESC');
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

    //Fetch review from the database to show on the frontend
    public function get_form_details()
    {
        $all_reviews = static::$_formDetailsModel->get('*', [], null, null, 'id', 'DESC');
        if (is_wp_error($all_reviews)) {
            return  [];
        }
        return $all_reviews;
    }

    //Handle clinician profile page status
    public function handlePageStatus($id)
    {
        global $wpdb;
        $employee_data_by_id = static::$_zohoPeoplesEmployeesModel->get('*', ['id' => $id], null, null, 'id', 'DESC');
        $status = '';

        if ($employee_data_by_id[0]->page_status === 'inactive' || $employee_data_by_id[0]->page_status === null) {
            $status = 'active';
        } else {
            $status = 'inactive';
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

    //Create & update clinican profile page
    public static function createClinicianProfilePage($data, $id, $getAllReviews)
    {

        global $wpdb;
        $upload_dir = wp_upload_dir();
        $employee_id = $data['employee_id'];
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

        $showAllReviewsBtn = '';

        if($totalVerifiedReviews > 0) {
            $showAllReviewsBtn = "   <div class='all-reviews' id='show-all-reviews-btn'>
<a href='https://wellqor.com/show-all-reviews?employee_id={$employee_id}' >Read More</a>
                     </div>";
        }

        //Style in Code Snippets Footer
        $content = <<<HTML
          <div class="employee-details">
<div class="profile-heading">
<div class="container">
    <div class="profile-img">
        <img src='https://wellqor.com/wp-content/uploads/2023/11/animated_petal_bulletsArtboard-2-copy-7.svg'>
        <img src='$new_headshot_download_url'>
    </div>
    <div class="title">
        <div class="name">
            <h2 ><span>$preferred_name_nickname</span> <span>$lname, </span><span>$medical_qualification</span></h2>
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
<span>$show_phrases[0]</span>
<span>$show_phrases[1]</span>
<span>$show_phrases[2]</span>
<span>$show_phrases[3]</span>
</div>
</div>
<div class="line"></div>
<div class="featured-FPO">
<div class="featured-content-FPO">
<h4><span>Featured Reviews</span></h4>
{$map($reviewsData, function ($reviews) {
            return "
           <a href='https://wellqor.com/show-all-reviews?employee_id={$reviews->employee_id}'>
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

     <div class="patient-form">
                <div class="form-info">
                    <div class="title">

                        <h1>Not yet in touch with a patient liasion? Get matched with a therapist!</h1>
                    </div>
                    <div class="contact">

                        <h2>Tell us about you!</h2>
                        <p>If you prefer providing information over the phone, call(646) 687-4646</p>
                    </div>
                    <div class="option">
                        <h2>Get options in a few hours!</h2>
                        <p>We'll send you therapist profiles, including their clinical experience, approach,  and patient reviews.</p>
                    </div>
                    <div class="pick-time">
                        <h2>Pick your therapist & time.</h2>
                        <p>Let us know which therapist you like and what appointment slot yout prefer.</p>
                    </div>

                </div>
                <div class="form">
                    <iframe aria-label='2024 Get Started Form' frameborder="0"
                        style="height:750px;width:100%;border:none;"
                        src='https://forms.wellqor.com/wellqor/form/2024GetStartedForm/formperma/gsievJJFZAdSwOy89usPO9sBqaBQSn30XGf6mpdmlaQ'></iframe>
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
        }
    }
}
