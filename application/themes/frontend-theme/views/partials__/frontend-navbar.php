<v-app-bar app fixed light clipped-left clipped-right
    class="app-navbar"
    height="60" 
    :color="navbarColor" 
    :dark="navbarDark" 
    :flat="navbarFlat">
    <v-menu bottom left>
        <template v-slot:activator="{ on }">
            <v-btn icon small
                class="mr-3 ml-0"
                v-if="$vuetify.breakpoint.mdAndDown"
                v-on="on">
                <v-icon style="font-size: 19px" class="font-weight-bold">ms-Icon ms-Icon--GlobalNavButton</v-icon>
            </v-btn>
        </template>

        <v-list>
            <?php foreach ($menu_items as $menu_item) {
                echo sprintf(
                    '<v-list-item href="%s"><v-list-item-title class="%s">%s</v-list-item-title></v-list-item>',
                    $menu_item['url'],
                    $menu_item['active'] ? 'success--text' : '',
                    $menu_item['title']
                );
            } ?>
            <?php if (!frontendIsLoggedIn()) { ?>
                <v-list-item href="<?php echo site_url('login') ?>">
                    <v-list-item-title>LOGIN</v-list-item-title>
                </v-list-item>
                <v-list-item href="<?php echo site_url('join') ?>">
                    <v-list-item-title>JOIN</v-list-item-title>
                </v-list-item>
            <?php } ?>
        </v-list>
    </v-menu>

    <div class="v-toolbar__title">
        <a href="<?php echo site_url(); ?>" class="d-block" style="text-decoration: none;">
            <core-logo outlined app-title="<?php echo $site_name_full; ?>" style="width:135px"></core-logo>
        </a>
    </div>

    <div class="spacer"></div>

    <div class="v-toolbar__items hidden-sm-and-down">
        <?php foreach ($menu_items as $menu_item) {
            echo sprintf(
                '<v-btn text href="%s" class="%s">%s</v-btn>',
                $menu_item['url'],
                $menu_item['active'] ? 'success--text' : '',
                $menu_item['title']
            );
        } ?>
    </div>

    <search-button></search-button>
    <login-button href-login="<?php echo site_url('login') ?>" href-join="<?php echo site_url('join') ?>" :login-only="<?php echo frontendIsLoggedIn() ? 'true' : 'false'; ?>" :login-active="<?php echo $section === 'account' ? 'true' : 'false'; ?>"></login-button>
</v-app-bar>