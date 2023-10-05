<template>
    <v-container fluid class="pa-5">
        <v-card outlined :loading="loading">
            <v-card-title>
                <span>{{ $t("references::category:directory") }}</span>
                <v-spacer></v-spacer>
                <v-btn
                    rounded
                    small
                    v-if="$can('create', $route.meta.module)"
                    color="primary"
                    :to="{ name: 'references.category.create' }">
                    <v-icon left>add</v-icon>
                    {{ $t("btn::create") }}
                </v-btn>
            </v-card-title>
            <v-divider />
            <v-card-text>
                <v-row>
                    <v-col cols="12" lg="6">
                        <v-treeview
                            dense
                            transition
                            hoverable
                            item-text="title"
                            v-model="tree"
                            :active.sync="active"
                            :open="open"
                            :items="items">
                            <template v-slot:prepend="{ item, open }">
                                <v-icon v-if="item.parent <= 0">
                                    {{ open ? "folder_open" : "folder" }}
                                </v-icon>
                            </template>
                            <template v-slot:label="{ item }">
                                <core-more-menu
                                    :edit-btn="$can('edit', $route.meta.module)"
                                    :edit-url="{
                                        name: 'references.category.edit',
                                        params: { id: item.id }
                                    }"
                                    :remove-btn="
                                        $can('remove', $route.meta.module)
                                    "
                                    @remove-action="removeAction(item)"
                                />
                                {{ item.title }}
                            </template>
                        </v-treeview>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <router-view></router-view>
    </v-container>
</template>

<script>
export default {
    name: "references-category-page",
    data() {
        return {
            tree: [],
            active: [],
            open: [],
            items: [],
            loading: false
        };
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
            that.$axios.get("references/category").then(response => {
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
                item.name
            );
            if (confirm(confirmText)) {
                this.loading = true;
                const that = this;
                this.$axios
                    .get("references/category/remove/", {
                        params: { id: item.id }
                    })
                    .then(response => {
                        const { data } = response;
                        if (data.success) {
                            that.$coresnackbars.success(data.message);
                            that.refreshAction();
                        } else {
                            that.$coresnackbars.error(data.message);
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
