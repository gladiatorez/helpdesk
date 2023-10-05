<template>
    <app-form-dialog
        v-model="showModal"
        :title="title"
        :url-close="{ name: 'references.category.index', }"
        :loading="loading"
        :btn-save="mode === 'add'"
        @save-close="addCloseAction"
        @save="addAction">
        <v-text-field
            v-model="item.name"
            :disabled="loading"
            :error-messages="errorMsg.name"
            :label="$t('references::category:name')"
            :placeholder="$t('references::category:name_placeholder')"
        />
        <v-autocomplete
            v-model="item.parent_parentId"
            :disabled="loading"
            :items="parent_parentOptions"
            :error-messages="errorMsg.parentId"
            :label="'Parent'"
            :placeholder="'Choose parent of category'"
            @change="fetchCategorySub"
        />

        <v-autocomplete v-if="item.parent_parentId"
            v-model="item.parentId"
            :disabled="loading"
            :items="parentOptions"
            :error-messages="errorMsg.parentId"
            :label="'Sub Parent'"
            :placeholder="'Choose sub parent of category'"
        />

        <template v-if="item.parentId">
            <v-text-field
                v-model.number="item.estimate"
                :disabled="loading"
                :error-messages="errorMsg.estimate"
                :label="$t('references::category:estimate')"
                :placeholder="$t('references::category:estimate_placeholder')"
                
            />

            <v-autocomplete
                v-model="item.priorityId"
                :disabled="loading"
                :items="priorityOptions"
                :error-messages="errorMsg.priorityId"
                :label="$t('references::category:priority')"
            />

            <v-autocomplete
                small-chips multiple deletable-chips hide-selected
                v-model="item.staff"
                :items="staffOptions"
                :disabled="loading"
                :error-messages="errorMsg['staff[]']"
                :label="$t('references::category:staff')"
                :placeholder="$t('references::category:staff_choose')">
                <template slot="item" slot-scope="props" avatar>
                    <v-list-item-avatar
                        color="primary"
                        class="white--text">
                        <core-avatar-initial
                            :title="props.item.text"
                        ></core-avatar-initial>
                    </v-list-item-avatar>
                    <v-list-item-content>
                        <v-list-item-title class="body-2">{{ props.item.text }}</v-list-item-title>
                        <v-list-item-subtitle
                            class="caption"
                            style="text-transform: lowercase">
                            {{ props.item.position }}
                        </v-list-item-subtitle>
                    </v-list-item-content>
                </template>
            </v-autocomplete>

            <v-checkbox
                v-if="item.staff.length > 0"
                class="pt-0 mt-0"
                color="primary"
                v-model="item.autoSendStaff"
                :label="$t('references::category:staff_auto_send')"
            />
        </template>

        <v-textarea
            v-model="item.descr"
            :disabled="loading"
            :error-messages="errorMsg.descr"
            :label="$t('references::category:descr')"
            :placeholder="$t('references::category:descr_placeholder')"
        />

        <v-checkbox
            class="mt-0"
            color="primary"
            :label="$t('lb::active')"
            v-model="item.active"
            true-value="A"
            false-value="D"
        />

        <v-checkbox
            class="mt-0"
            color="primary"
            :label="'Task'"
            v-model="item.task"
            true-value="1"
            false-value="0"
        />
    </app-form-dialog>
</template>

