<?php
/**
 *
 * @package bitwelzp
 */
namespace BitCode\WELZP\Integration;

/**
 * Provides details of available integration and helps to
 * execute available integrations
 */

use BitCode\WELZP\Core\Util\Route;
use FilesystemIterator;
 use WP_Error;

final class Integrations
{
    public function registerAjax()
    {
        $dirs = new FilesystemIterator(__DIR__);
        foreach ($dirs as $dirInfo) {
            if ($dirInfo->isDir()) {
                $integartionBaseName = basename($dirInfo);
                if (file_exists(__DIR__.'/'.$integartionBaseName)
                    && file_exists(__DIR__.'/'.$integartionBaseName.'/'.$integartionBaseName.'Handler.php')
                ) {
                    $integration = __NAMESPACE__. "\\{$integartionBaseName}\\{$integartionBaseName}Handler";
                    if (method_exists($integration, 'registerAjax')) {
                        $integration::registerAjax();
                    }
                }
            }
        }
    }

    public function registerHooks()
    {
        Route::post('integration/save', [$this, 'save']);
        Route::post('integration/update', [$this, 'update']);
        Route::post('integration/delete', [$this, 'delete']);
        Route::post('integration/toggleStatus', [$this, 'toggle_status']);
        $dirs = new FilesystemIterator(__DIR__);
        foreach ($dirs as $dirInfo) {
            if ($dirInfo->isDir()) {
                $integartionBaseName = basename($dirInfo);
                if (file_exists(__DIR__.'/'.$integartionBaseName)
                    && file_exists(__DIR__.'/'.$integartionBaseName.'/'.$integartionBaseName.'Handler.php')
                ) {
                    $integration = __NAMESPACE__. "\\{$integartionBaseName}\\{$integartionBaseName}Handler";
                    if (method_exists($integration, 'registerHooks')) {
                        $integration::registerHooks();
                    }
                }
            }
        }
    }

    /**
     * Checks a Integration Exists or not
     *
     * @param  String $name Name of Integration
     * @return boolean
     */
    protected static function isExists($name)
    {
        if (class_exists("BitCode\\WELZP\\Integration\\{$name}\\{$name}Handler")) {
            return "BitCode\\WELZP\\Integration\\{$name}\\{$name}Handler";
        } else {
            return false;
        }
    }

    public function save($data)
    {
        $missing_field = null;
        if (empty($data->formId)) {
            $missing_field = 'formId';
        }
        if (empty($data->type)) {
            $missing_field = 'Integration type';
        }
        if (!is_null($missing_field)) {
            wp_send_json_error(sprintf(__('%s cann\'t be empty', 'bitwelzp'), $missing_field));
        }
        $integrationName = !empty($data->name) ? $data->name : '';
        $integrationType = $data->type;
        unset($data->type, $data->name);
        $integrationDetails = wp_json_encode($data);
        $integrationCategory = 'integration';
        $integrationHandler = new IntegrationHandler($data->formId);
        $saveStatus = $integrationHandler->saveIntegration($integrationName, $integrationType, $integrationDetails, $integrationCategory);
        if (is_wp_error($saveStatus)) {
            wp_send_json_error($saveStatus->get_error_message());
        }
        wp_send_json_success(['id' => $saveStatus, 'msg' => __('Integration saved successfully', 'bitwelzp')]);
    }

    public function update($data)
    {
        $missing_field = null;
        if (empty($data->formId)) {
            $missing_field = 'formId';
        }
        if (empty($data->id)) {
            $missing_field = 'Integration id';
        }
        if (empty($data->type)) {
            $missing_field = 'Integration type';
        }
        if (!is_null($missing_field)) {
            wp_send_json_error(sprintf(__('%s cann\'t be empty', 'bitwelzp'), $missing_field));
        }
        $integrationName = !empty($data->name) ? $data->name : '';
        $integrationType = $data->type;
        $integrationId = $data->id;
        $integrationHandler = new IntegrationHandler($data->formId);
        unset($data->type, $data->name, $data->formId);
        $integrationDetails = wp_json_encode($data);
        $integrationCategory = 'integration';
        $updateStatus = $integrationHandler->updateIntegration($integrationId, $integrationName, $integrationType, $integrationDetails, $integrationCategory);
        if (is_wp_error($updateStatus) && $updateStatus->get_error_code() !== 'result_empty') {
            wp_send_json_error($updateStatus->get_error_message());
        }
        wp_send_json_success(__('Integration updated successfully', 'bitwelzp'));
    }

    public function delete($data)
    {
        $missing_field = null;
        if (empty($data->formId)) {
            $missing_field = 'formId';
        }
        if (empty($data->id)) {
            $missing_field = 'Integration id';
        }
        if (!is_null($missing_field)) {
            wp_send_json_error(sprintf(__('%s cann\'t be empty', 'bitwelzp'), $missing_field));
        }
        $integrationHandler = new IntegrationHandler($data->formId);
        $deleteStatus = $integrationHandler->deleteIntegration($data->id);
        if (is_wp_error($deleteStatus)) {
            wp_send_json_error($deleteStatus->get_error_message());
        }
        wp_send_json_success(__('Integration deleted successfully', 'bitwelzp'));
    }
    
    public function toggle_status($data)
    {
        $missing_field = null;
        if (!property_exists($data, 'status')) {
            $missing_field = 'status';
        }
        if (empty($data->id)) {
            $missing_field = 'Integration id';
        }
        if (!is_null($missing_field)) {
            wp_send_json_error(sprintf(__('%s cann\'t be empty', 'bitwelzp'), $missing_field));
        }
        $integrationHandler = new IntegrationHandler($data->formId);
        $toggleStatus = $integrationHandler->updateStatus($data->id, $data->status);
        if (is_wp_error($toggleStatus)) {
            wp_send_json_error($toggleStatus->get_error_message());
        }
        wp_send_json_success(__('Integration status changed successfully', 'bitwelzp'));
    }
    

    /**
     * This function helps to execute Integration
     *
     * @param Array   $integrations List  of integrstion to execute.
     *                              Element   will be json string like {"id":1}
     * @param Array   $fieldValues  Values of submitted fields
     * @param Integer $formID       ID of current form
     *
     * @return void                  Nothing to return
     */
    public static function executeIntegrations($formID, $fieldValues)
    {
        $integrationHandler = new IntegrationHandler($formID);
        $integrations = $integrationHandler->getAllIntegration('integration', 'Zoho CRM', 1);
        if (!is_wp_error($integrations)) {
            foreach ($integrations as $integrationDetails) {
                $integrationName = is_null($integrationDetails) ? null : ucfirst(str_replace(' ', '', $integrationDetails->integration_type));
                if (!is_null($integrationName) && static::isExists($integrationName)) {
                    $integration = static::isExists($integrationName);
                    $handler = new $integration($integrationDetails->id, $formID);
                    $handler->execute($integrationHandler, $integrationDetails, $fieldValues);
                }
            }
        }
    }
}
