<v-navigation-drawer app dark
	:mini-variant.sync="miniDrawer"
	v-model="drawer"
	class="backend-sidemenu"
	width="220">
	<v-img :src="bgImage" height="100%">
		<v-toolbar flat :height="50" color="transparent">
			<app-logo app-name="<?php echo $site_name_full; ?>"></app-logo>
		</v-toolbar>
		<v-layout column
			class="fill-height"
			tag="v-list">
			<v-list dense class="scroll-y">
				<?php echo file_partial('main-menu'); ?>
			</v-list>
		</v-layout>
	</v-img>
</v-navigation-drawer>

<!--
<app-drawer v-model="drawer" app dark fixed stateless floating persistent :width="250" :mini-variant.sync="miniDrawer">
	<template slot="toolbar-title">
		<app-logo app-name="<?php echo $site_name_full; ?>"></app-logo>
	</template>
	<v-list dense class="scroll-y">
		<?php echo file_partial('main-menu'); ?>
	</v-list>
</app-drawer>
-->