<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    echo file_partial('meta');
    echo file_partial('script-top');
    echo $template['metadata'];
    ?>
</head>

<body>

<div id="root" style="display: none; position: relative">
    <transition name="slide-x-transition">
        <v-app v-if="isMounted">
            <?php echo file_partial('frontend-navbar'); ?>
            <?php echo $template['body']; ?>
            <?php echo file_partial('frontend-footer'); ?>

            <div class="sticky-chatmedia pa-2 elevation-1">
                <div class="sticky-chatmedia--title">Chat Us</div>
                <whats-app-btn rounded depressed hide-text href="https://api.whatsapp.com/send/?phone=6281355334442&text=Assallamu%20allaikum%20wr.%20wb.%20&app_absent=0" target="_blank"></whats-app-btn>
                <telegram-btn rounded depressed hide-text href="https://t.me/Helpdesk_kallagroup" target="_blank"></telegram-btn>
            </div>
        </v-app>
    </transition>
</div>

<?php echo file_partial('script-bottom'); ?>
</body>
</html>