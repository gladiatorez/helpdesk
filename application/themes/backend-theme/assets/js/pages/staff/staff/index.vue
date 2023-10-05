<template>
    <v-container fluid class="pa-5">
        <v-card outlined :loading="loading">
            <v-card-title>
                <span>{{ $t("staff::staff:directory") }}</span>
                <v-spacer></v-spacer>
                <v-btn
                    rounded
                    small
                    v-if="$can('create', $route.meta.module)"
                    color="primary"
                    :to="{ name: 'staff.staff.create' }">
                    <v-icon left>add</v-icon>
                    {{ $t("btn::create") }}
                </v-btn>
            </v-card-title>
            <v-divider />
            <v-card-text>
                <v-treeview
                    dense
                    hoverable
                    item-text="title"
                    :active.sync="active"
                    :open="open"
                    :items="items">
                    <template v-slot:label="{ item }">
                        <core-more-menu
                            :edit-btn="$can('edit', $route.meta.module)"
                            :edit-url="{
                                name: 'staff.staff.edit',
                                params: { id: item.id }
                            }"
                            :remove-btn="$can('remove', $route.meta.module)"
                            @remove-action="removeAction(item)"
                        />
                        {{ item.title }}
                    </template>
                </v-treeview>
            </v-card-text>
        </v-card>

        <router-view></router-view>
    </v-container>
</template>

<script>
export default {
    name: "staff-staff-page",
    data() {
        return {
            active: [],
            open: [],
            items: [],
            loading: false
        };
    },
    computed: {
        isSelected() {
            return this.active.length > 0;
        }
    },
    watch: {
        $route: function(route) {
            if (route.params.refresh) {
                this.refreshAction();
            }
        }
    },
    created() {
        this.$root.$on("page-header:refresh-action", this.refreshAction);
        this.getDataFromApi();
    },
    destroyed() {
        this.$root.$off("page-header:refresh-action", this.refreshAction);
    },
    methods: {
        getDataFromApi() {
            this.loading = true;
            const that = this;
            that.$axios.get("staff/staff").then(response => {
                that.loading = false;
                that.items = response.data;

                that.open = [];
                that.items.forEach(item => {
                    that.open.push(item.id);
                    if (item.children && item.children.length > 0) {
                        item.children.forEach(child => {
                            that.open.push(child.id);
                        });
                    }
                });
            });
        },
        refreshAction() {
            this.getDataFromApi();
        },
        removeAction(item) {
            if (this.loading) {
                return false;
            }

            const confirmText = this.$sprintf(
                this.$t("confirm:remove_text"),
                item.title
            );
            if (confirm(confirmText)) {
                this.loading = true;
                const that = this;
                this.$axios
                    .get("staff/staff/remove/", {
                        params: { id: item.id }
                    })
                    .then(response => {
                        const { data } = response;

                        that.$coresnackbars.success(data.message);

                        if (data.success) {
                            that.refreshAction();
                        }
                    })
                    .catch(function(error) {
                        const { statusText, status } = error;
                        that.$coresnackbars.error(
                            "Code: " + status + " " + statusText
                        );
                    })
                    .then(() => {
                        that.loading = false;
                    });
            }
        }
    }
};
</script>
