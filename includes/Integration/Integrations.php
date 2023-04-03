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
use WP_REST_Request;
use WP_REST_Server;

final class Integrations
{
    public function registerAjax()
    {
        $dirs = new FilesystemIterator(__DIR__);
        foreach ($dirs as $dirInfo) {
            if ($dirInfo->isDir()) {
                $integartionBaseName = basename($dirInfo);
                if (file_exists(__DIR__ . '/' . $integartionBaseName)
                    && file_exists(__DIR__ . '/' . $integartionBaseName . '/' . $integartionBaseName . 'Handler.php')
                ) {
                    $integration = __NAMESPACE__ . "\\{$integartionBaseName}\\{$integartionBaseName}Handler";
                    if (method_exists($integration, 'registerAjax')) {
                        $integration::registerAjax();
                    }
                }
            }
        }
    }

    public function registerHooks()
    {
        add_action('rest_api_init', [$this, 'loadApi']);
        Route::post('integration/save', [$this, 'save']);
        Route::post('integration/update', [$this, 'update']);
        Route::post('integration/delete', [$this, 'delete']);
        Route::post('integration/toggleStatus', [$this, 'toggle_status']);
        $dirs = new FilesystemIterator(__DIR__);
        foreach ($dirs as $dirInfo) {
            if ($dirInfo->isDir()) {
                $integartionBaseName = basename($dirInfo);
                if (file_exists(__DIR__ . '/' . $integartionBaseName)
                    && file_exists(__DIR__ . '/' . $integartionBaseName . '/' . $integartionBaseName . 'Handler.php')
                ) {
                    $integration = __NAMESPACE__ . "\\{$integartionBaseName}\\{$integartionBaseName}Handler";
                    if (method_exists($integration, 'registerHooks')) {
                        $integration::registerHooks();
                    }
                }
            }
        }
    }

    public function loadApi()
    {
        $args = [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'handleRedirect'],
            'permission_callback' => '__return_true',
        ];
        register_rest_route(
            'bitwelzp',
            '/redirect',
            [$args]
        );
    }

    public function handleRedirect(WP_REST_Request $request)
    {
        $state = $request->get_param('state');
        $parsed_url = parse_url(get_site_url());
        $site_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
        $site_url .= empty($parsed_url['port']) ? null : ':' . $parsed_url['port'];
        if (strpos($state, $site_url) === false) {
            return new WP_Error('404');
        }
        $params = $request->get_params();
        unset($params['rest_route'], $params['state']);
        if (wp_redirect($state . '&' . http_build_query($params), 302)) {
            exit;
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
