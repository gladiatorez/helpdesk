<template>
    <v-container fluid class="pa-5">
        <v-card flat>
            <v-data-table
                sort-by="name"
                :sort-desc="false"
                :headers="headers"
                :items="items"
                :server-items-length="totalItems"
                class="elevation-0"
                :options.sync="tableOptions"
                :loading="loading">
                <template v-slot:item.updatedAt="{ item }">
                    <span title="DD/MM/YYYY HH:mm">
                        {{
                            $moment(item.updaatedAt).format("DD/MM/YYYY HH:mm")
                        }}
                    </span>
                </template>
            </v-data-table>
        </v-card>

        <router-view></router-view>
    </v-container>
</template>

<script>
import { fetchDtRows } from "../../../utils/helpers";

export default {
    name: "references-keyword-page",
    data() {
        return {
            headers: [
                {
                    text: this.$t("references::keyword:name"),
                    value: "name"
                },
                {
                    text: this.$t("lb::updated_at"),
                    value: "updatedAt",
                    width: "160px"
                }
            ],
            tableOptions: {},
            items: [],
            totalItems: 0,
            loading: false,
            searchText: ""
        };
    },
    watch: {
        $route: function(route) {
            if (route.params.refresh) {
                this.refreshAction();
            }
        },
        tableOptions() {
            this.refreshAction();
        }
    },
    created() {
        this.$root.$on("page-header:refresh-action", this.refreshAction);
        this.$root.$on("page-header:search-action", this.searchAction);
        this.$root.$on(
            "page-header:search-cancel-action",
            this.searchClearAction
        );
    },
    destroyed() {
        this.$root.$off("page-header:refresh-action", this.refreshAction);
        this.$root.$off("page-header:search-action", this.searchAction);
        this.$root.$off(
            "page-header:search-cancel-action",
            this.searchClearAction
        );
    },
    methods: {
        searchAction(payload) {
            this.searchText = payload;
            this.refreshAction();
        },
        searchClearAction() {
            this.searchText = "";
            this.refreshAction();
        },
        refreshAction() {
            this.loading = true;
            fetchDtRows(
                "references/keyword",
                this.tableOptions,
                this.searchText
            )
                .then(data => {
                    this.items = data.items;
                    this.totalItems = data.total;
                })
                .catch(error => {
                    this.loading = false;
                })
                .then(() => {
                    this.loading = false;
                });
        },
        removeAction(item) {
            if (this.loading) {
                return false;
            }

            const confirmText = sprintf(
                this.$t("confirm:remove_text"),
                item.descr
            );
            if (confirm(confirmText)) {
                this.loading = true;
                const that = this;
                this.$axios
                    .get("references/keyword/remove/", {
                        params: { id: item.id }
                    })
                    .then(response => {
                        const { data } = response;

                        that.$snackbars.success(data.message);

                        if (data.success) {
                            that.refreshAction();
                        }
                    })
                    .catch(function(error) {
                        const { statusText, status } = error;
                        that.$snackbars.error(
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
