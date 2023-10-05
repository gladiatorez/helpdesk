<template>
    <v-container fluid class="pa-5">
        <div class="d-md-flex">
            <v-select
                dense
                solo
                flat
                hide-details
                background-color="grey lighten-4"
                v-model="filterUserGroup"
                :class="$vuetify.breakpoint.smAndUp ? 'mr-4' : ''"
                :prefix="'Filter ' + this.$t('users::user:group') + ': '"
                :style="{maxWidth: $vuetify.breakpoint.smAndUp ? '300px' : '100%'}"
                :items="userGroupOptions"
            />
            <v-select
                dense
                solo
                flat
                background-color="grey lighten-4"
                v-model="filterCompany"
                :prefix="'Filter ' + this.$t('users::user:company') + ': '"
                :style="{maxWidth: $vuetify.breakpoint.smAndUp ? '200px' : '100%'}"
                :items="companyOptions"
            />
        </div>
        <v-card flat>
            <v-data-table
                sort-by="username"
                :sort-desc="false"
                :headers="headers"
                :items="items"
                :server-items-length="totalItems"
                :options.sync="tableOptions"
                :loading="loading"
            >
                <template v-slot:item.id="{ item }">
                    <core-more-menu
                        :remove-btn="false"
                        :edit-btn="$can('edit', $route.meta.module)"
                        :edit-url="{
                            name: 'users.user.edit',
                            params: { id: item.id }
                        }"
                    />
                </template>
                <template v-slot:item.active="{ item }">
                    <v-checkbox
                        :ripple="false"
                        v-model="item.active"
                        color="primary"
                        class="mt-0"
                        readonly
                        hide-details
                    ></v-checkbox>
                </template>

                <template v-slot:item.lastLogin="{ item }">
                    <template v-if="item.lastLogin">
                        <span
                            title="DD/MM/YYYY HH:mm"
                        >{{ $moment(item.lastLogin).format('DD/MM/YYYY HH:mm') }}</span>
                    </template>
                    <template v-else>--</template>
                </template>

                <template v-slot:item.updatedAt="{ item }">
                    <span
                        title="DD/MM/YYYY HH:mm"
                    >{{ $moment(item.updaatedAt).format('DD/MM/YYYY HH:mm') }}</span>
                </template>
            </v-data-table>
        </v-card>

        <router-view></router-view>
    </v-container>
</template>

<script>
import { fetchDtRows } from "../../../utils/helpers";

export default {
    name: "users-user-page",
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
                    text: this.$t("users::user:username"),
                    value: "username"
                },
                {
                    text: this.$t("users::user:email"),
                    value: "email"
                },
                {
                    text: this.$t("users::user:group"),
                    value: "groupName"
                },
                {
                    text: this.$t("users::user:company"),
                    value: "companyAbbr"
                },
                {
                    text: this.$t("lb::active"),
                    value: "active"
                },
                {
                    text: this.$t("users::user:last_login"),
                    value: "lastLogin",
                    width: "160px"
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
            searchText: "",
            filterUserGroup: "ALL",
            filterCompany: "ALL",
            userGroupOptions: [{ value: "ALL", text: "All" }],
            companyOptions: [{ value: "ALL", text: "All" }]
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
        },
        filterUserGroup() {
            this.refreshAction();
        },
        filterCompany() {
            this.refreshAction();
        }
    },
    async created() {
        this.$root.$on("page-header:refresh-action", this.refreshAction);
        this.$root.$on("page-header:search-action", this.searchAction);
        this.$root.$on(
            "page-header:search-cancel-action",
            this.searchClearAction
        );

        await this.fetchOptions();
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
        fetchOptions() {
            const that = this;
            return that.$axios.get("users/user/form_options").then(response => {
                const { data } = response;
                that.userGroupOptions = [{ value: "ALL", text: "All" }];
                data.groups.forEach(group => {
                    that.userGroupOptions.push({
                        value: group.id,
                        text: group.name
                    });
                });

                that.companyOptions = [{ value: "ALL", text: "All" }];
                data.companies.forEach(company => {
                    that.companyOptions.push({
                        value: company.id,
                        text: company.abbr
                    });
                });
            });
        },
        refreshAction() {
            this.loading = true;

            let filters = [];
            if (this.filterUserGroup !== 'ALL') {
                filters.push({
                    field: "groupId",
                    operator: "in",
                    value: this.filterUserGroup
                });
            }
            if (this.filterCompany !== 'ALL') {
                filters.push({
                    field: "companyId",
                    operator: "in",
                    value: this.filterCompany
                });
            }
            
            if (filters.length > 0) {
                this.tableOptions.filters = filters;
            } else {
                this.tableOptions.filters = [];
            }

            fetchDtRows("users/user", this.tableOptions, this.searchText)
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
                item.name
            );
            if (confirm(confirmText)) {
                this.loading = true;
                const that = this;
                this.$axios
                    .get("users/user/remove/", {
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
