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

<div id="root" style="display: none">
  <v-app>
    <?php echo $template['body']; ?>
  </v-app>
</div>

<?php

echo Asset::render_js('vue');
echo Asset::render_js('vuetify');

if (ENVIRONMENT === 'development') {
	echo Asset::render_js('webpack-dev-server');
	echo Asset::render_js('webpack-profiler');
}

echo Asset::render_js('page');

?>
</body>
</html>