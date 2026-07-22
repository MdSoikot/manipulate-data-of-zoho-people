<?php

namespace BitCode\WELZP\Integration;

use BitCode\WELZP\Core\Database\IntegrationModel;
use BitCode\WELZP\Admin\Log\Handler as Log;
use BitCode\WELZP\Core\Util\IpTool;

final class IntegrationHandler
{
    private static $_formID;
    private static $_integrationModel;
    private $_user_details;

    /**
     * Constructor of Integration Handler
     *
     * @param  Integer $formID       If Integration is accessible globally then
     *                               $formID will be 0 and category app
     * @param  Array   $user_details Details of user accessing data
     * @return void
     */
    public function __construct($formID, $user_details = null)
    {
        static::$_formID = $formID;
        static::$_integrationModel = new IntegrationModel();
        $this->_user_details = IpTool::getUserDetail();
    }


    public function getAIntegration($integrationID, $integrationCategory = null, $integrationType = null)
    {
        $conditions = array(
            'form_id' => static::$_formID,
            'id' => $integrationID,
        );
        if (!is_null($integrationType)) {
            $conditions = array_merge($conditions, ['integration_type' => $integrationType]);
        }
        if (!is_null($integrationCategory)) {
            $conditions = array_merge($conditions, ['category' => $integrationCategory]);
        }
        return static::$_integrationModel->get(
            array(
                'id',
                'integration_name',
                'integration_type',
                'integration_details',
                'form_id',
                'status'
            ),
            $conditions
        );
    }

    public function getAllIntegration($integrationCategory = null, $integrationType = null, $status = null)
    {
        $conditions = array(
            'form_id' => static::$_formID
        );
        if (!is_null($integrationType)) {
            $conditions = array_merge($conditions, ['integration_type' => $integrationType]);
        }

        if (!is_null($integrationCategory)) {
            $conditions = array_merge($conditions, ['category' => $integrationCategory]);
        }
        if (!is_null($status)) {
            $conditions = array_merge($conditions, ['status' => $status]);
        }
        return static::$_integrationModel->get(
            array(
                'id',
                'integration_name',
                'integration_type',
                'integration_details',
                'status'
            ),
            $conditions
        );
    }

    public function saveIntegration($integrationName, $integrationType, $integrationDetails, $integrationCategory, $status = null)
    {
        if ($status == null) {
            $status = 1;
        }
        return static::$_integrationModel->insert(
            array(
                "integration_name" => $integrationName,
                "integration_type" => $integrationType,
                "integration_details" => $integrationDetails,
                'category' => $integrationCategory,
                'form_id' => static::$_formID,
                "user_id" => $this->_user_details['id'],
                "user_ip" => $this->_user_details['ip'],
                "status" => $status,
                "created_at" => $this->_user_details['time'],
                "updated_at" => $this->_user_details['time']
            )
        );
    }

    public function updateIntegration($integrationID, $integrationName, $integrationType, $integrationDetails, $integrationCategory, $status = null)
    {
        return static::$_integrationModel->update(
            array(
                "integration_name" => $integrationName,
                "integration_type" => $integrationType,
                "integration_details" => $integrationDetails,
                'category' => $integrationCategory,
                // 'form_id' => static::$_formID,
                "user_id" => $this->_user_details['id'],
                "user_ip" => $this->_user_details['ip'],
                "updated_at" => $this->_user_details['time']
            ),
            array(
                "id" => $integrationID
            )
        );
    }
    
    public function updateStatus($integrationID, $status)
    {
        return static::$_integrationModel->update(
            array(
                "status" => $status,
                "user_id" => $this->_user_details['id'],
                "user_ip" => $this->_user_details['ip'],
                "updated_at" => $this->_user_details['time']
            ),
            array(
                "id" => $integrationID
            )
        );
    }

    public function duplicateAllInAForm($oldFormId)
    {
        $integCols = ["integration_name", "integration_type", "integration_details", 'category', 'form_id', "user_id", "user_ip", "status", "created_at", "updated_at"];
        $integDupData = array(
            "integration_name",
            "integration_type",
            "integration_details",
            'category',
            static::$_formID,
            $this->_user_details['id'],
            $this->_user_details['ip'],
            "status",
            $this->_user_details['time'],
            $this->_user_details['time']
        );
        return static::$_integrationModel->duplicate($integCols, $integDupData, ['form_id' => $oldFormId]);
    }

    public function deleteIntegration($integrationID)
    {
        $delStatus = static::$_integrationModel->delete(
            array(
                'id' => $integrationID,
                'form_id' => static::$_formID,
            )
        );
        if (is_wp_error($delStatus)) {
            return $delStatus;
        }
        Log::delete((object)['integration_id' => $integrationID]);
        return $delStatus;
    }
}
