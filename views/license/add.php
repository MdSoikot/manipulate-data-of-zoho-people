<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<style>
    .bitform-license {
        display: flex;
        padding: 10%;
        justify-content: center;
        align-items: center;
    }
    .error {
        color: #e52d2d;
        padding: 5px;
    }
</style>
<div class="bitform-license">
    <div>
        <?php
        if (isset($_POST) && isset($_POST['licenseKey'])) {
            include_once BITWELZP_PLUGIN_DIR_PATH . 'includes/Core/Update/API.php';
            $activationStatus = \BitCode\WELZP\Core\Update\API::activateLicense($_POST['licenseKey']);
            if ($activationStatus === true) {
                echo "License activated successfully";
            } else {
                echo '<div class="error">'. $activationStatus . '</div>'; ?>
                <br>
                <br>
        <form action="" method="post">
            <label for="licenseKey">
                License Key
            </label>
            <input type="text" name="licenseKey" id="" placehoder="Please enter your license key">
            <input type="submit">
        </form>
        <?php
            }
        } else {?>
        <form action="" method="post">
            <label for="licenseKey">
                License Key
            </label>
            <input type="text" name="licenseKey" id="" placehoder="Please enter your license key">
            <input type="submit">
        </form>
        <?php }?>
    </div>
</div>