<?php

/**
 * Provides IP related functionality
 */

namespace BitCode\WELZP\Core\Util;

final class IpTool
{
    /**
     * Check ip address
     *
     * @return ip_addr IP address of current visitor
     */
    private static function _checkIP()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    /**
     * Check device info
     *
     * @return void
     */
    private static function _checkDevice()
    {
        if (isset($_SERVER)) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            global $HTTP_SERVER_VARS;
            if (isset($HTTP_SERVER_VARS)) {
                $user_agent = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
            } else {
                global $HTTP_USER_AGENT;
                $user_agent = $HTTP_USER_AGENT;
            }
        }
        return IpTool::_getBrowserName($user_agent) . '|' . IpTool::_getOS($user_agent);
    }
    /**
     * Get browser name
     *
     * @link https://stackoverflow.com/questions/18070154/get-operating-system-info
     *
     * @param string $user_agent $_SERVER['HTTP_USER_AGENT']
     *
     * @return void
     */
    private static function _getBrowserName($user_agent)
    {
        // Make case insensitive.
        $t = strtolower($user_agent);

        // If the string *starts* with the string, strpos returns 0 (i.e., FALSE). Do a ghetto hack and start with a space.
        // "[strpos()] may return Boolean FALSE, but may also return a non-Boolean value which evaluates to FALSE."
        //     http://php.net/manual/en/function.strpos.php
        $t = " " . $t;

        // Humans / Regular Users
        if (strpos($t, 'opera') || strpos($t, 'opr/')) {
            return 'Opera';
        } elseif (strpos($t, 'edge')) {
            return 'Edge';
        } elseif (strpos($t, 'Edg')) {
            return 'Edge';
        } elseif (strpos($t, 'chrome')) {
            return 'Chrome';
        } elseif (strpos($t, 'safari')) {
            return 'Safari';
        } elseif (strpos($t, 'firefox')) {
            return 'Firefox';
        } elseif (strpos($t, 'msie') || strpos($t, 'trident/7')) {
            return 'Internet Explorer';
        } elseif (strpos($t, 'google')) {
            return 'Googlebot';
        } elseif (strpos($t, 'bing')) {
            return 'Bingbot';
        } elseif (strpos($t, 'slurp')) {
            return 'Yahoo! Slurp';
        } elseif (strpos($t, 'duckduckgo')) {
            return 'DuckDuckBot';
        } elseif (strpos($t, 'baidu')) {
            return 'Baidu';
        } elseif (strpos($t, 'yandex')) {
            return 'Yandex';
        } elseif (strpos($t, 'sogou')) {
            return 'Sogou';
        } elseif (strpos($t, 'exabot')) {
            return 'Exabot';
        } elseif (strpos($t, 'msn')) {
            return 'MSN';
        }

        // Common Tools and Bots
        elseif (strpos($t, 'mj12bot')) {
            return 'Majestic';
        } elseif (strpos($t, 'ahrefs')) {
            return 'Ahrefs';
        } elseif (strpos($t, 'semrush')) {
            return 'SEMRush';
        } elseif (strpos($t, 'rogerbot') || strpos($t, 'dotbot')) {
            return 'Moz';
        } elseif (strpos($t, 'frog') || strpos($t, 'screaming')) {
            return 'Screaming Frog';
        } elseif (strpos($t, 'facebook')) {
            return 'Facebook';
        } elseif (strpos($t, 'pinterest')) {
            return 'Pinterest';
        } elseif (
            strpos($t, 'crawler') || strpos($t, 'api')
            || strpos($t, 'spider') || strpos($t, 'http')
            || strpos($t, 'bot') || strpos($t, 'archive')
            || strpos($t, 'info') || strpos($t, 'data')
        ) {
            return 'Bot';
        }

        return 'Other (Unknown)';
    }
    /**
     * Provide Operating System Information of User
     *
     * @link https://stackoverflow.com/questions/18070154/get-operating-system-info
     *
     * @return void
     */
    private static function _getOS($user_agent)
    {
        $ros[] = array('Windows XP', 'Windows XP');
        $ros[] = array('Windows NT 5.1|Windows NT5.1', 'Windows XP');
        $ros[] = array('Windows 2000', 'Windows 2000');
        $ros[] = array('Windows NT 5.0', 'Windows 2000');
        $ros[] = array('Windows NT 4.0|WinNT4.0', 'Windows NT');
        $ros[] = array('Windows NT 5.2', 'Windows Server 2003');
        $ros[] = array('Windows NT 6.0', 'Windows Vista');
        $ros[] = array('Windows NT 7.0', 'Windows 7');
        $ros[] = array('Windows CE', 'Windows CE');
        $ros[] = array(
            '(media center pc).([0-9]{1,2}\.[0-9]{1,2})',
            'Windows Media Center'
        );
        $ros[] = array('(win)([0-9]{1,2}\.[0-9x]{1,2})', 'Windows');
        $ros[] = array('(win)([0-9]{2})', 'Windows');
        $ros[] = array('(windows)([0-9x]{2})', 'Windows');
        // Doesn't seem like these are necessary...not totally sure though..
        //$ros[] = array('(winnt)([0-9]{1,2}\.[0-9]{1,2}){0,1}', 'Windows NT');
        //$ros[] = array('(windows nt)(([0-9]{1,2}\.[0-9]{1,2}){0,1})', 'Windows NT'); // fix by bg
        $ros[] = array('Windows ME', 'Windows ME');
        $ros[] = array('Win 9x 4.90', 'Windows ME');
        $ros[] = array('Windows 98|Win98', 'Windows 98');
        $ros[] = array('Windows 95', 'Windows 95');
        $ros[] = array('(windows)([0-9]{1,2}\.[0-9]{1,2})', 'Windows');
        $ros[] = array('win32', 'Windows');
        $ros[] = array('(java)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})', 'Java');
        $ros[] = array('(Solaris)([0-9]{1,2}\.[0-9x]{1,2}){0,1}', 'Solaris');
        $ros[] = array('dos x86', 'DOS');
        $ros[] = array('unix', 'Unix');
        //Android
        $ros[] = array('SM', 'Samsung');
        $ros[] = array('HTC', 'HTC');
        $ros[] = array('LG', 'LG');
        $ros[] = array('Microsoft', 'Microsoft');
        $ros[] = array('Pixel', 'Pixel');
        $ros[] = array('MI', 'Xiaomi');
        $ros[] = array('Xiaomi', 'Xiaomi');
        $ros[] = array('Android', 'Android');
        $ros[] = array('android', 'Android');

        //iPhone
        $ros[] = array('iPhone', 'iPhone');

        $ros[] = array('Mac OS X', 'Mac OS X');
        $ros[] = array('Mac OS X Puma', 'Mac OS X 10.1[^0-9]');
        $ros[] = array('Mac_PowerPC', 'Macintosh PowerPC');
        $ros[] = array('(mac|Macintosh)', 'Mac OS');
        $ros[] = array('(sunos)([0-9]{1,2}\.[0-9]{1,2}){0,1}', 'SunOS');
        $ros[] = array('(beos)([0-9]{1,2}\.[0-9]{1,2}){0,1}', 'BeOS');
        $ros[] = array('(risc os)([0-9]{1,2}\.[0-9]{1,2})', 'RISC OS');
        $ros[] = array('os\/2', 'OS/2');
        $ros[] = array('freebsd', 'FreeBSD');
        $ros[] = array('openbsd', 'OpenBSD');
        $ros[] = array('netbsd', 'NetBSD');
        $ros[] = array('irix', 'IRIX');
        $ros[] = array('plan9', 'Plan9');
        $ros[] = array('osf', 'OSF');
        $ros[] = array('aix', 'AIX');
        $ros[] = array('GNU Hurd', 'GNU Hurd');
        $ros[] = array('(fedora)', 'Linux - Fedora');
        $ros[] = array('(kubuntu)', 'Linux - Kubuntu');
        $ros[] = array('(ubuntu)', 'Linux - Ubuntu');
        $ros[] = array('(debian)', 'Linux - Debian');
        $ros[] = array('(CentOS)', 'Linux - CentOS');
        $ros[] = array(
            '(Mandriva).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)',
            'Linux - Mandriva'
        );
        $ros[] = array(
            '(SUSE).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)',
            'Linux - SUSE'
        );
        $ros[] = array('(Dropline)', 'Linux - Slackware (Dropline GNOME)');
        $ros[] = array('(ASPLinux)', 'Linux - ASPLinux');
        $ros[] = array('(Red Hat)', 'Linux - Red Hat');
        // Loads of Linux machines will be detected as unix.
        // Actually, all of the linux machines I've checked have the 'X11' in the User Agent.
        //$ros[] = array('X11', 'Unix');
        $ros[] = array('(linux)', 'Linux');
        $ros[] = array('(amigaos)([0-9]{1,2}\.[0-9]{1,2})', 'AmigaOS');
        $ros[] = array('amiga-aweb', 'AmigaOS');
        $ros[] = array('amiga', 'Amiga');
        $ros[] = array('AvantGo', 'PalmOS');
        //$ros[] = array('(Linux)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3}(rel\.[0-9]{1,2}){0,1}-([0-9]{1,2}) i([0-9]{1})86){1}', 'Linux');
        //$ros[] = array('(Linux)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3}(rel\.[0-9]{1,2}){0,1} i([0-9]{1}86)){1}', 'Linux');
        //$ros[] = array('(Linux)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3}(rel\.[0-9]{1,2}){0,1})', 'Linux');
        $ros[] = array('[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3})', 'Linux');
        $ros[] = array('(webtv)/([0-9]{1,2}\.[0-9]{1,2})', 'WebTV');
        $ros[] = array('Dreamcast', 'Dreamcast OS');
        $ros[] = array('GetRight', 'Windows');
        $ros[] = array('go!zilla', 'Windows');
        $ros[] = array('gozilla', 'Windows');
        $ros[] = array('gulliver', 'Windows');
        $ros[] = array('ia archiver', 'Windows');
        $ros[] = array('NetPositive', 'Windows');
        $ros[] = array('mass downloader', 'Windows');
        $ros[] = array('microsoft', 'Windows');
        $ros[] = array('offline explorer', 'Windows');
        $ros[] = array('teleport', 'Windows');
        $ros[] = array('web downloader', 'Windows');
        $ros[] = array('webcapture', 'Windows');
        $ros[] = array('webcollage', 'Windows');
        $ros[] = array('webcopier', 'Windows');
        $ros[] = array('webstripper', 'Windows');
        $ros[] = array('webzip', 'Windows');
        $ros[] = array('wget', 'Windows');
        $ros[] = array('Java', 'Unknown');
        $ros[] = array('flashget', 'Windows');
        // delete next line if the script show not the right OS
        //$ros[] = array('(PHP)/([0-9]{1,2}.[0-9]{1,2})', 'PHP');
        $ros[] = array('MS FrontPage', 'Windows');
        $ros[] = array('(msproxy)/([0-9]{1,2}.[0-9]{1,2})', 'Windows');
        $ros[] = array('(msie)([0-9]{1,2}.[0-9]{1,2})', 'Windows');
        $ros[] = array('libwww-perl', 'Unix');
        $ros[] = array('UP.Browser', 'Windows CE');
        $ros[] = array('NetAnts', 'Windows');
        $ros[] = array('Android', 'Android');
        $file = count($ros);
        $os = '';
        for ($n = 0; $n < $file; $n++) {
            if (@preg_match('/' . $ros[$n][0] . '/i', $user_agent)) {
                $os = @$ros[$n][1];
                break;
            }
        }
        return trim($os);
    }

    /**
     * Set user details ip,cdevice, user_id, user's visited page, current mysql formatted time
     * 
     * @return Array of user details 
     */
    private static function _setUserDetail()
    {
        $user_details['ip'] = ip2long(IpTool::_checkIP());
        $user_details['device'] = IpTool::_checkDevice();
        $user_details['id'] = get_current_user_id();
        $user_details['page'] = is_object(get_post()) ? get_permalink(get_post()->ID) : null;
        $user_details['time'] = current_time("mysql");

        return $user_details;
    }

    /**
     * Provide user details
     * 
     * @return _setUserDetail user details array
     */
    public static function getUserDetail()
    {
        return IpTool::_setUserDetail();
    }

    /**
     * Provide user IP address
     * 
     * @return ip 
     */
    public static function getIP()
    {
        return IpTool::_checkIP();
    }
}
