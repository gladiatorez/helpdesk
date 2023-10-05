<template>
    <app-form-dialog
        v-model="showModal"
        :title="title"
        :url-close="{ name: 'staff.staff.index' }"
        :btn-save="mode === 'add'"
        :loading="loading"
        @save-close="addCloseAction"
        @save="addAction">
        <v-row>
            <v-col cols="12" md="6">
                <v-text-field
                    v-model="item.fullName"
                    :disabled="loading"
                    :error-messages="errorMsg.fullName"
                    :hide-details="!errorMsg.fullName"
                    :label="$t('staff::staff:full_name')"
                    :placeholder="$t('staff::staff:full_name')"
                ></v-text-field>
            </v-col>

            <v-col cols="12" md="3">
                <v-text-field
                    v-model="item.nik"
                    :disabled="loading"
                    :error-messages="errorMsg.nik"
                    :hide-details="!errorMsg.nik"
                    :label="$t('staff::staff:nik')"
                    :placeholder="$t('staff::staff:nik')"
                ></v-text-field>
            </v-col>

            <v-col cols="12" md="3">
                <v-autocomplete
                    v-model="item.picLevel"
                    :disabled="loading"
                    :items="levelOptions"
                    :error-messages="errorMsg.picLevel"
                    :hide-details="!errorMsg.picLevel"
                    :label="$t('staff::staff:pic_level')"
                    :placeholder="$t('staff::staff:pic_level')"
                ></v-autocomplete>
            </v-col>

            <template v-if="mode !== 'add'">
                <v-col cols="12">
                    <v-autocomplete
                        v-model="item.parentId"
                        :disabled="loading"
                        :items="headOptions"
                        :error-messages="errorMsg.parentId"
                        :hide-details="!errorMsg.parentId"
                        :label="$t('staff::staff:parent')"
                        :placeholder="$t('staff::staff:parent')"
                    ></v-autocomplete>
                </v-col>
            </template>

            <v-col cols="12" md="6">
                <v-autocomplete
                    v-model="item.companyId"
                    :disabled="loading"
                    :items="companyOptions"
                    :error-messages="errorMsg.companyId"
                    :hide-details="!errorMsg.companyId"
                    :label="$t('staff::staff:company')"
                    :placeholder="$t('staff::staff:company')"
                ></v-autocomplete>
            </v-col>

            <v-col cols="12" md="6">
                <v-text-field
                    v-model="item.position"
                    :disabled="loading"
                    :error-messages="errorMsg.position"
                    :hide-details="!errorMsg.position"
                    :label="$t('staff::staff:position')"
                    :placeholder="$t('staff::staff:position')"
                ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
                <v-text-field
                    v-model="item.phone"
                    :disabled="loading"
                    :error-messages="errorMsg.phone"
                    :hide-details="!errorMsg.phone"
                    :label="$t('staff::staff:phone')"
                    :placeholder="$t('staff::staff:phone')"
                ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
                <v-text-field
                    v-model="item.email"
                    :disabled="loading"
                    :error-messages="errorMsg.email"
                    :hide-details="!errorMsg.email"
                    :label="$t('staff::staff:email')"
                    :placeholder="$t('staff::staff:email')"
                    autocomplete="off"
                ></v-text-field>
            </v-col>

            <template v-if="mode === 'add'">
                <v-col cols="12" md="6">
                    <v-text-field
                        type="password"
                        v-model="item.password"
                        :disabled="loading"
                        :error-messages="errorMsg.password"
                        :hide-details="!errorMsg.password"
                        :label="$t('staff::staff:password')"
                        :placeholder="$t('staff::staff:password')"
                    ></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                    <v-text-field
                        type="password"
                        v-model="item.rePassword"
                        :disabled="loading"
                        :error-messages="errorMsg.rePassword"
                        :hide-details="!errorMsg.rePassword"
                        :label="$t('staff::staff:password_confirm_placeholder')"
                        :placeholder="$t('staff::staff:password_confirm_placeholder')"
                    ></v-text-field>
                </v-col>
            </template>

            <v-col cols="12" md="6">
                <v-autocomplete
                    v-model="item.groupId"
                    :disabled="loading"
                    :items="userGroupOptions"
                    :error-messages="errorMsg.groupId"
                    :hide-details="!errorMsg.groupId"
                    label="User group"
                    placeholder="User group"
                ></v-autocomplete>
            </v-col>

            <v-col cols="12" md="6">
                <v-checkbox
                    hide-details
                    color="primary"
                    :label="$t('lb::active')"
                    v-model="item.active"
                    :true-value="true"
                    :false-value="false"
                />
            </v-col>
        </v-row>
    </app-form-dialog>
</template>

