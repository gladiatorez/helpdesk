<template>
  <v-menu offset-y>
    <template v-slot:activator="{ on }">
      <v-btn block text v-on="on">
        Explore Account
        <v-icon right style="font-size: 13px;">ms-Icon ms-Icon--ChevronDown</v-icon>
      </v-btn>
    </template>
    <v-list>
      <v-list-item
          to="/"
          active-class="success--text">
        <v-list-item-icon>
          <v-icon>ms-Icon ms-Icon--News</v-icon>
        </v-list-item-icon>
        <v-list-item-title>
          Information
        </v-list-item-title>
      </v-list-item>

      <v-list-item
          to="/personalinfo"
          active-class="success--text">
        <v-list-item-icon>
          <v-icon>ms-Icon ms-Icon--Contact</v-icon>
        </v-list-item-icon>
        <v-list-item-title>
          Personal Info
        </v-list-item-title>
      </v-list-item>

      <v-list-item
          to="/history"
          active-class="success--text">
        <v-list-item-icon>
          <v-icon>ms-Icon ms-Icon--Clock</v-icon>
        </v-list-item-icon>
        <v-list-item-title>
          History
        </v-list-item-title>
      </v-list-item>

      <v-list-item
          :href="hrefLogout"
          active-class="success--text">
        <v-list-item-icon>
          <v-icon class="error--text">close</v-icon>
        </v-list-item-icon>
        <v-list-item-title class="error--text">
          Logout
        </v-list-item-title>
      </v-list-item>
    </v-list>
  </v-menu>
</template>

<script>
export default {
	name: 'account-menu',
	data() {
		return {
			showMenu: false,
		}
	},
	computed: {
		hrefLogout() {
			return SITE_URL + 'logout';
		}
	}
}
</script>

