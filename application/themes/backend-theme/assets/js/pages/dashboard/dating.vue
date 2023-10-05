<template>
    <v-container fluid class="px-6">
        <div class="mt-4 d-flex align-center">
            <v-dialog
                v-model="dateDialog"
                ref="dialog"
                :return-value.sync="dates"
                width="290px"
                persistent>
                <template v-slot:activator="{on}">
                    <v-btn v-on="on" style="letter-spacing: normal;" color="primary" rounded small>
                        <span class="text-capitalize v-icon--left" style="width: auto; height: auto; font-size: 13px;">Date: </span>
                        <span class="text-capitalize">{{ dateFormatted }}</span>
                        <v-icon right>arrow_drop_down</v-icon>
                    </v-btn>
                </template>

                <v-date-picker
                    v-model="dates"
                    no-title scrollable range>
                    <v-spacer></v-spacer>
                    <v-btn text color="primary" @click="dateDialog = false">
                        Cancel
                    </v-btn>
                    <v-btn color="primary" @click="dateChanged">
                        OK
                    </v-btn>
                </v-date-picker>
            </v-dialog>

            <v-dialog v-model="fullScreenDialog" fullscreen scrollable persistent>
                <template v-slot:activator="{on}">
                    <v-btn v-on="on" style="letter-spacing: normal;" color="primary" icon small class="ml-2">
                        <v-icon style="font-size: 16px">ms-Icon ms-Icon--ChromeFullScreen</v-icon>
                    </v-btn>
                </template>
                <v-card>
                    <v-card-title>
                        Dating dashboard
                        <v-chip small class="ml-3" color="blue lighten-4">
                            <span class="blue--text darken-4">{{ dateFormatted }}</span>
                        </v-chip>
                        <v-spacer />
                        <v-btn icon @click="fullScreenDialog = false">
                            <v-icon>close</v-icon>
                        </v-btn>
                    </v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col cols="12" lg="6">
                                <dating-progress-tiket
                                    outlined
                                    class="fill-height"
                                    :series-data="progressTicket.seriesData"
                                    :table-data="progressTicket.tableData"
                                />
                            </v-col>
                            <v-col cols="12" lg="6">
                                <dating-request-vs-close
                                    outlined
                                    class="fill-height"
                                    :series-data="requestVSClosed.seriesData"
                                    :table-data="requestVSClosed.tableData"
                                    :lead-time-target="requestVSClosed.leadTimeTarget"
                                    :lead-time-actual="requestVSClosed.leadTimeActual"
                                />
                            </v-col>
                            <v-col cols="12" lg="6">
                                <dating-close-achieve
                                    outlined
                                    class="fill-height"
                                    :series-data="closeVSAchieve.seriesData"
                                    :table-data="closeVSAchieve.tableData"
                                    :service-rate-target="closeVSAchieve.serviceRateTarget"
                                    :service-rate-actual="closeVSAchieve.serviceRateActual"
                                />
                            </v-col>
                            <v-col cols="12" lg="6">
                                <dating-by-category
                                    outlined
                                    class="fill-height"
                                    :series-data="requestByCategory.seriesData"
                                    :table-data="requestByCategory.tableData"
                                    :table-category="requestByCategory.tableCategory"
                                />
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-dialog>
        </div>

        <v-overlay :value="loading" z-index="9999">
            <div class="white text-center pa-3" style="border-radius: 5px;">
                <img src="../../../img/logo-k.svg" alt="Kalla Group" style="width: 40px">
                <div class="text-center text--secondary font-weight-medium" style="font-size: 11px">Please wait...</div>
            </div>
        </v-overlay>

        <v-row class="mt-2">
            <v-col cols="12" xl="6">
                <dating-progress-tiket
                    :series-data="progressTicket.seriesData"
                    :table-data="progressTicket.tableData"
                />
            </v-col>
            <v-col cols="12" xl="6">
                <dating-request-vs-close
                    :series-data="requestVSClosed.seriesData"
                    :table-data="requestVSClosed.tableData"
                    :lead-time-target="requestVSClosed.leadTimeTarget"
                    :lead-time-actual="requestVSClosed.leadTimeActual"
                />
            </v-col>
            <v-col cols="12">
                <dating-close-achieve
                    :series-data="closeVSAchieve.seriesData"
                    :table-data="closeVSAchieve.tableData"
                    :service-rate-target="closeVSAchieve.serviceRateTarget"
                    :service-rate-actual="closeVSAchieve.serviceRateActual"
                />
            </v-col>
            <v-col cols="12">
                <dating-by-category
                    :series-data="requestByCategory.seriesData"
                    :table-data="requestByCategory.tableData"
                    :table-category="requestByCategory.tableCategory"
                />
            </v-col>
        </v-row>
        <router-view />
    </v-container>
