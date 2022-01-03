<template>
  <div class="line-chart">
    <div class="overlay" v-if="loading || dataset.datasets[0].data.length === 0">
      <template v-if="loading">{{ $t('loading') }}</template>
      <template v-else>{{ $t('chart.noData') }}</template>
    </div>
    <LineChartJs :chartData="chartdata" @click="clickedNode => $emit('click', clickedNode)" />
  </div>
</template>

<script>
import LineChartJs from '@/components/charts/LineChartJs'

export default {
  name: 'LineChart',

  components: {
    LineChartJs
  },

  props: {
    dataset: {
      type: Object
    },
    loading: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      noData: {
        datasets: [
          {
            label: 'Score',
            backgroundColor: cssvar('--color-n-100'),
            borderColor: cssvar('--color-n-200'),
            borderWidth: 5,
            data: [
              { x: new Date('2020-01-01').toISOString(), y: 10 },
              { x: new Date('2020-01-08').toISOString(), y: 30 },
              { x: new Date('2020-01-15').toISOString(), y: 40 },
              { x: new Date('2020-01-22').toISOString(), y: 35 },
              { x: new Date('2020-01-29').toISOString(), y: 60 },
              { x: new Date('2020-02-05').toISOString(), y: 70 },
              { x: new Date('2020-02-12').toISOString(), y: 100 }
            ]
          }
        ]
      }
    }
  },

  computed: {
    chartdata () {
      return this.dataset.datasets[0].data.length > 0 ? this.dataset : this.noData
    }
  }
}

function cssvar (name) {
  return getComputedStyle(document.documentElement).getPropertyValue(name)
}
</script>

<style lang="scss" scoped>
.line-chart {
  position: relative;
}

.overlay {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: rgba($color: #ffffff, $alpha: 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
