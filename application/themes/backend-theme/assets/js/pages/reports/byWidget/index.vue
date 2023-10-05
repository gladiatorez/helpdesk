<template>
    <v-row class="fill-height ma-0"
        align="center"
        justify="center">
        <v-col cols="12" md="6">
            <v-card outlined :loading="loading" :disabled="loading">
                <v-card-title>
                    <span>Report filter</span>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <v-row>
                        <v-col cols="12" md="6">
                            <app-datepicker 
                                v-model="filterDateStart"
                                label="From date"
                                :error-messages="errorMsg.dateStart"
                                :hide-details="!errorMsg.dateStart"
                            />
                        </v-col>
                        <v-col cols="12" md="6">
                            <app-datepicker 
                                v-model="filterDateEnd"
                                label="To date"
                                :error-messages="errorMsg.dateEnd"
                                :hide-details="!errorMsg.dateEnd"
                            />
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-autocomplete
                                :items="categoryOptions"
                                v-model="filterCategory"
                                label="Services"
                                placeholder="Choose category"
                                :error-messages="errorMsg.category"
                                :hide-details="!errorMsg.category"
                                @change="filterCategorySub = 'ALL'"
                            />
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-autocomplete
                                :items="categorySubOptions"
                                :disabled="!filterCategory"
                                :error-messages="errorMsg.categorySub"
                                :hide-details="!errorMsg.categorySub"
                                v-model="filterCategorySub"
                                label="Category"
                                placeholder="Choose category sub"
                            />
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-autocomplete
                                :items="sbuOptions"
                                v-model="filterSbu"
                                label="SBU"
                                placeholder="Choose sbu"
                                :error-messages="errorMsg.sbu"
                                :hide-details="!errorMsg.sbu"
                            />
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-autocomplete
                                :items="staffOptions"
                                v-model="filterStaff"
                                label="Staff"
                                placeholder="Choose staff"
                                :error-messages="errorMsg.staff"
                                :hide-details="!errorMsg.staff"
                            />
                        </v-col>
                        <v-col cols="12" md="12">
                            <v-autocomplete
                                :items="flagOptions"
                                v-model="filterFlag"
                                label="Status"
                                placeholder="Choose status"
                                :error-messages="errorMsg.flag"
                                :hide-details="!errorMsg.flag"
                            />
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions>
                    <v-btn small color="primary" @click="requestAction">
                        SUBMIT
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-col>
    </v-row>
</template>

<script>
import AppDatepicker from '../../../components/AppDatepicker.vue';
export default {
    name: 'report-by-widget-page',
    components: {
        AppDatepicker
    },
    data() {
        return {
            loading: false,
            filterDateStart: '',
            filterDateEnd: '',
            filterSbu: 'ALL',
            filterStaff: 'ALL',
            filterCategory: '',
            filterCategorySub: '',
            filterFlag: 'ALL',
            errorMsg: {
                dateStart: '',
                dateEnd: '',
                sbu: '',
                staff: '',
                category: '',
                categorySub: '',
                flag: '',
            },
            sbuOptions: [],
            staffOptions: [],
            categories: [],
            flagOptions: [
                {value: 'ALL', text: 'All'},
                {value: 'REQUESTED', text: 'Request'},
                {value: 'OPENED', text: 'Open'},
                {value: 'Cancel', text: 'Cancel'},
                {value: 'PROGRESS', text: 'Progress'},
                {value: 'HOLD', text: 'Hold'},
                {value: 'FINISHED', text: 'Finished'},
                {value: 'CLOSED', text: 'Closed'}
            ],
        }
    },
    computed: {
        categoryOptions() {
            const that = this;
            let options = [];
            that.categories.forEach(element => {
                options.push({
                    value: element.value,
                    text: element.text
                });
            });
            return options;
        },
        categorySubOptions() {
            if (this.categories.length <= 0) {
                return [];
            }
            if (!this.filterCategory) {
                return [];
            }
            
            const findIndex = _.findIndex(this.categories, {value: this.filterCategory});
            
            if (findIndex < 0) {
                return [];
            }

            const category = this.categories[findIndex];
            let childrens = [{
                value: 'ALL',
                text: 'All'
            }];
            category.childrens.forEach(element => {
                childrens.push({
                    value: element.id,
                    text: element.name
                });
            });

            return childrens;
        }
    },
    async created() {
        this.filterDateStart = this.$moment().format('YYYY-MM-DD');
        this.filterDateEnd = this.$moment().format('YYYY-MM-DD');

        this.loading = true;
        await this.fetchOptions();
        this.loading = false
    },
    methods: {
        async fetchOptions() {
            this.parentOptions = [];
            this.staffOptions = [];
            return this.$axios.get('reports/by_widget/form_options').then(response => {
                const { data } = response;
                this.categories = data.categories;
                this.sbuOptions = data.companies;
                this.staffOptions = data.staffs;
            })
            .catch(error => {
                console.error(error);
            });
        },
        clearError() {
            this.errorMsg.dateStart = '';
            this.errorMsg.dateEnd = '';
            this.errorMsg.category = '';
            this.errorMsg.categorySub = '';
            this.errorMsg.sbu = '';
            this.errorMsg.staff = '';
            this.errorMsg.flag = '';
        },
        requestAction() {
            if (this.loading) {
                return false;
            }

            this.clearError();
            this.loading = true;

            const monthSelected = this.$moment(this.pickerMonthValue, 'YYYY-MM');
            
            const item = new FormData();
            item.set('dateStart', this.filterDateStart);
            item.set('dateEnd', this.filterDateEnd);
            item.set('category', this.filterCategory ? this.filterCategory : '');
            item.set('categorySub', this.filterCategorySub ? this.filterCategorySub : '');
            item.set('sbu', this.filterSbu ? this.filterSbu : '');
            item.set('staff', this.filterStaff ? this.filterStaff : '');
            item.set('flag', this.filterFlag ? this.filterFlag : '');

            const url = 'reports/by_widget/request_reports';
            this.$axios.post(url, item)
                .then((response) => {
                    const { data } = response;
                    if (data.success) {
                        this.$coresnackbars.success(data.message);
                        window.open(SITE_URL + '/reports/index?file=' + data.file, '_blank');
                    }

                    this.loading = false;
                })
                .catch((error) => {
                    const {statusText, data} = error;
                    this.$coresnackbars.error(statusText);

                    if (typeof data !== "undefined" && typeof data.message !== "undefined") {
                        if (typeof data.message === 'object') {
                            this.errorMsg = Object.assign({}, this.errorMsg, data.message);
                        }
                    }

                    this.loading = false;
                });
        }
    }
}
</script>
