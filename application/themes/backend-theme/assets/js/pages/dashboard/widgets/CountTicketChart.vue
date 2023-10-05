<template>
  <v-card flat>
    <v-card-text>
      <highcharts :options="options" ref="highchart"></highcharts>
    </v-card-text>
  </v-card>
</template>

<script>
import Highcharts from 'highcharts';
import exportingInit from 'highcharts/modules/exporting';
import { Chart } from 'highcharts-vue';
exportingInit(Highcharts);

export default {
  name: 'count-ticket-chart',
  components: {
    highcharts: Chart
  },
  data() {
    return {
      title: 'Monthly',
      year: '',
      options: {
        chart: {
          type: 'spline',
          style: {
            fontFamily: '"Roboto", sans-serif',
          },
          height: 280
        },
        exporting: {
          enabled: false,
        },
        credits: {
          enabled: false,
        },
        title: {
          text: 'Monthly 2019',
          align: 'left',
          style: {
            fontSize: '13px',
            fontWeight: '700',
            color: '#757575'
          }
        },
        subtitle: {
          text: '',
        },
        xAxis: {
          categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
          labels: {
            style: {
              color: '000',
              fontSize: '10px',
              textTransform: 'uppercase'
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
              enabled: false
            },
          }
        },
        series: []
      },
      fullscreen: false,
    }
  },
  computed: {
    getTitle() {
      return this.title + ' - ' + this.year
    },
    styles() {
      if (this.fullscreen) {
        return {
          position: 'fixed',
          top: 0,
          bottom: 0,
          left: 0, 
          right: 0,
          zIndex: 9999
        }
      }
      return {}
    }
  },
  created() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      const that = this;
      this.$axios.get('dashboard/ticket_monthly').then(({ data }) => {
        const datas = data.data
        that.options.series = [];
        
        let valuesOpened = [];
        for (const key in datas.opened) {
          if (datas.opened.hasOwnProperty(key)) {
            const element = datas.opened[key];
            valuesOpened.push(element);
          }
        }

        let valuesClosed = [];
        for (const key in datas.closed) {
          if (datas.closed.hasOwnProperty(key)) {
            const element = datas.closed[key];
            valuesClosed.push(element);
          }
        }

        that.year = data.year;
        that.options.title.text = 'Monthly ' + data.year;
        that.options.series.push({
          name: 'Opened',
          color: '#ffb822',
          marker: {
            fillColor: '#ffffff',
            enabled: false,
            lineWidth: 2,
            lineColor: '#ffb822',
          },
          data: valuesOpened
        });
        that.options.series.push({
          name: 'Closed',
          color: '#00ACC1',
          marker: {
            fillColor: '#ffffff',
            enabled: false,
            lineWidth: 2,
            lineColor: '#00ACC1',
          },
          data: valuesClosed
        });
      })
    },

    // toggleFullscreen() {
    //   // console.log(Highcharts);
    //   console.log(this.$refs.highchart.chart);
    //   this.$refs.highchart.chart.fullscreen = new Highcharts.FullScreen(this.$refs.highchart.chart.container);
    //   // Highcharts.FullScreen();
    // }
  }
}
</script>
