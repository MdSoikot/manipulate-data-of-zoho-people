<?php

namespace BitCode\WELZP\Core\Util;

final class Route
{
    private static $prefix = 'bitwelzp_';
    private static $invokeable;

    public static function get($hook, $invokeable)
    {
        if ($_SERVER["REQUEST_METHOD"] != "GET") {
            return;
        }
        static::$invokeable[static::$prefix . $hook][$_SERVER["REQUEST_METHOD"]] = $invokeable;
        add_action("wp_ajax_" . static::$prefix . $hook, [__CLASS__, 'action']);
        add_action("wp_ajax_nopriv_" . static::$prefix . $hook, [__CLASS__, 'action']);
    }

    public static function post($hook, $invokeable)
    {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            return;
        }
        static::$invokeable[static::$prefix . $hook][$_SERVER["REQUEST_METHOD"]] = $invokeable;
        add_action("wp_ajax_" . static::$prefix . $hook, [__CLASS__, 'action']);
        add_action("wp_ajax_nopriv_" . static::$prefix . $hook, [__CLASS__, 'action']);
    }
    public static function put($hook, $invokeable)
    {
        if ($_SERVER["REQUEST_METHOD"] != "PUT") {
            return;
        }
        static::$invokeable[static::$prefix . $hook][$_SERVER["REQUEST_METHOD"]] = $invokeable;
        add_action("wp_ajax_" . static::$prefix . $hook, [__CLASS__, 'action']);
        add_action("wp_ajax_nopriv_" . static::$prefix . $hook, [__CLASS__, 'action']);
    }

    public static function action()
    {
        if (isset($_REQUEST['_ajax_nonce']) && wp_verify_nonce(sanitize_text_field($_REQUEST['_ajax_nonce']), 'bitcffp_nonce')) {
            $invokeable = static::$invokeable[sanitize_text_field($_REQUEST['action'])][$_SERVER["REQUEST_METHOD"]];
            unset($_POST['_ajax_nonce'], $_POST['action'], $_GET['_ajax_nonce'], $_GET['action']);
            if (method_exists($invokeable[0], $invokeable[1])) {
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], 'form-data') === false && strpos($_SERVER["CONTENT_TYPE"], 'x-www-form-urlencoded') === false) {
                        $inputJSON = file_get_contents('php://input');
                        $data = is_string($inputJSON) ? \json_decode($inputJSON) : $inputJSON;
                    } else {
                        $data = (object) $_POST;
                    }
                } else {
                    $data =  (object) $_GET;
                }

                $reflectionMethod = new \ReflectionMethod($invokeable[0], $invokeable[1]);
                $reflectionMethod->invoke(new $invokeable[0](), $data);
            } else {
                wp_send_json_error('Method doesn\'t exists');
            }
        } else {
            wp_send_json_error(
                __(
                    'Token expired',
                    'bitwelzp'
                ),
                401
            );
        }
    }
}
