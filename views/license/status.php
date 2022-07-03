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
    <?php
    if (isset($_POST) && isset($_POST['disconnect'])) {
        include_once BITWELZP_PLUGIN_DIR_PATH . 'includes/Core/Update/API.php';
        $activationStatus = \BitCode\WELZP\Core\Update\API::disconnectLicense();
        if ($activationStatus === true) {
            echo "License deactivated successfully";
        } else {
            echo "<div class='error'>". $activationStatus . "</div>"; ?>
    <br>
    <br>
    <form action="" method="post">
        Disconnect this site from license
        <input type="submit" name="disconnect" value="disconnect">
    </form>
    <?php
        }
    } else {
        if (!empty($integrateStatus['expireIn'])) {
            $expireInDays = (strtotime($integrateStatus['expireIn']) - time()) / DAY_IN_SECONDS;
            if ($expireInDays < 25) {
                $notice = $expireInDays > 0 ?
                    sprintf(__("Bit Form Pro License will expire in %s days", "bitformpro"), (int) $expireInDays)
                     : __("Bit Form Pro License is expired", "bitformpro")
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p><?php echo $notice; ?></p>
                </div>
                <?php
            }
        }
        ?>
        <br>
        <br>
    <form action="" method="post">
        Disconnect this site from license
        <input type="submit" name="disconnect" value="disconnect">
    </form>
    <?php
    }
    ?>
</div>