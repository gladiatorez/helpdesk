<template>
	<v-navigation-drawer app dark
		:mini-variant="miniDrawer"
		v-model="drawer"
		class="backend-sidemenu"
		width="220"
		:src="bgImage">
		<template v-slot:prepend>
			<v-app-bar flat :height="50" color="transparent">
				<v-spacer />
				<v-slide-x-transition>
					<core-logo v-show="!miniDrawer" />
				</v-slide-x-transition>
				<v-spacer />
			</v-app-bar>
		</template>

		<template v-slot:default>
			<v-list dense shaped flat color="transparent">
				<template v-for="(menuItem, menuName) in menuItems">
					<template v-if="menuItem.items">
						<v-list-group :key="menuName">
							<template v-slot:activator>
								<v-list-item-icon>
									<v-icon>{{ menuItem.icon }}</v-icon>
								</v-list-item-icon>
								<v-list-item-content>
									<v-list-item-title>
										{{ $t(menuName) }}
									</v-list-item-title>
								</v-list-item-content>
							</template>

							<template v-for="(sub, keySub) in menuItem.items">
								<v-list-item 
									active-class="primary white--text"
									:key="menuName + '_' + keySub"
									:to="'/' + sub.url">
									<v-list-item-icon></v-list-item-icon>
									<v-list-item-content>
										<v-list-item-title>
											{{ $t(keySub) }}
										</v-list-item-title>
									</v-list-item-content>
								</v-list-item>
							</template>
						</v-list-group>
					</template>
					<template v-else>
						<v-list-item 
							active-class="primary"
							:key="menuName"
							:to="'/' + menuItem.url">
							<v-list-item-icon>
								<v-icon>{{ menuItem.icon }}</v-icon>
							</v-list-item-icon>
							<v-list-item-content>
								<v-list-item-title>
									{{ $t(menuName) }}
								</v-list-item-title>
							</v-list-item-content>
						</v-list-item>
					</template>
				</template>
			</v-list>
		</template>
		
		<template v-slot:append>
			<div class="d-flex align-items-center justify-center">
				<v-btn icon @click="toggleMiniDrawer">
					<v-icon v-if="!miniDrawer">menu_open</v-icon>
					<v-icon v-else>menu</v-icon>
				</v-btn>
			</div>
		</template>
	</v-navigation-drawer>
</template>

<script>
import SidebarBg from '../../../img/sidebar-bg.jpg'
export default {
	name: 'CoreDrawer',
	props: ['value'],
	computed: {
		bgImage() {
			return SidebarBg
		},
		menuItems() {
			return this.$store.state.layouts.menuItems;
		},
		drawer: {
			get() {
				return this.$store.state.layouts.drawer;
			},
			set(value) {
				this.$store.commit('layouts/setDrawer', value);
			}
		},
		miniDrawer() {
			return this.$store.state.layouts.miniDrawer;
		},
	},
	methods: {
		toggleMiniDrawer() {
			this.$store.commit('layouts/toggleMiniDrawer');
		}
	}
}
</script>
