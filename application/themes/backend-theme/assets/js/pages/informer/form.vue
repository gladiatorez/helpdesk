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
                    :label="$t('informer::full_name')"
                    :placeholder="$t('informer::full_name')"
                ></v-text-field>
            </v-col>

            <v-col cols="12" md="3">
                <v-text-field
                    v-model="item.nik"
                    :disabled="loading"
                    :error-messages="errorMsg.nik"
                    :hide-details="!errorMsg.nik"
                    :label="$t('informer::nik')"
                    :placeholder="$t('informer::nik')"
                ></v-text-field>
            </v-col>

            <v-col cols="12" md="3">
                <v-text-field
                    v-model="item.phone"
                    :disabled="loading"
                    :error-messages="errorMsg.phone"
                    :hide-details="!errorMsg.phone"
                    :label="$t('informer::phone')"
                    :placeholder="$t('informer::phone')"
                ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
                <v-autocomplete
                    v-model="item.companyId"
                    :disabled="loading"
                    :items='companyOptions'
                    :error-messages="errorMsg.companyId"
                    :hide-details="!errorMsg.companyId"
                    :label="$t('informer::company')"
                    :placeholder="$t('informer::company')"
                ></v-autocomplete>
            </v-col>

            <v-col cols="12" md="6">
                <v-autocomplete
                    v-model="item.companyBranchId"
                    :disabled="loading"
                    :items='companyBranchOptions'
                    :error-messages="errorMsg.companyBranchId"
                    :hide-details="!errorMsg.companyBranchId"
                    :label="$t('informer::location')"
                    :placeholder="$t('informer::location')"
                ></v-autocomplete>
            </v-col>

            <v-col cols="12" md="6">
                <v-autocomplete
                    v-model="item.departmentId"
                    :disabled="loading"
                    :items='departmentOptions'
                    :error-messages="errorMsg.departmentId"
                    :hide-details="!errorMsg.departmentId"
                    :label="$t('informer::department')"
                    :placeholder="$t('informer::department')"
                ></v-autocomplete>
            </v-col>

            <v-col cols="12" md="6">
                <v-text-field
                    v-model="item.position"
                    :disabled="loading"
                    :error-messages="errorMsg.position"
                    :hide-details="!errorMsg.position"
                    :label="$t('informer::position')"
                    :placeholder="$t('informer::position')"
                ></v-text-field>
            </v-col>
        </v-row>
    </app-form-dialog>
</template>

<script>
export default {
    name: "informer-form",
    components: {
        AppFormDialog: () => import('../../components/AppFormDialog.vue'),
    },
    data() {
        return {
            showModal: false,
            title: this.$t("informer::heading_new"),
            loading: false,
            mode: "add",
            item: {
                id: "",
                userId: "",
                fullName: "",
                companyId: "",
                companyBranchId: "",
                departmentId: "",
                departmentOther: "",
                phone: "",
                nik: "",
                position: "",
            },
            errorMsg: {
                id: "",
                userId: "",
                fullName: "",
                companyId: "",
                companyBranchId: "",
                departmentId: "",
                departmentOther: "",
                phone: "",
                nik: "",
                position: "",
            },
            companyOptions: [],
            departmentOptions: []
        };
    },
    computed: {
        companyBranchOptions() {
			const that = this;
			const companies = that.companyOptions.filter(element => {
				return element.value === that.item.companyId;
			});
			
			if (companies.length <= 0) {
				return [];
			}
			
			let options = [];
			companies.forEach(company => {
				if (company.branch) {
					company.branch.forEach(branch => {
						options.push({
							text: branch.name,
							value: branch.id
						})
					});
				}
			});
			return options;
		}
    },
    created() {
        this.title = this.$t("informer::heading_new");
        if (this.$route.params.id) {
            this.mode = "edit";
            this.title = this.$t("informer::heading_edit");
            this.item.id = this.$route.params.id;
        }
        this.initForm();
    },
    mounted() {
        this.showModal = true;
    },
    methods: {
        async initForm() {
            this.loading = true;
            await this.fetchOptions();
            if (this.mode === "edit") {
                this.fetchItem();
            }
            this.loading = false;
        },
        clearErrorMsg() {
            this.errorMsg = {
                id: "",
                userId: "",
                fullName: "",
                companyId: "",
                companyBranchId: "",
                departmentId: "",
                departmentOther: "",
                phone: "",
                nik: "",
                position: "",
            };
        },
        setEmptyItem() {
            this.item = {
                id: "",
                userId: "",
                fullName: "",
                companyId: "",
                companyBranchId: "",
                departmentId: "",
                departmentOther: "",
                phone: "",
                nik: "",
                position: "",
            };
        },
        fetchItem() {
            return this.$axios
                .get("informer/item", { params: { id: this.item.id } })
                .then(response => {
                    const { data } = response;
                    const row = data.data;

                    this.item = {
                        id: row.id,
                        userId: row.userId,
                        fullName: row.fullName,
                        companyId: row.companyId,
                        companyBranchId: row.companyBranchId,
                        departmentId: row.departmentId,
                        departmentOther: row.departmentOther,
                        phone: row.phone,
                        nik: row.nik,
                        position: row.position,
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
			return that.$axios.get('addons/companyoptions2')
				.then(response => {
					const { data } = response;
					that.companyOptions = data.companyOptions;
					
					that.departmentOptions = data.departmentOptions;
				})
				.catch(error => {
					console.log(error);
				})
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
            formData.append('fullName', this.item.fullName);
            formData.append('companyId', this.item.companyId);
            formData.append('companyBranchId', this.item.companyBranchId);
            formData.append('departmentId', this.item.departmentId);
            formData.append('departmentOther', this.item.departmentOther);
            formData.append('phone', this.item.phone);
            formData.append('nik', this.item.nik);
            formData.append('position', this.item.position);

            let url = "informer/create";
            if (this.mode === "edit") {
                formData.set("id", this.item.id);
                url = "informer/edit";
            }

            this.$axios
                .post(url, formData)
                .then(response => {
                    const { data } = response;
                    this.$coresnackbars.success(data.message);

                    if (data.success) {
                        if (mode === "saveClose") {
                            this.$router.push({
                                name: "informer.index",
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
