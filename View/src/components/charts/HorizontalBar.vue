<template>
  <div class="bar-chart">
    <div class="no-data" v-if="loading || dataset.length === 0">
      <div class="overlay">
        <template v-if="loading">{{ $t('loading') }}</template>
        <template v-else>{{ $t('chart.noData') }}</template>
      </div>
      <div class="bar-container" v-for="(bar, i) in fakeData" :key="i">
        <div class="bar" :style="{width: bar.valueBase100 + '%' }"></div>
        <!-- <div class="label">{{ bar.label }}</div> -->
        <!-- <div class="val">{{ valueFormat(bar.value) }}</div> -->
      </div>
    </div>
    <div
      v-else
      class="bar-container"
      v-for="(bar, i) in bars"
      :key="i"
      :class="{ average: bar.isAverage }"
      @click="emitId(bar)"
    >
      <div class="bar" :style="{width: bar.valueBase100 + '%' }"></div>
      <div class="label">{{ bar.label }}</div>
      <div class="val">{{ valueFormat(bar.value) }}</div>
    </div>
  </div>
</template>

<script>
import { mean } from 'simple-statistics'

export default {
  name: 'HorizontalBar',

  props: {
    dataset: {
      type: Array,
      default: () => []
    },
    withAverage: {
      type: Boolean,
      default: true
    },
    format: {
      type: String,
      default: 'default'
    },
    loading: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      fakeData: [
        { valueBase100: 100 },
        { valueBase100: 80 },
        { valueBase100: 60 },
        { valueBase100: 40 },
        { valueBase100: 20 }
      ]
    }
  },

  computed: {
    bars () {
      const dataset = [...this.dataset]

      if (dataset.length > 1 && this.withAverage) {
        const meanValue = mean(this.dataset.map(d => d.value)).toFixed(0)
        dataset.push({ label: this.$t('chart.average'), value: meanValue, isAverage: true })
      }

      if (dataset.length > 1) {
        dataset.sort((a, b) => b.value - a.value)
      }

      const base100Multiplier = 100 / dataset[0].value

      return dataset.map(data => {
        data.valueBase100 = data.value * base100Multiplier
        return data
      })
    }
  },

  methods: {
    valueFormat (value) {
      if (this.format === 'percent') {
        return new Intl.NumberFormat(this.$i18n.locale, { style: 'percent' }).format(value)
      } else {
        return new Intl.NumberFormat(this.$i18n.locale, { maximumFractionDigits: 0 }).format(value)
      }
    },

    emitId (bar) {
      if (!bar.isAverage) {
        this.$emit('click', bar.id)
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.bar-container {
  display: grid;
  grid-template-columns: 1fr max-content;
  grid-template-rows: max-content;
  align-items: center;
  margin-bottom: 8px;
  color: var(--color-p-900);
}

.bar {
  grid-column: 1 / span 2;
  grid-row: 1 / 2;
  height: 32px;
  border-radius: 20px;
  background-color: var(--color-p-100);

  .bar-container.average & {
    background-color: var(--color-p-500);
  }

  .no-data & {
    background-color: var(--color-n-200);
  }
}

.label {
  grid-column: 1 / 2;
  grid-row: 1 / 2;
  padding: 0 8px;
  font-size: 14px;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;

  // .bar-container.average & {
  //   mix-blend-mode: color-burn;
  // }
}

.val {
  grid-column: 2 / 3;
  grid-row: 1 / 2;
  padding-right: 8px;
  font-size: 14px;
  font-weight: $fw-semibold;

  // .bar-container.average & {
  //   mix-blend-mode: color-burn;
  // }
}

.no-data {
  position: relative;
}

.no-data .overlay {
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

.desktop {
  .label {
    font-size: 16px;
    padding: 0 16px;
  }

  .val {
    font-size: 16px;
    padding-right: 16px;
  }
}
</style>
