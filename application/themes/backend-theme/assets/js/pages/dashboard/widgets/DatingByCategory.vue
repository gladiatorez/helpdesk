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
                                <td style="font-size: 12px;">{{ row.text }}</td>
                                <td style="font-size: 12px;" class="text-right">{{ row.value }}</td>
                            </tr>
                            </tbody>
                        </v-simple-table>
                    </v-card>

                    <v-card outlined flat>
                        <v-simple-table dense>
                            <thead>
                            <tr>
                                <th>Kategori</th>
                                <th class="text-right">Jumlah</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(row, index) in tableCategory" :key="index">
                                <td style="font-size: 12px;">{{ row.text }}</td>
                                <td style="font-size: 12px;" class="text-right">{{ row.value }}</td>
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
        tableCategory: Array,
        outlined: Boolean
    },
    data() {
        return {
            defaultOptions: {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie',
                    height: '270px',
                    style: {
                        fontFamily: '"Roboto", sans-serif',
                    }
                },
                title: {
                    text: 'Tiket masuk berdasarkan kategori',
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                accessibility: {
                    point: {
                        valueSuffix: '%'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                        },
                    }
                },
                series: [{
                    name: 'Brands',
                    colorByPoint: true,
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
