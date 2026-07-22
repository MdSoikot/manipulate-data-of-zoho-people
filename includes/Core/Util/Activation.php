<?php

namespace BitCode\WELZP\Core\Util;

use BitCode\WELZP\Core\Database\DB;

/**
 * Class handling plugin activation.
 *
 * @since 1.0.0
 */
final class Activation
{
    public function activate()
    {
        add_action('bitwelzp_activation', array($this, 'install'));
    }

    public function install()
    {
        $installed = get_option('bitwelzp_installed');
        if ($installed) {
            $oldversion = get_option('bitwelzp_version');
        }
    
        if (!$installed || version_compare($oldversion, BITWELZP_VERSION, '!=')) {
            DB::migrate();
            update_option('bitwelzp_installed', time());
        }
        update_option('bitwelzp_version', BITWELZP_VERSION);
    }
}
