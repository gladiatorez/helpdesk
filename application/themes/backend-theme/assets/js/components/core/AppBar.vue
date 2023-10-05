<template>
    <v-app-bar app fixed hide-on-scroll
        v-model="show"
        class="backend-navbar"
        height="50"
        color="white"
        extension-height="45">
<!--        scroll-target=".v-content__wrap"-->
        <v-app-bar-nav-icon 
            v-if="$vuetify.breakpoint.mdAndDown"
            @click="toggleDrawer" 
        />
        <core-logo outlined
            v-if="!$vuetify.breakpoint.mdAndDown"
            v-show="miniDrawer" 
            class="mr-6"
            style="width: 135px"
        />
        <v-tooltip bottom>
            <template v-slot:activator="{ on }">
                <v-btn icon v-on="on" :href="baseUrl" target="_blank">
                    <v-icon style="font-size:21px;">ms-Icon ms-Icon--News</v-icon>
                </v-btn>
            </template>
            <span>Frontend</span>
        </v-tooltip>
        <v-btn depressed text rounded href="https://asset-ict.kallagroup.co.id/" target="_blank">
            <span style="letter-spacing: normal" class="text-none">CICT Assets</span>
            <v-icon right style="font-size: 12px;">ms-Icon ms-Icon--OpenInNewTab</v-icon>
        </v-btn>
        <v-spacer />

        <v-menu offset-x
            v-model="menuProfile"
            :close-on-content-click="false"
            :nudge-width="200">
            <template v-slot:activator="{ on }">
                <v-btn depressed icon
                    v-on="on" 
                    style="margin-right:0px; min-width:auto;">
                    <v-icon style="font-size:21px;">ms-Icon ms-Icon--Contact</v-icon>
                </v-btn>
            </template>

            <v-card>
                <v-list>
                    <v-list-item>
                        <v-list-item-avatar>
                            <v-avatar color="primary">
                                <v-icon dark>account_circle</v-icon>
                            </v-avatar>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>{{ fullName }}</v-list-item-title>
                            <v-list-item-subtitle>{{ position }}</v-list-item-subtitle>
                        </v-list-item-content>
                    </v-list-item>
                </v-list>

                <v-divider />

                <v-card-actions>
                    <v-btn text color="primary" to="/profile"  @click="menuProfile = false">Profile</v-btn>
                    <v-spacer />
                    <v-btn text color="error" :href="logoutUrl">Logout</v-btn>
                </v-card-actions>
            </v-card>
        </v-menu>

        <template v-slot:extension>
            <core-page-header />
        </template>
    </v-app-bar>    
</template>

<script>
export default {
    name: 'CoreAppBar',
    props: {
        value: Boolean
    },
    data() {
        return {
            menuProfile: false,
        }
    },
    computed: {
        show: {
            get() {
                return this.value
            },
            set(payload) {
                this.$emit('input', payload)
            }
        },
        miniDrawer() {
			return this.$store.state.layouts.miniDrawer;
        },
        baseUrl() {
            return window.BASE_URL;
        },
        logoutUrl() {
            return window.SITE_URL + '/auth/logout';
        },
        fullName() {
            return this.$store.state.userLogin.fullName
        },
        position() {
            return this.$store.state.userLogin.position
        },
    },
    methods: {
        toggleDrawer() {
            this.$store.commit('layouts/toggleDrawer')
        },
    }
}
</script>