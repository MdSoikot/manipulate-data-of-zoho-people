<?php

namespace BitCode\WELZP\Admin\Log;

use BitCode\WELZP\Core\Database\LogModel;
use BitCode\WELZP\Core\Util\IpTool;

final class Handler
{
    //Fetch all activity logs, newest first
    public function get()
    {
        $result = (new LogModel())->get('*', [], null, null, 'id', 'DESC');
        if (is_wp_error($result)) {
            wp_send_json_success([]);
        }
        //Model results are keyed by primary key; reindex so JSON encodes as an array
        wp_send_json_success(array_values($result));
    }

    /**
     * Record an activity log entry. Never interrupts the caller's flow.
     *
     * @param string            $action      machine key, e.g. employee_delete, review_add
     * @param string            $entityType  employee | review | integration
     * @param string|int        $entityId    id of the affected record ('' when n/a)
     * @param array|object|null $beforeState record state before the change
     * @param array|object|null $afterState  record state after the change
     */
    public static function save($action, $entityType, $entityId, $beforeState = null, $afterState = null)
    {
        $userId = get_current_user_id();
        if ($userId) {
            $user = wp_get_current_user();
            $userName = $user->display_name ? $user->display_name : $user->user_login;
        } else {
            $userName = (function_exists('wp_doing_cron') && wp_doing_cron()) ? 'System (Cron)' : 'Guest';
        }

        $beforeState = static::sanitizeState($beforeState);
        $afterState = static::sanitizeState($afterState);

        (new LogModel())->insert(
            [
                'action'       => $action,
                'entity_type'  => $entityType,
                'entity_id'    => is_null($entityId) ? '' : (string) $entityId,
                'before_state' => is_null($beforeState) ? null : wp_json_encode($beforeState),
                'after_state'  => is_null($afterState) ? null : wp_json_encode($afterState),
                'user_id'      => $userId,
                'user_name'    => $userName,
                'ip'           => IpTool::getIP(),
                'created_at'   => current_time('mysql'),
            ]
        );
    }

    //Normalize a state snapshot to an array and drop keys that carry no audit value
    private static function sanitizeState($state)
    {
        if (is_null($state)) {
            return null;
        }

        if (\is_object($state)) {
            $state = (array) $state;
        }

        if (!\is_array($state)) {
            return ['value' => (string) $state];
        }

        unset($state['_ajax_nonce'], $state['nonce'], $state['editRowId']);

        return $state;
    }

    //Delete selected log entries
    public function delete($data)
    {
        if (empty($data->ids)) {
            wp_send_json_error(__('Log Id required', 'bitwelzp'));
        }

        $logModel = new LogModel();
        $deleteStatus = $logModel->bulkDelete(['id' => array_map('absint', (array) $data->ids)]);

        if (is_wp_error($deleteStatus)) {
            wp_send_json_error($deleteStatus->get_error_code());
        }
        wp_send_json_success(__('Log deleted successfully', 'bitwelzp'));
    }

    //Delete every log entry
    public function clear()
    {
        global $wpdb;
        $wpdb->query("DELETE FROM `{$wpdb->prefix}bitwelzp_log_details`");
        wp_send_json_success(__('All logs cleared', 'bitwelzp'));
    }
}