</template>

<script>
import { Chart } from 'highcharts-vue';
import DatingProgressTiket from "./widgets/DatingProgressTiket";
import DatingRequestVsClose from "./widgets/DatingRequestVsClose";
import DatingCloseAchieve from "./widgets/DatingCloseAchieve";
import DatingByCategory from "./widgets/DatingByCategory";
export default {
    name: 'dashboard-dating-page',
    components: {
        DatingByCategory,
        DatingCloseAchieve,
        DatingRequestVsClose,
        DatingProgressTiket,
        highcharts: Chart
    },
    data() {
        return {
            dateDialog: false,
            fullScreenDialog: false,
            dates: [],
            progressTicket: {
                seriesData: [],
                tableData: []
            },
            requestVSClosed: {
                seriesData: [0, 0],
                tableData: [],
                leadTimeTarget: 0,
                leadTimeActual: 0,
            },
            closeVSAchieve: {
                seriesData: [0, 0],
                tableData: [],
                serviceRateTarget: 0,
                serviceRateActual: 0,
            },
            requestByCategory: {
                seriesData: [
                    {
                        name: 'Chrome',
                        y: 61.41,
                        sliced: true,
                        selected: true
                    }, {
                        name: 'Internet Explorer',
                        y: 11.84
                    }, {
                        name: 'Firefox',
                        y: 10.85
                    }, {
                        name: 'Edge',
                        y: 4.67
                    }, {
                        name: 'Safari',
                        y: 4.18
                    }, {
                        name: 'Sogou Explorer',
                        y: 1.64
                    }, {
                        name: 'Opera',
                        y: 1.6
                    }, {
                        name: 'QQ',
                        y: 1.2
                    }, {
                        name: 'Other',
                        y: 2.61
                    }
                ],
                tableData: [],
                tableCategory: [],
            },
            loading: false,
        }
    },
    computed: {
        dateFormatted() {
            if (this.dates.length > 0) {
                const dates = this.dates.sort((a, b) => new Date(a) - new Date(b));
                const startDate = this.$moment(dates[0]);
                const endDate = this.$moment(dates[dates.length - 1]);
                if (startDate.format('YYYY-MM-DD') === endDate.format('YYYY-MM-DD')) {
                    return startDate.format('DD MMM YYYY')
                }
                else if (startDate.format('MM-YYYY') === endDate.format('MM-YYYY')) {
                    return startDate.format('DD') + ' - ' + endDate.format('DD MMM YYYY')
                }
                else {
                    return startDate.format('DD MMM YYYY') + ' - ' + endDate.format('DD MMM YYYY')
                }
            }

            return 'Please choose'
        }
    },
    async created() {
        this.dates = [
            this.$moment().format('YYYY-MM-DD'),
            this.$moment().format('YYYY-MM-DD'),
            // '2021-01-01','2021-01-31'
        ];

        this.loading = true;
        await this.fetchWidget();
        this.loading = false;
    },
    methods: {
        async dateChanged() {
            this.$refs.dialog.save(this.dates)
            this.loading = true;
            await this.fetchWidget();
            this.loading = false;
        },
        fetchWidget() {
            const params = {
                start: this.$moment(this.dates[0]).format('YYYY-MM-DD'),
                end: this.$moment(this.dates[this.dates.length - 1]).format('YYYY-MM-DD'),
            }
            return this.$axios.get('dashboard/dating', {params})
                .then(response => {
                    const { data } = response;
                    this.progressTicket.seriesData = [
                        {
                            name: 'Tiket Progress',
                            y: parseInt(data.progressTicket.tiketProgress),
                        }, {
                            name: 'Tiket Hold',
                            y: parseInt(data.progressTicket.tiketHold),
                        }, {
                            name: 'Tiket Cancel',
                            y: parseInt(data.progressTicket.tiketCancel),
                        }, {
                            name: 'Tiket Finish',
                            y: parseInt(data.progressTicket.tiketFinished),
                        }, {
                            name: 'Tiket Close',
                            y: parseInt(data.progressTicket.tiketClosed),
                        }
                    ];
                    this.progressTicket.tableData = [
                        { text: 'Tiket Masuk', value: parseInt(data.progressTicket.tiketMasuk) },
                        { text: 'Tiket Progress', value: parseInt(data.progressTicket.tiketProgress) },
                        { text: 'Tiket Hold', value: parseInt(data.progressTicket.tiketHold) },
                        { text: 'Tiket Cancel', value: parseInt(data.progressTicket.tiketCancel) },
                        { text: 'Tiket Finish', value: parseInt(data.progressTicket.tiketFinished) },
                        { text: 'Tiket Close', value: parseInt(data.progressTicket.tiketClosed) },
                    ]

                    this.requestVSClosed.seriesData = [
                        parseInt(data.requestVsClosed.tiketOpened), parseInt(data.requestVsClosed.tiketClosed),
                    ]
                    this.requestVSClosed.tableData = [
                        { text: 'Tiket Open', value: parseInt(data.requestVsClosed.tiketOpened) },
                        { text: 'Tiket Close', value: parseInt(data.requestVsClosed.tiketClosed) },
                    ]
                    const actualLeadTime = (parseInt(data.requestVsClosed.tiketClosed) / parseInt(data.requestVsClosed.tiketOpened) * 100).toFixed(0);
                    this.requestVSClosed.leadTimeTarget = parseInt(data.targetLeadTime);
                    this.requestVSClosed.leadTimeActual = parseInt(actualLeadTime);

                    this.closeVSAchieve.seriesData = [
                        parseInt(data.closeVsAchieve.tiketClosed), parseInt(data.closeVsAchieve.tiketAchieve),
                    ]
                    this.closeVSAchieve.tableData = [
                        { text: 'Tiket Close', value: parseInt(data.closeVsAchieve.tiketClosed) },
                        { text: 'Tiket Achieve', value: parseInt(data.closeVsAchieve.tiketAchieve) },
                    ]
                    const actualServiceRate = (parseInt(data.closeVsAchieve.tiketAchieve) / parseInt(data.closeVsAchieve.tiketClosed) * 100).toFixed(0);
                    this.closeVSAchieve.serviceRateTarget = parseInt(data.targetServiceRate);
                    this.closeVSAchieve.serviceRateActual = parseInt(actualServiceRate);

                    this.requestByCategory.tableData = [
                        { text: 'Tiket Open', value: parseInt(data.requestVsClosed.tiketOpened) },
                    ]
                    const categories = []
                    const series = [];
                    data.requestByCategory.forEach((row) => {
                        categories.push({text: row.category_name, value: row.count})
                        series.push({
                            name: row.category_name,
                            y: parseInt(row.count),
                        })
                    })
                    this.requestByCategory.tableCategory = categories
                    this.requestByCategory.seriesData = series
                })
                .catch(error => {
                    console.log(error);
                });
        }
    }
}
</script>