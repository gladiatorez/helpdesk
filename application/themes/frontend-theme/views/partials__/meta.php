<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<link rel="apple-touch-icon" sizes="180x180" href="<?php echo base_url('apple-touch-icon.png') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo base_url('favicon-32x32.png') ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url('favicon-16x16.png') ?>">
<link rel="manifest" href="<?php echo base_url('site.webmanifest') ?>">

<?php if ($template['title'] != $template['page_title']) { ?>
	<title><?php echo $site_name_full . ' - ' . $template['title'] . ' :: ' . $template['page_title']; ?></title>
<?php 
} else { ?>
	<title><?php echo $site_name_full . ' - ' . $template['title']; ?></title>
<?php 
} ?>

<link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined" rel="stylesheet">