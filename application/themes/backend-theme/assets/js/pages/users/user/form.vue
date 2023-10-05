<template>
    <app-form-dialog
        v-model="showModal"
        :title="title"
        :url-close="{ name: 'users.user.index' }"
        :btn-save="mode === 'add'"
        :loading="loading"
        @save-close="addCloseAction"
        @save="addAction">
        <v-checkbox 
            label="Active"
            v-model="item.active"
        />
        <v-text-field
            label="Username"
            placeholder="Username"
            v-model="item.username"
            :disabled="loading"
            :error-messages="errorMsg.username"
        />
        <v-text-field
            label="Email"
            placeholder="Email"
            v-model="item.email"
            :disabled="loading"
            :error-messages="errorMsg.email"
        />
        <!-- <v-autocomplete
            v-model="item.group_id"
            :disabled="loading"
            :items="userGroupOptions"
            :error-messages="errorMsg.group_id"
            :hide-details="!errorMsg.group_id"
            label="User group"
            placeholder="User group"
        ></v-autocomplete> -->
    </app-form-dialog>
</template>

<script>
export default {
    name: "users-user-form",
    components: {
        AppFormDialog: () => import("../../../components/AppFormDialog.vue")
    },
    data() {
        return {
            showModal: false,
            title: this.$t("users::user:heading_new"),
            loading: false,
            mode: "add",
            item: {
                id: "",
                username: "",
                email: "",
                active: true,
                group_id: ""
            },
            errorMsg: {
                id: "",
                username: "",
                email: "",
                active: "",
                group_id: ""
            },
            userGroupOptions: []
        };
    },
    async created() {
        this.loading = true;
        this.title = this.$t("users::user:heading_new");
        if (this.$route.params.id) {
            this.mode = "edit";
            this.title = this.$t("users::user:heading_edit");
            this.item.id = this.$route.params.id;
            await this.fetchItem();
        }
        await this.fetchOptions();
        this.loading = false;
    },
    mounted() {
        this.showModal = true;
    },
    methods: {
        closeAction() {
            this.showModal = false;
            return this.$router.push({
                name: "users.user.index",
                params: {
                    refresh: true
                }
            });
        },
        clearErrorMsg() {
            this.errorMsg = {
                id: "",
                username: "",
                email: "",
                active: "",
                group_id: ""
            };
        },
        setEmptyItem() {
            this.item = {
                id: "",
                username: "",
                email: "",
                active: true,
                group_id: ""
            };
        },
        fetchItem() {
            return this.$axios
                .get("users/user/item", { params: { id: this.item.id } })
                .then(response => {
                    const { data } = response;
                    const row = data.data;

                    this.item = {
                        id: row.id,
                        username: row.username,
                        email: row.email,
                        password: row.password,
                        password_confirm: row.password_confirm,
                        active: row.active,
                        group_id: row.group_id
                    };

                })
                .catch(error => {
                    const { statusText, data } = error;
                    if (typeof data.message !== "undefined") {
                        this.$coresnackbars.error(data.message);
                    } else {
                        this.$coresnackbars.error(statusText);
                    }
                });
        },
        fetchOptions() {
            const that = this;
            that.userGroupOptions = [];

            return that.$axios
                .get("users/user/form_options")
                .then(response => {
                    const { data } = response;
                    data.groups.forEach(group => {
                        that.userGroupOptions.push({
                            value: group.id,
                            text: group.name
                        });
                    });
                })
                .catch(error => {
                    console.log(error);
                });
        },
        addAction() {
            this.saveChanges("save");
        },
        addCloseAction() {
            this.saveChanges("saveClose");
        },
        saveChanges(mode) {
            if (this.loading) {
                return false;
            }

            this.clearErrorMsg();
            this.loading = true;

            const formData = new FormData();
            formData.append('username', this.item.username);
            formData.append('email', this.item.email);
            formData.append('active', this.item.active ? 1 : 0);
            // formData.append('group_id', this.item.group_id);

            let url = "users/user/create";
            if (this.mode === "edit") {
                formData.append("id", this.item.id);
                url = "users/user/edit";
            }

            this.$axios
                .post(url, formData)
                .then(response => {
                    const { data } = response;
                    this.$coresnackbars.success(data.message);

                    if (data.success) {
                        if (mode === "saveClose") {
                            this.$router.push({
                                name: "users.user.index",
                                params: { refresh: true }
                            });
                        } else {
                            this.setEmptyItem();
                        }
                    }

                    this.loading = false;
                })
                .catch(error => {
                    const { statusText, data } = error;
                    this.$coresnackbars.error(statusText);

                    if (
                        typeof data !== "undefined" &&
                        typeof data.message !== "undefined"
                    ) {
                        if (typeof data.message === "object") {
                            this.errorMsg = Object.assign(
                                {},
                                this.errorMsg,
                                data.message
                            );
                        }
                    }

                    this.loading = false;
                });
        }
    }
};
</script>
