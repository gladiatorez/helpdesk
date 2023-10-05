<template>
    <v-container fluid class="pa-5">
        <v-card flat>
            <!-- <v-card-title>
                <v-btn
                    rounded
                    small
                    v-if="$can('create', $route.meta.module)"
                    color="primary"
                    class="white--text"
                    :to="{ name: 'informer.create' }"
                >
                    <v-icon left>add</v-icon>
                    {{ $t("btn::create") }}
                </v-btn>
            </v-card-title> -->
            <v-data-table
                sort-by="fullName"
                :sort-desc="false"
                :headers="headers"
                :items="items"
                :server-items-length="totalItems"
                :options.sync="tableOptions"
                :loading="loading">
                <template v-slot:item.id="{ item }">
                    <core-more-menu
                        :edit-btn="$can('edit', $route.meta.module)"
                        :edit-url="{
                            name: 'informer.edit',
                            params: { id: item.id }
                        }"
                        :remove-btn="$can('remove', $route.meta.module)"
                        @remove-action="removeAction(item)"
                    />
                </template>
                <template v-slot:item.departmentName="{ item }">
                    <template v-if="item.departmentName">{{ item.departmentName }}</template>
                    <template v-else>{{ item.departmentOther }}</template>
                </template>

                <template v-slot:item.active="{ item }">
                    <v-checkbox
                        v-model="item.active"
                        color="primary"
                        class="mt-0"
                        readonly
                        hide-details
                    ></v-checkbox>
                </template>

                <template v-slot:item.updatedAt="{ item }">
                    <span
                        title="DD/MM/YYYY HH:mm"
                    >{{ $moment(item.updaatedAt).format('DD/MM/YYYY HH:mm') }}</span>
                </template>
            </v-data-table>
        </v-card>

        <router-view />
    </v-container>
</template>

<script>
import { fetchDtRows } from "../../utils/helpers";
export default {
    name: "informer-index-page",
    data() {
        return {
            headers: [
                {
                    text: "",
                    value: "id",
                    sortable: false,
                    width: "40px"
                },
                {
                    text: this.$t("informer::full_name"),
                    value: "fullName"
                },
                {
                    text: this.$t("informer::nik"),
                    value: "nik"
                },
                {
                    text: this.$t("informer::company"),
                    value: "companyAbbr"
                },
                {
                    text: this.$t("informer::department"),
                    value: "departmentName",
                    sortable: false
                },
                {
                    text: this.$t("lb::status"),
                    value: "active",
                    width: "120px"
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
            fetchDtRows("informer", this.tableOptions, this.searchText)
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

            const confirmText = this.$sprintf(
                this.$t("confirm:remove_text"),
                item.fullName
            );
            if (confirm(confirmText)) {
                this.loading = true;
                const that = this;
                this.$axios
                    .get("informer/remove/", {
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