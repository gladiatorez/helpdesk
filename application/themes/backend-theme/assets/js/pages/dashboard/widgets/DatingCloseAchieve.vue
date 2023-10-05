<template>
    <v-card :outlined="outlined" :flat="outlined">
        <v-card-text class="pa-0">
            <v-row>
                <v-col cols="12" md="8">
                    <highcharts :options="chartOptions"></highcharts>
                </v-col>
                <v-col cols="12" md="4" class="d-flex align-center justify-center flex-column">
                    <v-card outlined flat class="mb-4">
                        <v-simple-table dense>
                            <thead>
                            <tr>
                                <th>Status</th>
                                <th class="text-right">Jumlah</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(row, index) in tableData" :key="index">
                                <td>{{ row.text }}</td>
                                <td class="text-right">{{ row.value }}</td>
                            </tr>
                            </tbody>
                        </v-simple-table>
                    </v-card>

                    <v-card outlined flat>
                        <v-simple-table dense>
                            <thead>
                            <tr>
                                <th>Lead Time</th>
                                <th class="text-right">Nilai</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td style="font-size: 12px;">Target</td>
                                <td style="font-size: 12px;" class="text-right">{{ serviceRateTarget }}%</td>
                            </tr>
                            <tr>
                                <td style="font-size: 12px;">Actual</td>
                                <td
                                    style="font-size: 12px;"
                                    class="text-right"
                                    :class="{'error white--text': serviceRateActual < serviceRateTarget, 'green accent-4': serviceRateActual >= serviceRateTarget}">
                                    {{ serviceRateActual }}%
                                </td>
                            </tr>
                            </tbody>
                        </v-simple-table>
                    </v-card>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>

<script>
import {Chart} from 'highcharts-vue';

export default {
    components: {
        highcharts: Chart
    },
    props: {
        seriesData: Array,
        tableData: Array,
        serviceRateTarget: Number,
        serviceRateActual: Number,
        outlined: Boolean
    },
    data() {
        return {
            defaultOptions: {
                chart: {
                    type: 'line',
                    height: '270px',
                    style: {
                        fontFamily: '"Roboto", sans-serif',
                    }
                },
                colors: ['#1E88E5'],
                title: {
                    text: 'Tiket Close vs Tiket Achieve (Lead Time)',
                },
                xAxis: {
                    categories: ['Tiket Close', 'Tiket Achieve']
                },
                yAxis: {
                    title: {
                        text: 'Jumlah'
                    }
                },
                plotOptions: {
                    line: {
                        dataLabels: {
                            enabled: true
                        },
                    }
                },
                series: [{
                    name: 'Tiket',
                    data: []
                }]
            },
        }
    },
    computed: {
        chartOptions() {
            const options = this.defaultOptions
            options.series[0].data = this.seriesData
            return options
        }
    },
}
</script>
