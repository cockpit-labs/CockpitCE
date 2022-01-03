<script>
import Chart from 'chart.js'
import { Line, mixins } from 'vue-chartjs'
import 'chartjs-adapter-luxon'

Chart.defaults.global.defaultFontFamily = '"Nunito Sans", Helvetica, Arial, sans-serif'

export default {
  extends: Line,

  mixins: [mixins.reactiveProp],

  props: {
    chartData: {
      type: Object,
      default: null
    }
  },

  data () {
    var vm = this

    return {
      options: {
        layout: {
          padding: {
            left: 0,
            right: 0,
            top: 12,
            bottom: 0
          }
        },
        responsive: true,
        maintainAspectRatio: false,
        legend: {
          display: false
        },
        tooltips: {
          intersect: false
        },
        scales: {
          xAxes: [{
            type: 'time',
            time: {
              unit: 'day',
              displayFormats: {
                day: 'd MMM'
              }
            },
            gridLines: {
              drawOnChartArea: false
            }
          }],
          yAxes: [{
            gridLines: {
              drawOnChartArea: false
            }
          }]
        },
        onClick: function (event, element) {
          const e = element[0]
          if (e) {
            const yValue = this.data.datasets[0].data[e._index]
            vm.$emit('click', { x: yValue.x, y: yValue.y })
          }
        }
      }
    }
  },

  mounted () {
    this.renderChart(this.chartData, this.options)
  }
}
</script>
