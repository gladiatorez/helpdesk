<v-navigation-drawer app floating clipped fixed class="app-drawer">
  <V-container class="pt-5 mt-5">
    <v-list>
      <?php foreach ($faq_categories as $category) { ?>
        <v-list-tile href="<?php echo site_url('faq/category/' . $category['id']); ?>">
          <v-list-tile-title>
            <?php echo $category['name'] ?>
          </v-list-tile-title>
        </v-list-tile>
      <?php 
			} ?>
    </v-list>
  </V-container>
</v-navigation-drawer>