<?php

/**
 * ZohoCrm Record Api
 *
 */

namespace BitCode\WELZP\Integration\ZohoCRM;

use WP_Error;
use BitCode\WELZP\Core\Util\HttpHelper;
use BitCode\WELZP\Integration\ZohoCRM\TagApiHelper;
use BitCode\WELZP\Integration\ZohoCRM\FilesApiHelper;
use BitCode\WELZP\Core\Util\DateTimeHelper;
use BitCode\WELZP\Admin\Log\Handler as Log;

/**
 * Provide functionality for Record insert,upsert
 */
class RecordApiHelper
{
    protected $_defaultHeader;
    protected $_apiDomain;
    protected $_tokenDetails;

    public function __construct($tokenDetails)
    {
        $this->_defaultHeader['Authorization'] = "Zoho-oauthtoken {$tokenDetails->access_token}";
        $this->_apiDomain = \urldecode($tokenDetails->api_domain);
        $this->_tokenDetails = $tokenDetails;
    }

    public function upsertRecord($module, $data)
    {
        $insertRecordEndpoint = "{$this->_apiDomain}/crm/v2/{$module}/upsert";
        $data = \is_string($data) ? $data : \json_encode($data);
        return HttpHelper::post($insertRecordEndpoint, $data, $this->_defaultHeader);
    }

    public function insertRecord($module, $data)
    {
        $insertRecordEndpoint = "{$this->_apiDomain}/crm/v2/{$module}";
        $data = \is_string($data) ? $data : \json_encode($data);
        return HttpHelper::post($insertRecordEndpoint, $data, $this->_defaultHeader);
    }

    public function serachRecord($module, $searchCriteria)
    {
        $searchRecordEndpoint = "{$this->_apiDomain}/crm/v2/{$module}/search";
        return HttpHelper::get($searchRecordEndpoint, ["criteria" => "({$searchCriteria})"], $this->_defaultHeader);
    }

