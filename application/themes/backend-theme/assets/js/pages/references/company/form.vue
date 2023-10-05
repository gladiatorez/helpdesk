<template>
    <app-form-dialog
        v-model="showModal"
        :title="title"
        :url-close="{
            name: 'references.company.index',
            params: {
                refresh: true
            }
        }"
        :btn-save="mode === 'add'"
        :loading="loading"
        @save-close="addCloseAction"
        @save="addAction">
        <v-row>
            <v-col cols="12" lg="8">
                <v-text-field 
                    v-model="item.name"
                    :disabled="loading"
                    :error-messages="errorMsg.name"
                    :hide-details="!errorMsg.name"
                    :label="$t('references::company:name')"
                    :placeholder="$t('references::company:name_placeholder')"
                />
            </v-col>

            <v-col cols="12" lg="4">
                <v-text-field 
                    v-model="item.abbr"
                    :disabled="loading"
                    :error-messages="errorMsg.abbr"
                    :hide-details="!errorMsg.abbr"
                    :label="$t('references::company:abbr')"
                    :placeholder="$t('references::company:abbr_placeholder')"
                />
            </v-col>

            <v-col cols="12">
                <v-text-field
                    v-model="item.address"
                    :disabled="loading"
                    :error-messages="errorMsg.address"
                    :hide-details="!errorMsg.address"
                    :label="$t('references::company:address')"
                    :placeholder="$t('references::company:address_placeholder')"
                />
            </v-col>

            <v-col cols="12" lg="6">
                <v-text-field
                    v-model="item.telephone"
                    :disabled="loading"
                    :error-messages="errorMsg.telephone"
                    :hide-details="!errorMsg.telephone"
                    :label="$t('references::company:telephone')"
                    :placeholder="$t('references::company:telephone_placeholder')"
                />
            </v-col>

            <v-col cols="12" lg="6">
                <v-text-field
                    v-model="item.faximile"
                    :disabled="loading"
                    :error-messages="errorMsg.faximile"
                    :hide-details="!errorMsg.faximile"
                    :label="$t('references::company:faximile')"
                    :placeholder="$t('references::company:faximile_placeholder')"
                />
            </v-col>

            <v-col cols="12" lg="6">
                <v-text-field
                    v-model="item.email"
                    :disabled="loading"
                    :error-messages="errorMsg.email"
                    :hide-details="!errorMsg.email"
                    :label="$t('references::company:email')"
                    :placeholder="$t('references::company:email_placeholder')"
                />
            </v-col>

            <v-col cols="12" lg="6">
                <v-text-field
                    v-model="item.website"
                    :disabled="loading"
                    :error-messages="errorMsg.website"
                    :hide-details="!errorMsg.website"
                    :label="$t('references::company:website')"
                    :placeholder="$t('references::company:website_placeholder')"
                ></v-text-field>
            </v-col>

            <v-col cols="12">
                <v-checkbox 
                    v-model="item.active"
                    class="mt-0"
                    true-value="A"
                    false-value="D"
                    :label="$t('lb::active')"
                />
            </v-col>
        </v-row>
    </app-form-dialog>
</template>

<script>
export default {
    name: 'references-company-form',
    components: {
        AppFormDialog: () => import('../../../components/AppFormDialog.vue'),
    },
    data() {
        return {
            showModal: false,
            loading: false,
            title: this.$t('references::company:heading_new'),
            loading: false,
            mode: 'add',
            item: {
                id: '',
                name: '',
                abbr: '',
                address: '',
                telephone: '',
                faximile: '',
                email: '',
                website: '',
                active: 'A',
            },
            errorMsg: {
                id: '',
                name: '',
                abbr: '',
                address: '',
                telephone: '',
                faximile: '',
                email: '',
                website: '',
            }
        }
    },
    async created() {
        this.title = this.$t('references::company:heading_new');
        if (this.$route.params.id) {
            this.mode = 'edit';
            this.title = this.$t('references::company:heading_edit');
            this.item.id = this.$route.params.id;

            await this.fetchItem();
        }
    },
    mounted() {
        this.showModal = true;
    },
    methods: {
        closeAction() {
            this.showModal = false;
            return this.$router.push({
                name: 'references.company.index',
                params: {
                    refresh: true
                }
            });
        },
        clearErrorMsg() {
            this.errorMsg = {
                id: '',
                name: '',
                abbr: '',
                address: '',
                telephone: '',
                faximile: '',
                email: '',
                website: '',
            };
        },
        setEmptyItem() {
            this.item = {
                id: '',
                name: '',
                abbr: '',
                address: '',
                telephone: '',
                faximile: '',
                email: '',
                website: '',
                active: 'A',
            };
        },
        fetchItem() {
            if (this.loading) {
                return false;
            }

            this.loading = true;
            this.$axios.get('references/company/item', { params: {id: this.item.id} }).then(response => {
                const { data } = response;
                const row = data.data;

                this.item = {
                id: row.id,
                name: row.name,
                abbr: row.abbr,
                address: row.address,
                telephone: row.telephone,
                faximile: row.faximile,
                email: row.email,
                website: row.website,
                active: row.active,
                };

                this.loading = false;
            }).catch(error => {
                const {statusText, data} = error;
                if (typeof data.message !== "undefined") {
                this.$coresnackbars.error(data.message);
                } else {
                this.$coresnackbars.error(statusText);
                }
                this.loading = false;
            });
        },
        addAction() {
            this.saveChanges('save')
        },
        addCloseAction() {
            this.saveChanges('saveClose')
        },
        saveChanges(mode) {
            if (this.loading) {
                return false;
            }

            this.clearErrorMsg();
            this.loading = true;

            const item = new FormData();
            item.set('name', this.item.name);
            item.set('abbr', this.item.abbr);
            item.set('address', this.item.address);
            item.set('telephone', this.item.telephone);
            item.set('faximile', this.item.faximile);
            item.set('email', this.item.email);
            item.set('website', this.item.website);
            item.set('active', this.item.active);

            let url = 'references/company/create';
            if (this.mode === 'edit') {
                item.set('id', this.item.id);
                url = 'references/company/edit';
            }

            this.$axios.post(url, item).then((response) => {
                const { data } = response;
                this.$coresnackbars.success(data.message);

                if (data.success) {
                    if (mode === 'saveClose') {
                        this.$router.push({
                            name: 'references.company.index',
                            params: {refresh: true}
                        });
                    } else {
                        this.setEmptyItem();
                        this.$el.querySelector("#company-name").focus();
                    }
                }

                this.loading = false;
            }).catch((error) => {
                const {statusText, data} = error;
                this.$coresnackbars.error(statusText);

                if (typeof data !== "undefined" && typeof data.message !== "undefined") {
                    if (typeof data.message === 'object') {
                    this.errorMsg = Object.assign({}, this.errorMsg, data.message);
                    }
                }

                this.loading = false;
            }).then(() => {
                this.loading = false;
            })
        }
    }
}
</script>
