<template>
  <v-card>
    <v-card-title>
      <span class="font-weight-bold body-2">{{ getTitle }}</span>
    </v-card-title>
    <v-card-text>
      <highcharts :options="options"></highcharts>
    </v-card-text>
  </v-card>
</template>

<script>
import { Chart } from 'highcharts-vue';
export default {
  name: 'ticket-sbu-chart',
  components: {
    highcharts: Chart
  },
  data() {
    return {
      title: 'SBU',
      year: '',
      month: '',
      options: {
        chart: {
          type: 'bar',
          height: '650px',
          style: {
            fontFamily: '"Roboto", sans-serif',
          }
        },
        title: {
          text: '',
        },
        subtitle: {
          text: '',
        },
        xAxis: {
          categories: [],
          labels: {
            style: {
              color: '000',
              fontSize: '10px',
              textTransform: 'uppercase',
              fontWeight: 'bold',
            }
          }
        },
        yAxis: {
          allowDecimals: false,
          title: {
            text: ''
          },
          labels: {
            style: {
              color: '000'
            }
          }
        },
        plotOptions: {
          line: {
            dataLabels: {
              enabled: true
            },
          }
        },
        series: []
      }
    }
  },
  computed: {
    getTitle() {
      return this.title + ' - ' + this.month + ' ' + this.year
    }
  },
  created() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      const that = this;
      this.$axios.get('dashboard/ticket_sbu').then(({ data }) => {
        that.options.xAxis.categories = [];
        that.options.series = [];

        const datas = data.rows
        let valueRequested = [];
        let valueClosed = [];
        data.rows.forEach(row => {
          that.options.xAxis.categories.push(row.abbr);
          valueRequested.push(row.requested);
          valueClosed.push(row.closed);
        });

        that.year = data.year;
        that.month = data.month;
        that.options.series.push({
          name: 'Opened',
          color: '#B2EBF2',
          marker: {
            fillColor: '#ffffff',
            enabled: true,
            lineWidth: 2,
            lineColor: '#34bfa3',
          },
          data: valueRequested
        });
        that.options.series.push({
          name: 'Closed',
          color: '#00ACC1',
          marker: {
            fillColor: '#ffffff',
            enabled: true,
            lineWidth: 2,
            lineColor: '#34bfa3',
          },
          data: valueClosed
        });
      })
    },
  }
}
</script>