    public function executeRecordApi($formID, $integId, $defaultConf, $module, $layout, $fieldValues, $fieldMap, $actions, $required, $fileMap = [], $isRelated = false)
    {
        global $isbitwelzpLicActive;
        $fieldData = [];
        $filesApiHelper = new FilesApiHelper($this->_tokenDetails, $formID, $integId);
        foreach ($fieldMap as $fieldKey => $fieldPair) {
            if (!empty($fieldPair->zohoFormField)) {
                if (empty($defaultConf->layouts->{$module}->{$layout}->fields->{$fieldPair->zohoFormField})) {
                    continue;
                }
                if ($fieldPair->formField === 'custom' && isset($fieldPair->customValue)) {
                    $fieldData[$fieldPair->zohoFormField] = $this->formatFieldValue($fieldPair->customValue, $defaultConf->layouts->{$module}->{$layout}->fields->{$fieldPair->zohoFormField});
                } elseif (strpos($fieldPair->formField, '=>') !== false) {
                    $formFieldValue = null;
                    foreach (explode('=>', $fieldPair->formField) as $key => $value) {
                        $formFieldValue = !isset($formFieldValue) ? $fieldValues[$value] : $formFieldValue[$value];
                    }
                    if (!is_null($formFieldValue)) {
                        $fieldData[$fieldPair->zohoFormField] = $this->formatFieldValue($formFieldValue, $defaultConf->layouts->{$module}->{$layout}->fields->{$fieldPair->zohoFormField});
                    }
                } else {
                    $fieldData[$fieldPair->zohoFormField] = $this->formatFieldValue($fieldValues[$fieldPair->formField], $defaultConf->layouts->{$module}->{$layout}->fields->{$fieldPair->zohoFormField});
                }

                if (empty($fieldData[$fieldPair->zohoFormField]) && \in_array($fieldPair->zohoFormField, $required)) {
                    $error = new WP_Error('REQ_FIELD_EMPTY', wp_sprintf(__('%s is required for zoho crm, %s module', 'bitwelzp'), $fieldPair->zohoFormField, $module));
                    Log::save($formID, $integId, wp_json_encode(['type' => 'record', 'type_name' => 'field']), 'validation', wp_json_encode($error));
                    return $error;
                }
                if (!empty($fieldData[$fieldPair->zohoFormField])) {
                    $requiredLength = $defaultConf->layouts->{$module}->{$layout}->fields->{$fieldPair->zohoFormField}->length;
                    $currentLength = is_array($fieldData[$fieldPair->zohoFormField]) || is_object($fieldData[$fieldPair->zohoFormField]) ?
                        @count($fieldData[$fieldPair->zohoFormField])
                        : strlen($fieldData[$fieldPair->zohoFormField]);
                    if ($currentLength > $requiredLength) {
                        $error = new WP_Error('REQ_FIELD_LENGTH_EXCEEDED', wp_sprintf(__('zoho crm field %s\'s maximum length is %s, Given %s', 'bitwelzp'), $fieldPair->zohoFormField, $module));
                        Log::save($formID, $integId, wp_json_encode(['type' => 'length', 'type_name' => 'field']), 'validation', wp_json_encode($error));
                        return $error;
                    }
                }
            }
        }

        if (count($fileMap) && $isbitwelzpLicActive) {
            foreach ($fileMap as $fileKey => $filePair) {
                if (!empty($filePair->zohoFormField)) {
                    if ($defaultConf->layouts->{$module}->{$layout}->fileUploadFields->{$filePair->zohoFormField}->data_type === 'fileupload' && !empty($fieldValues[$filePair->formField])) {
                        $files = $fieldValues[$filePair->formField];
                        $fileLength = $defaultConf->layouts->{$module}->{$layout}->fileUploadFields->{$filePair->zohoFormField}->length;
                        if (\is_array($files) && count($files) !== $fileLength) {
                            $files = array_slice($fieldValues[$filePair->formField], 0, $fileLength);
                        }
                        $uploadsIDs = $filesApiHelper->uploadFiles($files);
                        if ($uploadsIDs) {
                            $fieldData[$filePair->zohoFormField] = $uploadsIDs;
                        }
                    }
                }
            }
        }
        if (!empty($defaultConf->layouts->{$module}->{$layout}->id)  && $isbitwelzpLicActive) {
            $fieldData['Layout']['id'] = $defaultConf->layouts->{$module}->{$layout}->id;
        }
        if (!empty($actions->gclid) && isset($fieldValues['gclid']) && $isbitwelzpLicActive) {
            $fieldData['$gclid'] = $fieldValues['gclid'];
        }
        if (!empty($actions->rec_owner)  && $isbitwelzpLicActive) {
            $fieldData['Owner']['id'] = $actions->rec_owner;
        }
        $requestParams['data'][] = (object) $fieldData;
        $requestParams['trigger'] = [];
        if (!empty($actions->workflow) && $isbitwelzpLicActive) {
            $requestParams['trigger'][] = 'workflow';
        }
        if (!empty($actions->approval) && $isbitwelzpLicActive) {
            $requestParams['trigger'][] = 'approval';
        }
        if (!empty($actions->blueprint) && $isbitwelzpLicActive) {
            $requestParams['trigger'][] = 'blueprint';
        }
        if (!empty($actions->assignment_rules) && $isbitwelzpLicActive) {
            $requestParams['lar_id'] = $actions->assignment_rules;
        }
        $recordApiResponse = '';
        if (!empty($actions->upsert) && !empty($actions->upsert->crmField)) {
            $requestParams['duplicate_check_fields'] = [];
            if (!empty($actions->upsert)) {
                $duplicateCheckFields = [];
                $searchCriteria = '';
                foreach ($actions->upsert->crmField as $fieldInfo) {
                    if (!empty($fieldInfo->name) && $fieldData[$fieldInfo->name]) {
                        $duplicateCheckFields[] = $fieldInfo->name;
                        if (empty($searchCriteria)) {
                            $searchCriteria .= "({$fieldInfo->name}:equals:{$fieldData[$fieldInfo->name]})";
                        } else {
                            $searchCriteria .= "and({$fieldInfo->name}:equals:{$fieldData[$fieldInfo->name]})";
                        }
                    }
                }
                if (isset($actions->upsert->overwrite) && !$actions->upsert->overwrite && !empty($searchCriteria)) {
                    $searchRecordApiResponse = $this->serachRecord($module, $searchCriteria);
                    if (!empty($searchRecordApiResponse) && !empty($searchRecordApiResponse->data)) {
                        $previousData = $searchRecordApiResponse->data[0];
                        foreach ($fieldData as $apiName => $currentValue) {
                            if (!empty($previousData->{$apiName})) {
                                $fieldData[$apiName] = $previousData->{$apiName};
                            }
                        }
                        $requestParams['data'][] = (object) $fieldData;
                    }
                }
                $requestParams['duplicate_check_fields'] = $duplicateCheckFields;
            }
            $recordApiResponse = $this->upsertRecord($module, (object) $requestParams);
        } elseif ($isRelated) {
            $recordApiResponse = $this->insertRecord($module, (object) $requestParams);
        } else {
            $recordApiResponse = $this->upsertRecord($module, (object) $requestParams);
        }
        if (isset($recordApiResponse->status) &&  $recordApiResponse->status === 'error') {
            Log::save($formID, $integId, wp_json_encode(['type' => 'record', 'type_name' => $module]), 'error', wp_json_encode($recordApiResponse));
        } else {
            Log::save($formID, $integId, wp_json_encode(['type' => 'record', 'type_name' => $module]), 'success', wp_json_encode($recordApiResponse));
        }
        if (
            !empty($recordApiResponse->data)
            && !empty($recordApiResponse->data[0]->code)
            && $recordApiResponse->data[0]->code === 'SUCCESS'
            && !empty($recordApiResponse->data[0]->details->id)
            && $isbitwelzpLicActive
        ) {
            if (!empty($actions->tag_rec) && class_exists('BitCode\WELZP\Integration\ZohoCRM\TagApiHelper')) {
                $tags = '';
                $tag_rec = \explode(",", $actions->tag_rec);
                foreach ($tag_rec as $tag) {
                    if (is_string($tag) && substr($tag, 0, 2) === '${' && $tag[strlen($tag) - 1] === '}') {
                        $tags .= (!empty($tags) ? ',' : '') . $fieldValues[substr($tag, 2, strlen($tag) - 3)];
                    } else {
                        $tags .= (!empty($tags) ? ',' : '') . $tag;
                    }
                }
                $tagApiHelper = new TagApiHelper($this->_tokenDetails, $module);
                $addTagResponse = $tagApiHelper->addTagsSingleRecord($recordApiResponse->data[0]->details->id, $tags);
                if (isset($addTagResponse->status) &&  $addTagResponse->status === 'error') {
                    Log::save($formID, $integId, wp_json_encode(['type' => 'tag', 'type_name' => $module]), 'error', wp_json_encode($addTagResponse));
                } else {
                    Log::save($formID, $integId, wp_json_encode(['type' => 'tag', 'type_name' => $module]), 'success', wp_json_encode($addTagResponse));
                }
            }
            if (!empty($actions->attachment) && $isbitwelzpLicActive) {
                $validAttachments = array();
                $fileFound = 0;
                $responseType = 'success';
                $attachmentApiResponses = [];
                $attachment = explode(",", $actions->attachment);
                foreach ($attachment as $fileField) {
                    if (isset($fieldValues[$fileField]) && !empty($fieldValues[$fileField])) {
                        $fileFound = 1;
                        if (is_array($fieldValues[$fileField])) {
                            foreach ($fieldValues[$fileField] as $singleFile) {
                                $attachmentApiResponse = $filesApiHelper->uploadFiles($singleFile, true, $module, $recordApiResponse->data[0]->details->id);
                                if (isset($attachmentApiResponse->status) &&  $attachmentApiResponse->status === 'error') {
                                    $responseType = 'error';
                                }
                            }
                        } else {
                            $attachmentApiResponse = $filesApiHelper->uploadFiles($fieldValues[$fileField], true, $module, $recordApiResponse->data[0]->details->id);
                            if (isset($attachmentApiResponse->status) &&  $attachmentApiResponse->status === 'error') {
                                $responseType = 'error';
                            }
                        }
                    }
                }
                if ($fileFound) {
                    Log::save($formID, $integId, wp_json_encode(['type' => 'attachment', 'type_name' => $module]), $responseType, wp_json_encode($attachmentApiResponses));
                }
            }
        }

        return $recordApiResponse;
    }