<script>
export default {
    name: "category-category-form",
    components: {
        AppFormDialog: () => import('../../../components/AppFormDialog.vue'),
    },
    data() {
        return {
            showModal: false,
            title: this.$t("references::category:heading_new"),
            loading: false,
            mode: "add",
            item: {
                id: "",
                name: "",
                parentId: null,
                descr: "",
                icon: "",
                estimate: 86400,
                active: "A",
                staff: [],
                autoSendStaff: false,
                priorityId: null,
                task: 0,
            },
            errorMsg: {
                id: "",
                name: "",
                parentId: "",
                descr: "",
                icon: "",
                estimate: "",
                active: "A",
                "staff[]": "",
                priorityId: "",
                task:0
            },
            parentOptions: [{ value: null, text: "None" }],
            staffOptions: [],
            priorityOptions: [{ value: null, text: "None" }]
        };
    },
    created() {
        this.title = this.$t("references::category:heading_new");
        if (this.$route.params.id) {
            this.mode = "edit";
            this.title = this.$t("references::category:heading_edit");
            this.item.id = this.$route.params.id;
        }
        this.initForm();
    },
    mounted() {
        this.showModal = true;
    },
    methods: {
        async initForm() {
            await this.fetchOptions();
            if (this.mode === "edit") {
                this.fetchItem();
            }
        },
        closeAction() {
            this.showModal = false;
            return this.$router.push({
                name: "references.category.index",
                params: {
                    // refresh: true
                }
            });
        },
        clearErrorMsg() {
            this.errorMsg = {
                id: "",
                name: "",
                parentId: "",
                descr: "",
                icon: "",
                estimate: "",
                active: "A",
                "staff[]": "",
                task:0
            };
        },
        setEmptyItem() {
            this.item = {
                id: "",
                name: "",
                parentId: null,
                descr: "",
                icon: "",
                estimate: 86400,
                active: "A",
                staff: [],
                autoSendStaff: false,
                priorityId: null,
                task: 0
            };
        },
        fetchOptions() {
            const that = this;
            that.parent_parentOptions = [];
            that.parentOptions = [];
            that.staffOptions = [];
            that.$axios
                .get("references/category/form-options")
                .then(response => {
                    const { data } = response;
                    that.parent_parentOptions = [{ value: null, text: "None" }];
                    data.parent_parentOptions.forEach(element => {
                        that.parent_parentOptions.push({
                            value: element.id,
                            text: element.name
                        });
                    });

                    that.parentOptions = [{ value: null, text: "None" }];
                    data.parentOptions.forEach(element => {
                        that.parentOptions.push({
                            value: element.id,
                            text: element.name
                        });
                    });

                    that.staffOptions = [];
                    data.staffOptions.forEach(element => {
                        that.staffOptions.push({
                            value: element.id,
                            text: element.fullName,
                            position: element.position
                        });
                    });

                    that.priorityOptions = [{ value: null, text: "None" }];
                    data.priorityOptions.forEach(element => {
                        that.priorityOptions.push({
                            value: element.id,
                            text: element.name
                        });
                    });
                })
                .catch(error => {
                    console.log(error);
                });
        },
        fetchCategorySub() {
			const that = this;
            this.item.parentId = '';
            that.parentOptions = [];
			that.$axios.get('references/category/sub_category', {
				params: {parent_id: this.item.parent_parentId}
			})
			.then(response => {
				const {data} = response;
				that.parentOptions = [{ value: null, text: "None" }];
                    data.parentOptions.forEach(element => {
                        that.parentOptions.push({
                            value: element.id,
                            text: element.name
                        });
                    });
			}).catch((error) => {
                console.log(error);
			});
		},
        fetchItem() {
            if (this.loading) {
                return false;
            }

            this.loading = true;
            this.$axios
                .get("references/category/item", {
                    params: { id: this.item.id }
                })
                .then(response => {
                    const { data } = response;
                    const row = data.data;

                    this.item = {
                        id: row.id,
                        name: row.name,
                        parentId: row.parentId,
                        descr: row.descr,
                        icon: row.icon,
                        estimate: row.estimate,
                        active: row.active,
                        task: row.task,
                        staff: row.staff ? row.staff : [],
                        priorityId: row.priorityId ? row.priorityId : null,
                        autoSendStaff: row.autoSendStaff
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
            item.set("parent_parentId", this.item.parent_parentId ? this.item.parent_parentId : "0");
            item.set("parentId", this.item.parentId ? this.item.parentId : "0");
            item.set("descr", this.item.descr);
            item.set("icon", this.item.icon ? this.item.icon : "");
            item.set("active", this.item.active);
            item.set("task", this.item.task);

            if (this.item.parentId > 0) {
                item.set(
                    "priorityId",
                    this.item.priorityId ? this.item.priorityId : ""
                );
                item.set("autoSendStaff", this.item.autoSendStaff ? "1" : "0");
                item.set("estimate", this.item.estimate);
                if (this.item.staff.length > 0) {
                    this.item.staff.forEach(element => {
                        item.append("staff[]", element);
                    });
                }
            }

            let url = "references/category/create";
            if (this.mode === "edit") {
                item.set("id", this.item.id);
                url = "references/category/edit";
            }

            this.$axios
                .post(url, item)
                .then(response => {
                    const { data } = response;
                    this.$coresnackbars.success(data.message);

                    if (data.success) {
                        if (mode === "saveClose") {
                            this.$router.push({
                                name: "references.category.index",
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
