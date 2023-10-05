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

<div id="root">
<!-- <v-app> -->
  <!-- <transition name="slide-x-transition"> -->
    
    <?php // echo file_partial('backend-sidemenu'); ?>
    <?php // echo file_partial('backend-navbar'); ?>

    <!-- <v-content class="app-content"> -->
      <!--<app-page-header></app-page-header>-->
      <!-- <div class="v-content__wrap--inner">
        <v-breadcrumbs :items="itemBreadcrumbs" class="pl-4 pr-4 pb-0">
          <v-icon slot="divider">la-angle-right</v-icon>
        </v-breadcrumbs> -->
        <?php // echo $template['body']; ?>

        <!-- <v-footer class="pa-4 backend-footer" color="white" height="60">
          <small class="blue-grey--text text--lighten-1">
            {{ new Date().getFullYear() }} &copy; 
            <a href="http://it.kallagroup.co.id" class="font-weight-bold" style="text-decoration:none">CICT Kalla Group</a>
          </small>
        </v-footer> -->
      <!-- </div> -->
    <!-- </v-content> -->
    
  <!-- </transition> -->
<!-- </v-app> -->
</div>

<?php echo file_partial('script-bottom'); ?>
</body>
</html>