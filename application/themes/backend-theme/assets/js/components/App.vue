<template>
    <v-app>
        <core-drawer />
        <core-app-bar v-model="appbarShow" />

        <v-content>
            <!-- <v-breadcrumbs class="pb-0" :items="itemBreadcrumbs" /> -->
            <router-view />
        </v-content>

        <v-footer app inset absolute class="backend-footer">
            <small class="blue-grey--text text--lighten-1">
                {{ new Date().getFullYear() }} &copy; 
                <a href="http://it.kallagroup.co.id" class="font-weight-bold" style="text-decoration:none">CICT Kalla Group</a>
            </small>
        </v-footer>
    </v-app>
</template>

<script>
export default {
    name: 'App',
    data() {
        return {
            appbarShow: true
        }
    },
    computed: {
        itemBreadcrumbs() {
            const that = this;
            let breadcrumbs = [{
                text: 'MACCA',
                to: '/'
            }];

            this.$route.matched.forEach(element => {
                const title = element.meta
                    ? element.meta.title
                    ? that.$t(element.meta.title)
                    : element.name
                    : element.path;

                const findPath = _.findIndex(breadcrumbs, { to: element.path });
                const findTitle = _.findIndex(breadcrumbs, { text: title });
                if (find < 0 || findTitle < 0) {
                    breadcrumbs.push({
                        text: element.meta
                            ? element.meta.title
                            ? that.$t(element.meta.title)
                            : element.name
                            : element.path,
                        to: element.path
                    });
                }
            });
            return breadcrumbs;
        },
    },
    methods: {
        onAppInput(payload) {
            console.log(payload)
        }
    }
}
</script>