
<template>
    <v-container fluid class="pl-5 pr-5 pb-5">
        

        <v-btn rounded small fixed right top 
        color="secondary"
        style="top:55px; z-index:5; right:33px;"
        :to="{name: 'queues.task.create'}"
        >
        
        <v-icon left>add</v-icon>
        {{$t('btn::create')}}
      </v-btn>

        

        <v-card flat color="transparent" class="mt-5" :loading="loading" :disabled="loading">
            <v-data-iterator row wrap
                sort-by="createdAt"
                :sort-desc="true"
                :items="items"
                :server-items-length="totalItems"
                :options.sync="tableOptions"
                :loading="loading">
                <template v-slot:default="props">
                    <v-row>
                        <v-col 
                            v-for="item in props.items"
                            :key="item.id"
                            cols="12" xl="6"
                            class="py-0">
                            <ticket-item
                                class="mb-3"
                                :item="item"
                                :to="{name: 'queues.task.detail', params: {id: item.id}}" 
                                :ripple="false"
                            />
                        </v-col>
                    </v-row>
                </template>
            </v-data-iterator>
        </v-card>

        <app-form-dialog 
            hide-form-action
            v-model="filterDialog"
            title="Filters"
            max-width="500"
            :persistent="false"
            @close="filterDialog = false">
            <v-autocomplete outlined multiple small-chips clearable dense
                v-model="filterProcessValue"
                :items="filterProcessItems"
                :label="'Filter ' + $t('queues::lb:process')"
                @change="refreshAction"
            ></v-autocomplete>

            <v-autocomplete outlined multiple small-chips clearable dense
                v-model="filterPriorityValue"
                :items="filterPriorityItems"
                :label="'Filter ' + $t('queues::lb:priority')"
                @change="refreshAction"
            ></v-autocomplete>

            <template v-slot:form-action>
                <v-card-actions>
                    <v-btn small depressed 
                        color="primary"
                        @click="filterClearAction">
                        Clear
                    </v-btn>
                </v-card-actions>
            </template>
        </app-form-dialog>
    </v-container>
</template>

<script>
import { fetchDtRows } from '../../../utils/helpers';
export default {
    name: 'queues-page',
    components: {
        AppFormDialog: () => import('../../../components/AppFormDialog.vue'),
        TicketItem: () => import('../../../components/Ticket/TicketItem.vue'),
    },
    data() {
        return {
            filterDialog: false,
            filterProcessValue: ['PROGRESS','HOLD'],
            filterProcessItems: [
                { value: 'REQUESTED', text: 'Request' },
                { value: 'OPENED', text: 'Open' },
                { value: 'PROGRESS', text: 'Progress' },
                { value: 'FINISHED', text: 'Finish' },
                { value: 'CLOSED', text: 'Close' },
                { value: 'HOLD', text: 'Hold' },
            ],
            filterPriorityValue: [],
            filterPriorityItems: [
                { value: '1', text: 'High' },
                { value: '2', text: 'Normal' },
                { value: '3', text: 'Low' },
            ],
            tableOptions: {},
            items: [],
            totalItems: 0,
            loading: false,
            searchText: null,
        }
    },
    computed: {
        isFiltered() {
            if (this.filterProcessValue.length > 0 || this.filterPriorityValue.length > 0) {
                return true;
            }
            return false;
        },
        filterTextStatus() {
            if (!this.isFiltered) {
                return '';
            }
            
            let statusText = [];
            if (this.filterProcessValue.length > 0) {
                statusText.push('By Process (' + this.filterProcessValue.length + ')');
            }
            if (this.filterPriorityValue.length > 0) {
                statusText.push('By Priority (' + this.filterPriorityValue.length + ')');
            }
            return statusText.join(' - ');
        }
    },
    watch: {
        tableOptions() {
            this.refreshAction();
        }
    },
    created() {
        this.$root.$on('page-header:refresh-action', this.refreshAction);
        this.$root.$on('page-header:search-action', this.searchAction);
        this.$root.$on('page-header:search-cancel-action', this.searchClearAction);
    },
    destroyed() {
        this.$root.$off('page-header:refresh-action', this.refreshAction);
        this.$root.$off('page-header:search-action', this.searchAction);
        this.$root.$off('page-header:search-cancel-action', this.searchClearAction);
    },
    methods: {
        getDataFromApi () {
            this.loading = true;
            const that = this;
            return new Promise((resolve, reject) => {
                            const { sortBy, descending, page, rowsPerPage } = this.pagination;
                            
                            let params = {
                                sort: sortBy,
                                order: descending ? 'desc' : 'asc',
                                page: page,
                                limit: rowsPerPage,
                                search: this.searchText
                            }

                            let filters = [];
                            if (this.filterProcessValue.length > 0) {
                                let filtersProcess = [];
                                this.filterProcessValue.forEach(element => {
                                    filtersProcess.push(element);
                                });
                                if (filtersProcess.length > 0) {
                                    filters.push({
                                        field: 'flag', operator: 'in', value: filtersProcess
                                    });
                                }
                            }
                            if (this.filterPriorityValue.length > 0) {
                                let filtersPriority = [];
                                this.filterPriorityValue.forEach(element => {
                                    filtersPriority.push(element);
                                });
                                if (filtersPriority.length > 0) {
                                    filters.push({
                                        field: 'priorityId', operator: 'in', value: filtersPriority
                                    });
                                }
                            }

                            if (filters.length > 0) {
                                params.filters = filters;
                            }

                            that.$axios.get('queues/assignment', { params })
                                .then(response => {
                    that.loading = false;
                    resolve({
                        items: response.data.rows,
                        total: response.data.total,
                    });
                });
            });
        },
        refreshAction() {
            let filters = [];
            if (this.filterProcessValue.length > 0) {
                let filtersProcess = [];
                this.filterProcessValue.forEach(element => {
                    filtersProcess.push(element);
                });
                if (filtersProcess.length > 0) {
                    filters.push({
                        field: 'flag', operator: 'in', value: filtersProcess
                    });
                }
            }
            if (this.filterPriorityValue.length > 0) {
                let filtersPriority = [];
                this.filterPriorityValue.forEach(element => {
                    filtersPriority.push(element);
                });
                if (filtersPriority.length > 0) {
                    filters.push({
                        field: 'priorityId', operator: 'in', value: filtersPriority
                    });
                }
            }

            if (filters.length > 0) {
                this.tableOptions.filters = filters;
            } else {
                this.tableOptions.filters = [];
            }

            fetchDtRows('queues/assignment', this.tableOptions, this.searchText).then(data => {
                this.items = data.items
                this.totalItems = data.total
            }).catch(error => {
                this.loading = false;
            }).then(() => {
                this.loading = false;
            })
        },
        searchAction(payload) {
            this.searchText = payload;
            this.refreshAction();
        },
        searchClearAction() {
            this.searchText = null;
            this.refreshAction();
        },
        filterClearAction() {
            this.filterProcessValue = []
            this.filterPriorityValue = []

            this.refreshAction()
        }
    },
}
</script>
