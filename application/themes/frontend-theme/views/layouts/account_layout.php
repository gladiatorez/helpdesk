<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

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

<div id="root" style="display: none">
  <transition name="slide-x-transition">
    <v-app v-if="isMounted">
      <account-drawer></account-drawer>
      <?php echo file_partial('frontend-navbar'); ?>
      <!-- grey lighten-3 -->
      <v-content class="white">
        <account-menu class="hidden-md-and-up"></account-menu>
        <?php echo $template['body']; ?>
      </v-content>
    </v-app>
  </transition>
</div>

<?php

echo Asset::render_js('vue');
echo Asset::render_js('vuetify');

if (ENVIRONMENT === 'development') {
  echo Asset::render_js('webpack-dev-server');
  echo Asset::render_js('webpack-profiler');
}

echo Asset::render_js('account-bundle');
echo Asset::render_js('page');
?>
</body>
</html>