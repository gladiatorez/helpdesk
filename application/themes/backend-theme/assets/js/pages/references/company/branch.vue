<template>
    <app-form-dialog
        hide-form-action
        v-model="showModal"
        :title="title"
        :url-close="{
            name: 'references.company.index',
        }"
        :loading="loading">
        <v-expansion-panels class="mb-4">
            <v-expansion-panel>
                <v-expansion-panel-header>
                    <span class="font-weight-bold primary--text">New location</span>
                </v-expansion-panel-header>
                <v-expansion-panel-content>
                    <v-text-field
                        v-model="itemNew.name"
                        label="Branch name"
                        placeholder="Enter new branch"
                        :error-messages="itemError.name"
                    />

                    <v-checkbox 
                        class="mt-0"
                        color="primary"
                        label="Active"
                        v-model="itemNew.active"
                        true-value="A"
                        false-value="D"
                    />

                    <v-btn small color="primary" @click="addNewAction">Save</v-btn>
                </v-expansion-panel-content>
            </v-expansion-panel>
        </v-expansion-panels>

        <v-data-table
            :headers="headers"
            :items="items"
            :server-items-length="totalItems"
            class="elevation-0"
            :options.sync="tableOptions">
            <template v-slot:item.name="props">
                <v-edit-dialog large 
                    :return-value.sync="props.item.name"
                    @save="saveEditDialog(props.item)">
                    <template v-slot:default>
                        <div>{{ props.item.name }}</div>
                    </template>
                    <template v-slot:input>
                        <v-text-field dense single-line
                            v-model="props.item.name"
                            placeholder="Edit"
                        />
                    </template>
                </v-edit-dialog>
            </template>

            <template v-slot:item.active="props">
                <v-edit-dialog large>
                    <template v-slot:default>
                        <core-status-value v-model="props.item.active" />
                    </template>
                    <template v-slot:input>
                        <v-checkbox 
                            slot="input"
                            color="primary"
                            label="Active"
                            v-model="props.item.active"
                            true-value="A"
                            false-value="D"
                        />
                    </template>
                </v-edit-dialog>
            </template>

            <template v-slot:item.id="{ item }">
                <v-btn icon small color="error" @click="removeAction(item)">
                    <v-icon>delete</v-icon>
                </v-btn>
            </template>
        </v-data-table>
    </app-form-dialog>
</template>

<script>
import { fetchDtRows } from '../../../utils/helpers';
export default {
    name: 'references-company-branch',
    components: {
        AppFormDialog: () => import('../../../components/AppFormDialog.vue'),
    },
    data() {
        return {
            showModal: false,
            loading: false,
            headers: [
                { 
                    text: this.$t('references::company:name'), 
                    value: 'name', 
                },
                { 
                    text: this.$t('lb::status'), 
                    value: 'active', 
                    width: '90px'
                },
                { 
                    text: '', 
                    value: 'id', 
                    width: '40px'
                },
            ],
            tableOptions: {},
            items: [],
            totalItems: 0,
            searchText: null,
            companyId: '',
            itemNew: {
                name: '',
                active: 'A'
            },
            itemError: {
                name: '',
                active: ''
            }
        }
    },
    computed: {
        title() {
        return this.$t('references::company:branch');
        }
    },
    watch: {
        tableOptions() {
        this.refreshAction();
        }
    },
    created() {
        if (this.$route.params.id) {
        this.companyId = this.$route.params.id;
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
        refreshAction() {
            this.loading = true;
            fetchDtRows(`references/company/branchlist?companyId=${this.companyId}`, this.tableOptions, this.searchText).then(data => {
                this.items = data.items
                this.totalItems = data.total
            }).catch(error => {
                this.loading = false;
            }).then(() => {
                this.loading = false;
            })
        },
        addNewAction() {
            if (this.loading) {
                return false;
            }

            this.itemError.name = '';
            this.itemError.active = '';
            this.loading = true;

            const item = new FormData();
            item.set('name', this.itemNew.name);
            item.set('companyId', this.companyId);
            item.set('active', this.itemNew.active);

            const url = 'references/company/branchnew';

            this.$axios.post(url, item).then((response) => {
                const { data } = response;
                if (data.success) {
                    this.$coresnackbars.success(data.message);

                    this.itemNew.name = '';
                    this.itemNew.active = 'A';
                    this.refreshAction();
                }

                this.loading = false;
            }).catch((error) => {
                const {statusText, data} = error;
                this.$coresnackbars.error(statusText);

                if (typeof data !== "undefined" && typeof data.message !== "undefined") {
                    if (typeof data.message === 'object') {
                    this.itemError = Object.assign({}, this.itemError, data.message);
                    }
                }

                this.loading = false;
            });
        },
        saveEditDialog(payload) {
            if (this.loading) {
                return false;
            }

            this.itemError.name = '';
            this.itemError.active = '';
            this.loading = true;

            const item = new FormData();
            item.set('id', payload.id);
            item.set('name', payload.name);
            item.set('companyId', this.companyId);
            item.set('active', payload.active);

            const url = 'references/company/branchedit';

            this.$axios.post(url, item).then((response) => {
                const { data } = response;
                if (data.success) {
                    this.$coresnackbars.success(data.message);
                    this.refreshAction();
                }

                this.loading = false;
            }).catch((error) => {
                const {statusText, data} = error;
                this.$coresnackbars.error(statusText);

                this.loading = false;
            });
        },
        removeAction(item) {
            if (this.loading) {
                return false;
            }

            const confirmText = sprintf(this.$t('confirm:remove_text'), item.name);
            if (confirm(confirmText)) {
                this.loading = true;
                const that = this;
                this.$axios.get('references/company/branchremove/', {
                    params: {id: item.id}
                })
                .then(response => {
                    const {data} = response;

                    that.$coresnackbars.success(data.message);

                    if (data.success) {
                    that.refreshAction();
                    }
                })
                .catch(function (error) {
                    const { statusText, status } = error;
                    that.$coresnackbars.error('Code: ' + status + ' ' + statusText);
                })
                .then(() => {
                    that.loading = false;
                });
            }
        }
    }
}
</script>