<script>
export default {
    name: "staff-staff-form",
    components: {
        AppFormDialog: () => import('../../../components/AppFormDialog.vue'),
    },
    data() {
        return {
            showModal: false,
            title: this.$t("staff::staff:heading_new"),
            loading: false,
            mode: "add",
            item: {
                id: "",
                userId: "",
                fullName: "",
                companyId: null,
                parentId: null,
                phone: "",
                nik: "",
                position: "",
                email: "",
                password: "",
                rePassword: "",
                active: true,
                picLevel: null,
                groupId: "19"
            },
            errorMsg: {
                id: "",
                userId: "",
                fullName: "",
                companyId: "",
                parentId: "",
                phone: "",
                nik: "",
                position: "",
                email: "",
                password: "",
                rePassword: "",
                active: "",
                picLevel: "",
                groupId: ""
            },
            companyOptions: [],
            levelOptions: [],
            headOptions: [],
            userGroupOptions: []
        };
    },
    created() {
        this.title = this.$t("staff::staff:heading_new");
        if (this.$route.params.id) {
            this.mode = "edit";
            this.title = this.$t("staff::staff:heading_edit");
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
        clearErrorMsg() {
            this.errorMsg = {
                id: "",
                userId: "",
                fullName: "",
                companyId: "",
                parentId: "",
                phone: "",
                nik: "",
                position: "",
                email: "",
                password: "",
                rePassword: "",
                active: "",
                picLevel: ""
            };
        },
        setEmptyItem() {
            this.item = {
                id: "",
                userId: "",
                fullName: "",
                companyId: null,
                parentId: null,
                phone: "",
                nik: "",
                position: "",
                email: "",
                password: "",
                rePassword: "",
                active: true,
                picLevel: null,
                groupId: "19"
            };
        },
        fetchItem() {
            if (this.loading) {
                return false;
            }

            this.loading = true;
            this.$axios
                .get("staff/staff/item", { params: { id: this.item.id } })
                .then(response => {
                    const { data } = response;
                    const row = data.data;

                    this.item = {
                        id: row.id,
                        userId: row.user.userId,
                        fullName: row.fullName,
                        companyId: row.user.companyId,
                        parentId: row.parentId,
                        phone: row.phone,
                        nik: row.nik,
                        position: row.position,
                        email: row.user.email,
                        active: row.user.active,
                        picLevel: row.level.id,
                        groupId: row.user.groupId
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
        fetchOptions() {
            const that = this;
            that.companyOptions = [];
            that.levelOptions = [];
            that.headOptions = [];
            that.userGroupOptions = [];

            let params = {};
            if (this.mode === "edit") {
                params = {
                    params: {
                        id: this.item.id
                    }
                };
            }
            that.$axios
                .get("staff/staff/form-options", params)
                .then(response => {
                    const { data } = response;
                    that.companyOptions = [];
                    data.companyOptions.forEach(element => {
                        that.companyOptions.push({
                            value: element.id,
                            text: element.name
                        });
                    });

                    that.levelOptions = [];
                    data.levelOptions.forEach(element => {
                        that.levelOptions.push({
                            value: element.id,
                            text: element.name
                        });
                    });

                    that.headOptions = [
                        {
                            value: null,
                            text: "None"
                        }
                    ];
                    data.headOptions.forEach(element => {
                        that.headOptions.push({
                            value: element.id,
                            text: element.fullName
                        });
                    });

                    that.userGroupOptions = [];
                    data.userGroupOptions.forEach(element => {
                        that.userGroupOptions.push({
                            value: element.id,
                            text: element.name
                        });
                    });
                })
                .catch(error => {
                    console.log(error);
                    that.companyOptions = [{ value: null, text: "Error" }];
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
            item.set("fullName", this.item.fullName);
            item.set(
                "companyId",
                this.item.companyId ? this.item.companyId : ""
            );
            item.set("phone", this.item.phone);
            item.set("nik", this.item.nik);
            item.set("position", this.item.position);
            item.set("email", this.item.email);
            item.set("active", this.item.active ? "1" : "0");
            item.set("picLevel", this.item.picLevel ? this.item.picLevel : "");
            item.set("parentId", this.item.parentId ? this.item.parentId : "");
            item.set("groupId", this.item.groupId ? this.item.groupId : "");

            let url = "staff/staff/create";
            if (this.mode === "edit") {
                item.set("id", this.item.id);
                item.set("userId", this.item.userId);
                url = "staff/staff/edit";
            } else {
                item.set("password", this.item.password);
                item.set("rePassword", this.item.rePassword);
            }

            this.$axios
                .post(url, item)
                .then(response => {
                    const { data } = response;
                    this.$coresnackbars.success(data.message);

                    if (data.success) {
                        if (mode === "saveClose") {
                            this.$router.push({
                                name: "staff.staff.index",
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
