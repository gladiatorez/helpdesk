window.CKEDITOR_BASEPATH = "/ckeditor/";

import Vue from "vue";
import router from "./router";
import store from "./store";
import api from "./utils/api";
import filters from "./utils/filters";
import "./components/core";

import "../styles/backend-theme.scss";

import CoreSnackbars from "./components/Snackbars";
Vue.use(CoreSnackbars);

import App from "./components/App.vue";

import(/* webpackChunkName: "plugins" */ "./plugins").then(() => {
    Vue.prototype.$axios = api();
    window.VUE = new Vue({
        el: "#root",
        render: h => h(App),
        vuetify,
        router,
        store,
        filters: {
            ...filters
        },
        data: {
            drawer: true,
            miniDrawer: false,
            menuProfile: false,
            isMounted: false,
            bgImage: require("../img/sidebar-bg.jpg")
        },
        computed: {},
        async created() {
            this.$store.commit("layouts/setSiteName", window.SITE_TITLE_FULL);
            this.$store.commit(
                "layouts/setSiteNameAbbr",
                window.SITE_TITLE_ABBR
            );
            this.$store.commit("layouts/setMenuItems", window.MENU_ITEMS);
            this.$store.commit(
                "userLogin/setFullName",
                ufhy.user.profile.fullName
                    ? ufhy.user.profile.fullName
                    : "User login"
            );
            this.$store.commit(
                "userLogin/setPosition",
                ufhy.user.profile.position ? ufhy.user.profile.position : ""
            );
            this.$store.commit(
                "userLogin/setUserId",
                ufhy.user.profile.userId ? ufhy.user.profile.userId : ""
            );
        },
        methods: {
            hasInPermission(roles = [], module) {
                if (Array.isArray(roles)) {
                    let hasRole = false;
                    const that = this;
                    roles.forEach(role => {
                        if (that.$can(role, module)) {
                            hasRole = true;
                        }
                    });

                    return hasRole;
                }
                return false;
            }
        }
    });
});
