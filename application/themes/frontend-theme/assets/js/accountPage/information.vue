<template>
    <v-container>
        <h3 class="mb-4 mt-4">Your current ticket</h3>

        <v-row>
            <v-col cols="12" lg="9">
                <v-data-iterator
                    :items="items"
                    :server-items-length="totalItems"
                    :options.sync="pagination"
                    :loading="loading">
                    <template v-slot:default="props">
                        <v-row>
                            <v-col 
                                v-for="item in props.items"
                                cols="12" md="6"
                                :key="item.uid">
                                <v-hover v-slot:default="{ hover }">
                                    <v-card outlined
                                        tag="a" 
                                        :flat="!hover"
                                        :elevation="hover ? 4 : 0"
                                        :to="{name: 'detailticket', params: {id: item.uid}}">
                                        <v-card-title class="pb-2 pt-3" style="line-height:1.2">
                                            <div>
                                                <div class="body-2 font-weight-bold">#{{item.number}}</div>
                                                <small class='caption'>{{$moment(item.createdAt).format('DD/MM/YYYY HH:mm:ss')}}</small>
                                            </div>
                                            <v-spacer></v-spacer>
                                            <ticket-flag-chip :flag="item.flag"></ticket-flag-chip>
                                        </v-card-title>
                                        <v-divider></v-divider>
                                        <v-card-text>
                                            <div style="max-height: 24px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;" class="body-2 black--text">{{ item.subject }}</div>
                                            <div style="max-height: 24px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">{{ item.descr }}</div>
                                        </v-card-text>
                                        <v-divider></v-divider>
                                        <v-card-actions style="background: #fafafa;">
                                            <div class="px-2">
                                                <priority-icon :priority-name="item.priorityName"></priority-icon>
                                            </div>
                                            <v-spacer></v-spacer>
                                            <div class="px-2 text-xs-right">
                                                <span class="caption">Category</span><br/>
                                                <span class="caption font-weight-bold">{{item.categoryName}}</span>
                                            </div>
                                        </v-card-actions>
                                    </v-card>
                                </v-hover>
                            </v-col>
                        </v-row>
                    </template>
                </v-data-iterator>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
import TicketFlagChip from '../../../../backend-theme/assets/js/components/core/FlagChip.vue'
import PriorityIcon from '../../../../backend-theme/assets/js/components/core/PriorityIcon.vue'
export default {
    name: 'account-information-page',
    components: {
        TicketFlagChip, PriorityIcon
    },
    data() {
        return {
            pagination: {},
            items: [],
            totalItems: 0,
            loading: false,
            searchText: null,
        }
    },
    watch: {
        pagination() {
            this.refreshAction();
        }
    },
    methods: {
        getDataFromApi () {
            this.loading = true;
            const that = this;
            return new Promise((resolve, reject) => {
                const { sortBy, sortDesc, page, itemsPerPage, filters } = this.pagination;
                let sortOrders = []
                for (let index = 0; index < sortDesc.length; index++) {
                    const element = sortDesc[index]
                    sortOrders.push(element ? 'desc' : 'asc')
                }
                that.$axios.get('profile/personalinfo/currentticket', {
                    params: {
                        sortFields: sortBy,
                        sortOrders: sortOrders,
                        page: page,
                        limit: itemsPerPage,
                        search: this.searchText,
                        filters: filters,
                    }
                }).then(response => {
                    that.loading = false;
                    resolve({
                        items: response.data.rows,
                        total: response.data.total,
                    });
                });
            });
        },
        refreshAction() {
            this.getDataFromApi().then(data => {
                this.items = data.items
                this.totalItems = data.total
            })
    },
    }
}
</script>