    public function formatFieldValue($value, $formatSpecs)
    {
        if (empty($value)) {
            return '';
        }

        switch ($formatSpecs->json_type) {
            case 'jsonarray':
                $apiFormat = 'array';
                break;
            case 'jsonobject':
                $apiFormat = 'object';
                break;

            default:
                $apiFormat = $formatSpecs->json_type;
                break;
        }

        $formatedValue = '';
        $fieldFormat = gettype($value);
        if ($fieldFormat === $apiFormat && $formatSpecs->data_type !== 'datetime') {
            $formatedValue = $value;
        } else {
            if ($apiFormat === 'array' || $apiFormat === 'object') {
                if ($fieldFormat === 'string') {
                    if (strpos($value, ',') === -1) {
                        $formatedValue = json_decode($value);
                    } else {
                        $formatedValue = explode(',', $value);
                    }
                    $formatedValue = is_null($formatedValue) && !is_null($value) ? [$value] : $formatedValue;
                } else {
                    $formatedValue = $value;
                }

                if ($apiFormat === 'object') {
                    $formatedValue = (object) $formatedValue;
                }
            } elseif ($apiFormat === 'string' && $formatSpecs->data_type !== 'datetime') {
                $formatedValue = !is_string($value) ? json_encode($value) : $value;
            } elseif ($formatSpecs->data_type === 'datetime') {
                $dateTimeHelper = new DateTimeHelper();
                $formatedValue = $dateTimeHelper->getFormated($value, 'Y-m-d\TH:i', wp_timezone(), 'Y-m-d\TH:i:sP', null);
            } else {
                $stringyfiedValue = !is_string($value) ? json_encode($value) : $value;

                switch ($apiFormat) {
                    case 'double':
                        $formatedValue = (float) $stringyfiedValue;
                        break;

                    case 'boolean':
                        $formatedValue = (bool) $stringyfiedValue;
                        break;

                    case 'integer':
                        $formatedValue = (int) $stringyfiedValue;
                        break;

                    default:
                        $formatedValue = $stringyfiedValue;
                        break;
                }
            }
        }
        $formatedValueLenght = $apiFormat === 'array' || $apiFormat === 'object' ? (is_countable($formatedValue) ? \count($formatedValue) : @count($formatedValue)) : \strlen($formatedValue);
        if ($formatedValueLenght > $formatSpecs->length) {
            $formatedValue = $apiFormat === 'array' || $apiFormat === 'object' ? array_slice($formatedValue, 0, $formatSpecs->length) : substr($formatedValue, 0, $formatSpecs->length);
        }

        return $formatedValue;
    }
}
