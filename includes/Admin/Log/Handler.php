<?php
namespace BitCode\WELZP\Admin\Log;

use BitCode\WELZP\Core\Database\LogModel;
final class Handler
{
    public function __construct()
    {
        //
    }

    public function get($data)
    {
        if (!isset($data->id)) {
            wp_send_json_error('Integration Id cann\'t be empty');
        }
        $logModel = new LogModel();
        $countResult = $logModel->count(['integration_id' => $data->id]);
        if (is_wp_error($countResult)) {
            wp_send_json_success([
                'count' => 0,
                'data' => [],
            ]);
        }
        $count = $countResult[0]->count;
        if ($count < 1) {
            wp_send_json_success([
                'count' => 0,
                'data' => [],
            ]); 
        }
        $offset = 0;
        $limit = 10;
        if (isset($data->offset)) {
            $offset = $data->offset;
        }
        if (isset($data->limit)) {
            $limit = $data->limit;
        }
        $result = $logModel->get('*', ['integration_id' => $data->id], $limit, $offset, 'id', 'desc');
        if (is_wp_error($result)) {
            wp_send_json_success([
                'count' => 0,
                'data' => [],
            ]);
        }
        wp_send_json_success([
            'count' => intval($count),
            'data' => $result,
        ]);
    }

    public static function save($form_id,$integration_id,$api_type,$response_type,$response_obj)
    {
        $logModel = new LogModel();
        $logModel->insert(
            [
                'form_id' => $form_id,
                'integration_id' => $integration_id,
                'api_type' => $api_type,
                'response_type' => $response_type,
                'response_obj' => $response_obj,
                'created_at' => current_time("mysql")
            ]
        );
    }
    
    public static function delete($data)
    {
        if (empty($data->id) && empty($data->integration_id)) {
            wp_send_json_error('Integration Id or Log Id required');
        }
        $condition = null;
        if (!empty($data->id)) {
            if (is_array($data->id)) {
                $condition = [
                    'id' =>  $data->id
                ];
            } else {
                $condition = [
                    'id' => $data->id
                ];
            }
        }
        if (!empty($data->integration_id)) {
            $condition = [
                'integration_id' => $data->integration_id
            ];
        }
        $logModel = new LogModel();
        $deleteStatus = $logModel->bulkDelete($condition);

        if (is_wp_error($deleteStatus)) {
            wp_send_json_error($deleteStatus->get_error_code());
        }
        wp_send_json_success(__('Log deleted successfully', 'bitwelzp'));
    }
}
