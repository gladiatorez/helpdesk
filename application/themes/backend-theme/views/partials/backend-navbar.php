<v-app-bar app fixed prominent flat
	class="backend-navbar"
	:height="50"
	color="white"
	extension-height="45">
	<v-toolbar-side-icon @click.stop="drawer = !drawer" class="hidden-lg-and-up"></v-toolbar-side-icon>
	
	<v-tooltip bottom>
		<v-btn icon slot="activator" href="<?php echo site_url(); ?>" target="_blank">
			<v-icon>la la-newspaper-o</v-icon>
		</v-btn>
		<span>Frontend</span>
	</v-tooltip>

	<v-spacer></v-spacer>

	<v-menu offset-x
		v-model="menuProfile"
		:close-on-content-click="false"
		:nudge-width="200">
		<v-btn icon slot="activator">
			<v-icon>ms-Icon--person</v-icon>
		</v-btn>

		<v-card>
			<v-list>
				<v-list-tile avatar>
					<v-list-tile-avatar>
						<v-icon class="grey lighten-4 icon-profile">ms-Icon--person</v-icon>
					</v-list-tile-avatar>

					<v-list-tile-content>
						<v-list-tile-title>
							<?php echo $current_user->profile->full_name ? $current_user->profile->full_name : 'User login'; ?>
						</v-list-tile-title>
						<v-list-tile-sub-title>
							<?php echo $current_user->profile->position ? $current_user->profile->position : ''; ?>
						</v-list-tile-sub-title>
					</v-list-tile-content>

					<v-list-tile-action>
						
					</v-list-tile-action>
				</v-list-tile>
			</v-list>

			<v-divider></v-divider>

			<v-card-actions>
				<v-btn flat block color="primary" to="/profile"  @click="menuProfile = false">Profile</v-btn>
				<v-btn flat block color="error" href="<?php echo site_url_backend('auth/logout') ?>">Logout</v-btn>
			</v-card-actions>
		</v-card>
	</v-menu>

	<template slot="extension">
		<app-page-header></app-page-header>
	</template>
</v-app-bar>
