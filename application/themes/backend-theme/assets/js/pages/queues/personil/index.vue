<template>
    <v-container fluid class="pl-5 pr-5 pb-5">
        <v-btn fab small fixed right top 
            color="secondary" 
            style="top:70px; z-index:5; right:33px;"
            @click="filterDialog = true">
            <v-icon>filter_list</v-icon>
        </v-btn>

        <v-alert light dismissible :value="isFiltered" color="blue lighten-5">
            <span class="blue-grey--text text--darken-3">
                <span class="font-weight-bold">Filtered: </span>
                {{filterTextStatus}}
            </span>
        </v-alert>

        <v-card flat color="transparent" class="mt-5" :loading="loading" :disabled="loading">
            <v-data-iterator
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
                                :to="{name: 'queues.personil.detail', params: {id: item.id}}" 
                                :ripple="false"
                            />
                        </v-col>
                    </v-row>
                </template>
                
                <template slot="no-data">
                    <v-alert color="teal lighten-5" :value="true">
                        <div class="teal--text text--darken-2 text-center">
                            <v-icon style="font-size: 60px;" class="teal--text text--darken-2">error_outline</v-icon>
                        </div>
                        <div class="body-1 teal--text text--darken-2 text-center">
                            No data available
                        </div>
                    </v-alert>
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
            <v-autocomplete multiple small-chips outlined clearable
                v-model="filterStaffValue"
                :items="filterStaffItems"
                label="Filter staff"
                @change="refreshAction"
            ></v-autocomplete>

            <v-autocomplete multiple small-chips outlined clearable
                v-model="filterProcessValue"
                :items="filterProcessItems"
                :label="'Filter ' + $t('queues::lb:process')"
                @change="refreshAction"
            ></v-autocomplete>

            <v-autocomplete multiple small-chips outlined clearable
                v-model="filterPriorityValue"
                :items="filterPriorityItems"
                :label="'Filter ' + $t('queues::lb:priority')"
                @change="refreshAction"
            ></v-autocomplete>
        </app-form-dialog>
    </v-container>
</template>

<script>
import { fetchDtRows } from '../../../utils/helpers';
export default {
    name: 'queues-personil-page',
    components: {
        AppFormDialog: () => import('../../../components/AppFormDialog.vue'),
        TicketItem: () => import('../../../components/Ticket/TicketItem.vue')
    },
    data() {
        return {
            filterDialog: false,
            filterStaffValue: [],
            filterStaffItems: [],
            filterProcessValue: [],
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
    watch: {
        tableOptions() {
            this.refreshAction();
        }
    },
    computed: {
        isFiltered() {
            if (this.filterStaffValue.length > 0 || this.filterProcessValue.length > 0 || this.filterPriorityValue.length > 0) {
                return true;
            }
            return false;
        },
        filterTextStatus() {
            if (!this.isFiltered) {
                return '';
            }
            
            let statusText = [];
            if (this.filterStaffValue.length > 0) {
                statusText.push('By Staff (' + this.filterStaffValue.length + ')');
            }
            if (this.filterProcessValue.length > 0) {
                statusText.push('By Process (' + this.filterProcessValue.length + ')');
            }
            if (this.filterPriorityValue.length > 0) {
                statusText.push('By Priority (' + this.filterPriorityValue.length + ')');
            }
            return statusText.join(' - ');
        }
    },
    created() {
        this.$root.$on('page-header:refresh-action', this.refreshAction);
        this.$root.$on('page-header:search-action', this.searchAction);
        this.$root.$on('page-header:search-cancel-action', this.searchClearAction);
        this.fetchStaff();
    },
    destroyed() {
        this.$root.$off('page-header:refresh-action', this.refreshAction);
        this.$root.$off('page-header:search-action', this.searchAction);
        this.$root.$off('page-header:search-cancel-action', this.searchClearAction);
    },
    methods: {
        fetchStaff() {
            const that = this;
            that.filterStaffItems = [];
            that.$axios.get('queues/personil/stafflist').then(response => {
                const { data } = response;

                that.filterStaffItems = [];
                data.staffOptions.forEach(element => {
                    that.filterStaffItems.push({
                    value: element.id,
                    text: element.fullName
                    })
                });
            }).catch(error => {
                console.log(error);
            });
        },
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
                            if (this.filterStaffValue.length > 0) {
                                let filterStaff = [];
                                this.filterStaffValue.forEach(element => {
                                    filterStaff.push(element);
                                });
                                if (filterStaff.length > 0) {
                                    filters.push({
                                        field: 'staffId', operator: 'in', value: filterStaff
                                    });
                                }
                            }

                            if (filters.length > 0) {
                                params.filters = filters;
                            }

                            that.$axios.get('queues/personil', { params })
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
            if (this.filterStaffValue.length > 0) {
                let filterStaff = [];
                this.filterStaffValue.forEach(element => {
                    filterStaff.push(element);
                });
                if (filterStaff.length > 0) {
                    filters.push({
                        field: 'staffId', operator: 'in', value: filterStaff
                    });
                }
            }

            if (filters.length > 0) {
                this.tableOptions.filters = filters;
            } else {
                this.tableOptions.filters = [];
            }

            fetchDtRows('queues/personil', this.tableOptions, this.searchText).then(data => {
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
    },
}
</script>
