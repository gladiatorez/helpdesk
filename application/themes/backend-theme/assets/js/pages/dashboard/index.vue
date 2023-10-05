<template>
    <v-container fluid class="px-6">
        <v-row>
            <v-col md="4" cols="12">
                <v-card height="340">
                    <v-card-text class="pb-0">
                        <v-row><br><br></v-row>
                        <v-row>
                            <v-col cols="12" md="6">
                                <div class="subheading grey--text text--darken-1" style="font-size: large;">Ticket created</div>
                                <div class="grey--text text--darken-3 title">{{ widget.created }}</div>
                            </v-col>
                            <v-col cols="12" md="6">
                                <div class="subheading grey--text text--darken-1" style="font-size: large;">Ticket Hold</div>
                                <div class="grey--text text--darken-3 title">{{ widget.hold }}</div>
                            </v-col>
                            <v-col cols="12" md="6">
                                <div class="subheading grey--text text--darken-1" style="font-size: large;">Ticket Progress</div>
                                <div class="grey--text text--darken-3 title">{{ widget.progress }}</div>
                            </v-col>
                            <v-col cols="12" md="6">
                                <div class="subheading grey--text text--darken-1" style="font-size: large;">Ticket Close</div>
                                <div class="grey--text text--darken-3 title">{{ widget.close }}</div>
                            </v-col>
                        </v-row>
                        <v-row><br><br><br></v-row>
                    </v-card-text>

                    <count-ticket-chart style="height:100%" />
                </v-card>
            </v-col>

            <v-col md="8" cols="12" class="v-dialog--scrollable">
                <v-card height="600">
                    <v-card-title>
                        Ticket countdown
<!--                        <v-spacer/>-->
<!--                        <span class="px-1 caption">{{ progressCount }} Progress</span> |-->
<!--                        <span class="px-1 caption">{{ holdCount }} Hold</span>-->
                    </v-card-title>
                    <v-card-text class="pa-0">
                        <ticket-countdown
                            :progress-count.sync="progressCount"
                            :hold-count.sync="holdCount"
                        />
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

       

        <router-view />
    </v-container>
</template>

<script>
import CountTicketChart from './widgets/CountTicketChart.vue';
import TicketSbuChart from './widgets/TicketSbuChart.vue';
import TicketCategoryChart from './widgets/TicketCategoryChart.vue';
import TicketStaffTable from './widgets/TicketStaffTable.vue';
import TicketCountdown from './widgets/TicketCountdown.vue';
export default {
    name: 'dashboard-general-page',
    components: {
        CountTicketChart, TicketSbuChart, TicketCategoryChart, TicketStaffTable, TicketCountdown,
    },
    data() {
        return {
            widget: {
                created: 0,
                progress: 0,
                hold: 0,
                close: 0
            },
            progressCount: 0,
            holdCount: 0,
            chartOptions: {
                chart: {
                    style: {
                        fontFamily: '"Roboto", sans-serif',
                    },
                    backgroundColor: '',
                    height: 200
                },
                exporting: {
                    enabled: false,
                },
                credits: {
                    enabled: false,
                },
                title: {
                    text: '',
                },
                subtitle: {
                    text: '',
                },
                xAxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    // visible: false,
                },
                yAxis: {
                    // visible: false,
                    allowDecimals: false,
                    title: {
                        text: ''
                    },
                },
                pane: {
                    size: '100%',
                },
                legend: {
                    enabled: false,
                },
                series: [
                    {
                        name: 'Opened', 
                        type: 'area', 
                        marker: {
                            enabled: false,
                        },
                        data: [480,440,341,210,230,304,312,324,320,350,320,301],
                        color: '#B2EBF2',
                    },
                    {
                        name: 'Closed', 
                        type: 'area', 
                        marker: {
                            enabled: false,
                        },
                        data: [422,414,337,310,330,334,352,354,310,330,360,321],
                        color: '#00ACC1',
                    },
                ]
            }
        }
    },
    created() {
        this.fetchWidget();
    },
    sockets: {
        postingTicket(data) {
            this.fetchWidget()
        }
    },
    methods: {
        fetchWidget() {
            return this.$axios.get('dashboard/widget')
                .then(response => {
                    const { data } = response;

                    this.widget.created = data.ticket_created;
                    this.widget.progress = data.ticket_progress;
                    this.widget.hold = data.ticket_hold;
                    this.widget.close = data.ticket_close;
                })
                .catch(error => {
                    console.log(error);
                });
        },
    }
}
</script>