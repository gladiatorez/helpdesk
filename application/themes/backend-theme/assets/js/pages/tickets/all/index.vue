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
                                :to="{name: 'tickets.list.detail', params: {id: item.id}}" 
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
                v-model="filterSBUValue"
                :items="filterSBUItems"
                label="Filter SBU"
                @change="refreshAction"
            ></v-autocomplete>

            <v-autocomplete outlined multiple small-chips clearable dense
                v-model="filterCategoryValue"
                :items="filterCategoryItems"
                :label="'Filter ' + $t('tickets::lb:category')"
                @change="refreshAction"
            ></v-autocomplete>

            <v-autocomplete outlined multiple small-chips clearable dense
                v-model="filterProcessValue"
                :items="filterProcessItems"
                :label="'Filter ' + $t('tickets::lb:process')"
                @change="refreshAction"
            ></v-autocomplete>

            <v-autocomplete outlined multiple small-chips clearable dense
                v-model="filterPriorityValue"
                :items="filterPriorityItems"
                :label="'Filter ' + $t('tickets::lb:priority')"
                @change="refreshAction"
            ></v-autocomplete>

            <v-autocomplete outlined multiple small-chips clearable dense
                v-model="filterStaffValue"
                :items="filterStaffItems"
                :label="'Filter ' + $t('tickets::lb:staff')"
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
    name: 'tickets-page',
    components: {
        AppFormDialog: () => import('../../../components/AppFormDialog.vue'),
        TicketItem: () => import('../../../components/Ticket/TicketItem.vue')
    },
    data() {
        return {
            filterDialog: false,
            filterSBUValue: [],
            filterSBUItems: [],
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
            filterStaffValue: [],
            filterStaffItems: [],
            filterCategoryValue: [],
            filterCategoryItems: [],
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
            if (this.filterSBUValue.length > 0 || 
                this.filterProcessValue.length > 0 || 
                this.filterPriorityValue.length > 0 || 
                this.filterStaffValue.length > 0 ||
                this.filterCategoryValue.length > 0) {
                return true;
            }

            return false;
        },
        filterTextStatus() {
            if (!this.isFiltered) {
                return '';
            }
            
            let statusText = [];
            if (this.filterSBUValue.length > 0) {
                statusText.push('By SBU (' + this.filterSBUValue.length + ')');
            }
            if (this.filterProcessValue.length > 0) {
                statusText.push('By Process (' + this.filterProcessValue.length + ')');
            }
            if (this.filterPriorityValue.length > 0) {
                statusText.push('By Priority (' + this.filterPriorityValue.length + ')');
            }
            if (this.filterStaffValue.length > 0) {
                statusText.push('By Staff (' + this.filterStaffValue.length + ')');
            }
            if (this.filterCategoryValue.length > 0) {
                statusText.push('By Category (' + this.filterCategoryValue.length + ')');
            }
            return statusText.join(' - ');
        }
    },
    created() {
        this.$root.$on('page-header:refresh-action', this.refreshAction);
        this.$root.$on('page-header:search-action', this.searchAction);
        this.$root.$on('page-header:search-cancel-action', this.searchClearAction);
        this.fetchSBU();
        this.fetchStaff();
        this.fetchCategory();
    },
    destroyed() {
        this.$root.$off('page-header:refresh-action', this.refreshAction);
        this.$root.$off('page-header:search-action', this.searchAction);
        this.$root.$off('page-header:search-cancel-action', this.searchClearAction);
    },
    methods: {
        createAction() {
            this.$router.push({
                name: 'tickets.create'
            });
        },
        refreshAction() {
            this.loading = true;
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
            if (this.filterSBUValue.length > 0) {
                let filtersSBU = [];
                this.filterSBUValue.forEach(element => {
                    filtersSBU.push(element);
                });
                if (filtersSBU.length > 0) {
                    filters.push({
                        field: 'companyId', operator: 'in', value: filtersSBU
                    });
                }
            }
            if (this.filterCategoryValue.length > 0) {
                let filtersCategory = [];
                this.filterCategoryValue.forEach(element => {
                    filtersCategory.push(element);
                });
                if (filtersCategory.length > 0) {
                    filters.push({
                        field: 'categoryId', operator: 'in', value: filtersCategory
                    });
                }
            }
            if (this.filterStaffValue.length > 0) {
                let filtersStaff = [];
                this.filterStaffValue.forEach(element => {
                    filtersStaff.push(element);
                });
                if (filtersStaff.length > 0) {
                    filters.push({
                        field: 'staffId', operator: 'in', value: filtersStaff
                    });
                }
            }

            if (filters.length > 0) {
                this.tableOptions.filters = filters;
            } else {
                this.tableOptions.filters = [];
            }

            fetchDtRows('tickets', this.tableOptions, this.searchText).then(data => {
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
        fetchSBU() {
            const that = this;
            that.filterSBUItems = [];
            that.$axios.get('tickets/sbulist').then(response => {
                const { data } = response;

                that.filterSBUItems = [];
                data.forEach(element => {
                    that.filterSBUItems.push({
                    value: element.id,
                    text: element.name
                    })
                });
            }).catch(error => {
                console.log(error);
            });
        },
        fetchCategory() {
            const that = this;
            that.$axios.get('tickets/category').then(response => {
                const { data } = response;

                that.filterCategoryItems = data.rows;
            }).catch(error => {
                console.log(error);
            });
        },
        fetchStaff() {
            const that = this;
            that.filterStaffItems = [];
            that.$axios.get('tickets/staff_list').then(response => {
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
        filterClearAction() {
            this.filterSBUValue = [];
            this.filterProcessValue = [];
            this.filterPriorityValue = [];
            this.filterStaffValue = [];

            this.refreshAction();
        }
    },
}
</script>
