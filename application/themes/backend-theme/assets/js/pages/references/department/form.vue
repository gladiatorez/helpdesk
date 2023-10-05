<template>
    <app-form-dialog
        v-model="showModal"
        :url-close="{ name: 'references.department.index' }"
        :title="title"
        :loading="loading"
        :btn-save="mode === 'add'"
        @save-close="addCloseAction"
        @save="addAction">
        <v-text-field
            v-model="item.name"
            :disabled="loading"
            :error-messages="errorMsg.name"
            :label="$t('references::department:name')"
            :placeholder="$t('references::department:name_placeholder')"
        />

        <v-checkbox
            class="mt-0"
            color="primary"
            :label="$t('lb::active')"
            v-model="item.active"
            true-value="A"
            false-value="D"
        />
    </app-form-dialog>
</template>

<script>
export default {
    name: "references-department-form",
    components: {
        AppFormDialog: () => import('../../../components/AppFormDialog.vue'),
    },
    data() {
        return {
            showModal: false,
            title: this.$t("references::department:heading_new"),
            loading: false,
            mode: "add",
            item: {
                id: "",
                name: "",
                active: "A"
            },
            errorMsg: {
                id: "",
                name: ""
            }
        };
    },
    created() {
        this.title = this.$t("references::department:heading_new");
        if (this.$route.params.id) {
            this.mode = "edit";
            this.title = this.$t("references::department:heading_edit");
            this.item.id = this.$route.params.id;
            this.fetchItem();
        }
    },
    mounted() {
        this.showModal = true;
    },
    methods: {
        clearErrorMsg() {
            this.errorMsg = {
                id: "",
                name: ""
            };
        },
        setEmptyItem() {
            this.item = {
                id: "",
                name: "",
                active: "A"
            };
        },
        fetchItem() {
            if (this.loading) {
                return false;
            }

            this.loading = true;
            this.$axios
                .get("references/department/item", {
                    params: { id: this.item.id }
                })
                .then(response => {
                    const { data } = response;
                    const row = data.data;

                    this.item = {
                        id: row.id,
                        name: row.name,
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
            item.set("name", this.item.name);
            item.set("active", this.item.active);

            let url = "references/department/create";
            if (this.mode === "edit") {
                item.set("id", this.item.id);
                url = "references/department/edit";
            }

            this.$axios
                .post(url, item)
                .then(response => {
                    const { data } = response;
                    this.$coresnackbars.success(data.message);

                    if (data.success) {
                        if (mode === "saveClose") {
                            this.$router.push({
                                name: "references.department.index",
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
