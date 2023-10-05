<template>
    <app-form-dialog
        v-model="showModal"
        :title="title"
        :url-close="{ name: 'staff.pic_level.index' }"
        :btn-save="mode === 'add'"
        :loading="loading"
        @save-close="addCloseAction"
        @save="addAction">
        <v-text-field
            v-model="item.name"
            :disabled="loading"
            :error-messages="errorMsg.name"
            :label="$t('staff::pic_level:name')"
            :placeholder="$t('staff::pic_level:name_placeholder')"
        />
        <v-select
            v-model="item.level"
            :items="levelOptions"
            :disabled="loading"
            :error-messages="errorMsg.level"
            :label="$t('staff::pic_level:level')"
        />
    </app-form-dialog>
</template>

<script>
export default {
    name: "staff-pic-level-form",
    components: {
        AppFormDialog: () => import('../../../components/AppFormDialog.vue'),
    },
    data() {
        return {
            showModal: false,
            title: this.$t("staff::pic_level:heading_new"),
            loading: false,
            mode: "add",
            item: {
                id: "",
                name: "",
                level: "2"
            },
            errorMsg: {
                id: "",
                name: "",
                level: ""
            },
            levelOptions: ["1", "2", "3", "4"]
        };
    },
    created() {
        this.title = this.$t("staff::pic_level:heading_new");
        if (this.$route.params.id) {
            this.mode = "edit";
            this.title = this.$t("staff::pic_level:heading_edit");
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
                name: "",
                level: ""
            };
        },
        setEmptyItem() {
            this.item = {
                id: "",
                name: "",
                level: "2"
            };
        },
        fetchItem() {
            if (this.loading) {
                return false;
            }

            this.loading = true;
            this.$axios
                .get("staff/pic_level/item", { params: { id: this.item.id } })
                .then(response => {
                    const { data } = response;
                    const row = data.data;

                    this.item = {
                        id: row.id,
                        name: row.name,
                        level: row.level
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
            item.set("level", this.item.level);

            let url = "staff/pic_level/create";
            if (this.mode === "edit") {
                item.set("id", this.item.id);
                url = "staff/pic_level/edit";
            }

            this.$axios
                .post(url, item)
                .then(response => {
                    const { data } = response;
                    this.$coresnackbars.success(data.message);

                    if (data.success) {
                        if (mode === "saveClose") {
                            this.$router.push({
                                name: "staff.pic_level.index",
                                params: { refresh: true }
                            });
                        } else {
                            this.setEmptyItem();
                        }
                    }

                    this.loading = false;
                })
                .catch(error => {
                    console.log(error);
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
