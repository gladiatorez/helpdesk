<template>
  <v-card>
    <v-card-title>
      <span>{{ getTitle }}</span>
    </v-card-title>
    <v-card-text>
      <highcharts :options="options"></highcharts>
    </v-card-text>
  </v-card>
</template>

<script>
import Highcharts from 'highcharts';
import drilldown from 'highcharts/modules/drilldown';
import { Chart } from 'highcharts-vue';
drilldown(Highcharts);

export default {
  name: 'ticket-category-chart',
  components: {
    highcharts: Chart
  },
  data() {
    return {
      title: 'Category',
      year: '',
      month: '',
      options: {
        chart: {
          plotBackgroundColor: null,
          plotBorderWidth: null,
          plotShadow: false,
          type: 'pie',
          height: '280px',
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
        plotOptions: {
          series: {
            dataLabels: {
              enabled: true,
              color: '#34bfa3',
              format: '<b>{point.name}</b>: {point.percentage:.1f} %',
            }
          }
        },
        series: [],
        drilldown: {
          activeDataLabelStyle: {
            color: '#34bfa3',
            textDecoration: 'none'
          },
          drillUpButton: {
            position: {
              align: 'left'
            }
          },
          series: []
        }
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
      this.$axios.get('dashboard/ticket_category').then(({ data }) => {
        that.options.series = [];
        that.options.drilldown.series = [];

        const datas = data.rows
        let values = [];

        data.rows.forEach(row => {
          values.push({
            name: row.name,
            y: parseInt(row.cnt_head),
            drilldown: 'drilldown_' + row.id,
          })

          let childs = []
          if (row.childs) {
            row.childs.forEach(child => {
              const childRow = [child.name, parseInt(child.cnt_child)]
              childs.push(childRow)
            });
          }

          that.options.drilldown.series.push({
            name: row.name,
            id: 'drilldown_' + row.id,
            data: childs
          });
        });

        that.year = data.year;
        that.month = data.month;
        that.options.series.push({
          name: 'Tickets',
          colorByPoint: true,
          data: values
        });
      })
    },
  }
}
</script>
