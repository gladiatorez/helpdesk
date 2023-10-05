<template>
    <app-form-dialog
        v-model="showModal"
        :title="title"
        :url-close="{ name: 'references.email_list.index' }"
        :btn-save="mode === 'add'"
        :loading="loading"
        @save-close="addCloseAction"
        @save="addAction">
        <v-text-field
            background-color="white"
            v-model="item.email"
            :disabled="loading"
            :error-messages="errorMsg.email"
            :label="$t('references::email_list:email')"
            :placeholder="$t('references::email_list:email_placeholder')"
        />
        <v-checkbox
            color="primary"
            class="mt-0"
            :label="$t('lb::active')"
            v-model="item.active"
            true-value="A"
            false-value="D"
        />
    </app-form-dialog>
</template>

<script>
export default {
    name: "references-email-list-form",
    components: {
        AppFormDialog: () => import('../../../components/AppFormDialog.vue'),
    },
    data() {
        return {
            showModal: false,
            title: this.$t("references::email_list:heading_new"),
            loading: false,
            mode: "add",
            item: {
                id: "",
                email: "",
                active: "A"
            },
            errorMsg: {
                id: "",
                email: ""
            }
        };
    },
    created() {
        this.title = this.$t("references::email_list:heading_new");
        if (this.$route.params.id) {
            this.mode = "edit";
            this.title = this.$t("references::email_list:heading_edit");
            this.item.id = this.$route.params.id;
            this.fetchItem();
        }
    },
    mounted() {
        this.showModal = true;
    },
    methods: {
        closeAction() {
            this.showModal = false;
            return this.$router.push({
                name: "references.email_list.index",
                params: {
                    refresh: true
                }
            });
        },
        clearErrorMsg() {
            this.errorMsg = {
                id: "",
                email: ""
            };
        },
        setEmptyItem() {
            this.item = {
                id: "",
                email: "",
                active: "A"
            };
        },
        fetchItem() {
            if (this.loading) {
                return false;
            }

            this.loading = true;
            this.$axios
                .get("references/email_list/item", {
                    params: { id: this.item.id }
                })
                .then(response => {
                    const { data } = response;
                    const row = data.data;

                    this.item = {
                        id: row.id,
                        email: row.email,
                        active: row.active
                    };

                    this.loading = false;
                })
                .catch(error => {
                    const { statusText, data } = error;
                    if (typeof data.message !== "undefined") {
                        this.$coresnackbars.error(data.message);
                    } else {
                        this.$coresnackbars.error(statusText);
                    }
                    this.loading = false;
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

            const item = new FormData();
            item.set("email", this.item.email);
            item.set("active", this.item.active);

            let url = "references/email_list/create";
            if (this.mode === "edit") {
                item.set("id", this.item.id);
                url = "references/email_list/edit";
            }

            this.$axios
                .post(url, item)
                .then(response => {
                    const { data } = response;
                    this.$coresnackbars.success(data.message);

                    if (data.success) {
                        if (mode === "saveClose") {
                            this.$router.push({
                                name: "references.email_list.index",
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
