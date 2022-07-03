<?php

/**
 * ZohoCrm Files Api
 *
 */

namespace BitCode\WELZP\Integration\ZohoCRM;

use BitCode\WELZP\Core\Util\HttpHelper;
use BitCode\WELZP\Admin\Log\Handler as Log;

/**
 * Provide functionality for Upload files
 */
final class FilesApiHelper
{
    private $_defaultHeader;
    private $_apiDomain;
    private $_payloadBoundary;
    private $_formID;

    /**
     *
     * @param Object  $tokenDetails Api token details
     * @param Integer $formID       ID of the form, for which integration is executing
     * @param Integer $entryID      Current submittion ID
     */
    public function __construct($tokenDetails, $formID, $integId)
    {
        $this->_integId = $integId;
        $this->_payloadBoundary = wp_generate_password(24);
        $this->_defaultHeader['Authorization'] = "Zoho-oauthtoken {$tokenDetails->access_token}";
        $this->_defaultHeader['content-type'] = "multipart/form; boundary=" . $this->_payloadBoundary;
        $this->_apiDomain = \urldecode($tokenDetails->api_domain);
        $this->_formID = $formID;
    }

    /**
     * Helps to execute upload files api
     *
     * @param Mixed $files        Files path
     * @param Bool  $isAttachment Check upload type
     * @param Mixed $module       Attachment Module name
     * @param Mixed $recordID     Record id
     *
     * @return Array $uploadedFiles ID's of uploaded file in Zoho CRM
     */
    public function uploadFiles($files, $isAttachment = false, $module = '', $recordID = 0)
    {
        $uploadFileEndpoint = $isAttachment ?
            "{$this->_apiDomain}/crm/v2/{$module}/{$recordID}/Attachments"
            : "{$this->_apiDomain}/crm/v2/files";
        $payload = '';
        $upDir = wp_upload_dir();
        $files = is_string($files) ? str_replace($upDir['baseurl'] . FLUENTFORM_UPLOAD_DIR, $upDir['basedir'] . FLUENTFORM_UPLOAD_DIR, $files) : $files;
        if (is_array($files)) {
            foreach ($files as $fileIndex => $fileName) {
                $path = str_replace($upDir['baseurl'] . FLUENTFORM_UPLOAD_DIR, $upDir['basedir'] . FLUENTFORM_UPLOAD_DIR, $fileName);
                if (is_readable("{$path}") && !is_dir("{$path}")) {
                    $payload .= '--' . $this->_payloadBoundary;
                    $payload .= "\r\n";
                    $payload .= 'Content-Disposition: form-data; name="' . 'file' .
                        '"; filename="' . basename("{$path}") . '"' . "\r\n";
                    $payload .= "\r\n";
                    $payload .= file_get_contents("{$path}");
                    $payload .= "\r\n";
                }
            }
        } elseif (is_readable("{$files}") && !is_dir("{$files}")) {
            $payload .= '--' . $this->_payloadBoundary;
            $payload .= "\r\n";
            $payload .= 'Content-Disposition: form-data; name="' . 'file' .
                '"; filename="' . basename("{$files}") . '"' . "\r\n";
            $payload .= "\r\n";
            $payload .= file_get_contents("{$files}");
            $payload .= "\r\n";
        }
        if (empty($payload)) {
            return false;
        }
        $payload .= '--' . $this->_payloadBoundary . '--';
        $uploadResponse = HttpHelper::post($uploadFileEndpoint, $payload, $this->_defaultHeader);
        if (!$isAttachment) {
            $uploadedFiles = [];
            if (!empty($uploadResponse->data) && \is_array($uploadResponse->data)) {
                foreach ($uploadResponse->data as $singleFileResponse) {
                    if (!empty($singleFileResponse->code) && $singleFileResponse->code === 'SUCCESS') {
                        $uploadedFiles[] = $singleFileResponse->details->id;
                    }
                }
            }
            if (isset($uploadResponse->status) &&  $uploadResponse->status === 'error') {
                Log::save($this->_formID, $this->_integId, wp_json_encode(['type' => 'upload', 'type_name' => 'file']), 'error', wp_json_encode($uploadResponse));
            } else {
                Log::save($this->_formID, $this->_integId, wp_json_encode(['type' => 'upload', 'type_name' => 'file']), 'success', wp_json_encode($uploadResponse));
            }
            return $uploadedFiles;
        }
        return $uploadResponse;
    }
}
