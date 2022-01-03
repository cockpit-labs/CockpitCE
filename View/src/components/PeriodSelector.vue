<template>
  <div class="period-selector">
    <div class="selector">
      <SButton v-if="!isCustomPeriod" class="btn prev" @click="prevPeriod" :disabled="disabled || noPrevPeriod">
          <SIcon name="angle-left" fw />
      </SButton>
      <SButton class="current-period" @click="showPeriods" :disabled="disabled">
        <div class="loader" v-if="loading">{{ $t('loading') }}</div>
        <div v-if="!loading && !noPeriods">{{ `${dateStart.toFormat('DD')} - ${dateEnd.toFormat('DD')}` }}</div>
        <div v-if="!loading && noPeriods">{{ $t('periodSelector.noPeriod') }}</div>
      </SButton>
      <SButton v-if="!isCustomPeriod" class="btn next" @click="nextPeriod" :disabled="disabled || noNextPeriod">
          <SIcon name="angle-right" fw />
      </SButton>
    </div>

    <ModalView ref="periodsList">
      <template #title>{{ $t('periodSelector.periods') }}</template>

      <div class="period-list" v-show="!showCustomPeriod">
        <div
          v-for="(period, i) in periods"
          :key="i"
          :class="['period', {selected: i === selectedPeriod}]"
          @click="selectPeriod(i)">
          {{ formatPeriod(period) }}
        </div>
      </div>

      <div class="custom-period" v-show="showCustomPeriod">
        <label>{{ $t('periodSelector.dateStart') }}</label>
        <SDatetime v-model="customPeriodStart" :max="endOfToday" />
        <label>{{ $t('periodSelector.dateEnd') }}</label>
        <SDatetime v-model="customPeriodEnd" :max="endOfToday" />
        <SButton @click="setCustomPeriod">{{ $t('periodSelector.apply') }}</SButton>
      </div>

      <template #footer>
        <SButtonText v-show="!showCustomPeriod" @click="showCustomPeriod = true">{{ $t('periodSelector.customPeriod') }}</SButtonText>
        <SButtonText v-show="showCustomPeriod" @click="showCustomPeriod = false">{{ $t('periodSelector.showPeriods') }}</SButtonText>
      </template>
    </ModalView>
  </div>
</template>

<script>
import ModalView from '@/components/ModalView'
import { DateTime } from 'luxon'

export default {
  name: 'PeriodSelector',

  components: {
    ModalView
  },

  props: {
    value: {
      type: Object
    },
    periods: {
      type: Array
    },
    loading: {
      type: Boolean,
      dafault: false
    }
  },

  data () {
    return {
      customPeriodStart: null,
      customPeriodEnd: null,
      showCustomPeriod: false
    }
  },

  computed: {
    dateStart () {
      return this.value ? this.value.dateStart : null
    },

    dateEnd () {
      return this.value ? this.value.dateEnd : null
    },

    selectedPeriod () {
      return this.value ? this.value.selectedPeriod : null
    },

    isCustomPeriod () {
      return this.selectedPeriod === null
    },

    noPeriods () {
      return this.periods === null || this.periods.length === 0
    },

    disabled () {
      return this.loading || this.noPeriods
    },

    endOfToday () {
      return DateTime.local().endOf('day').toISO()
    },

    noPrevPeriod () {
      return this.selectedPeriod === 0
    },

    noNextPeriod () {
      return this.selectedPeriod === this.periods.length - 1
    }
  },

  methods: {
    showPeriods () {
      this.$refs.periodsList.open()
    },

    formatPeriod (period) {
      const { start, end } = period
      return DateTime.fromISO(start).toFormat('DD') + ' - ' + DateTime.fromISO(end).toFormat('DD')
    },

    prevPeriod () {
      const newIndex = this.selectedPeriod - 1

      if (newIndex >= 0) {
        const { start, end } = this.periods[newIndex]
        this.emitInput(start, end, newIndex)
      }
    },

    nextPeriod () {
      const newIndex = this.selectedPeriod + 1

      if (newIndex <= this.periods.length - 1) {
        const { start, end } = this.periods[newIndex]
        this.emitInput(start, end, newIndex)
      }
    },

    selectPeriod (periodIndex) {
      const { start, end } = this.periods[periodIndex]
      this.emitInput(start, end, periodIndex)
      this.$refs.periodsList.close()
    },

    setCustomPeriod () {
      this.emitInput(this.customPeriodStart, this.customPeriodEnd, null)
      this.$refs.periodsList.close()
    },

    emitInput (start, end, index) {
      this.$emit('input', {
        dateStart: DateTime.fromISO(start),
        dateEnd: DateTime.fromISO(end),
        selectedPeriod: index
      })
    }
  },

  watch: {
    value: {
      handler (newValue) {
        this.customPeriodStart = newValue.dateStart ? newValue.dateStart.toISO() : null
        this.customPeriodEnd = newValue.dateEnd ? newValue.dateEnd.toISO() : null
        this.showCustomPeriod = newValue.selectedPeriod === null
      },
      deep: true,
      immediate: true
    }
  }
}
</script>

<style lang="scss" scoped>
.selector {
  display: flex;
  box-shadow: 0 3px 4px rgba(0, 0, 0, 0.12);
  border-radius: 8px;

  .current-period {
    flex: 1;
    justify-content: center;
    color: var(--color-n-600);
    background-color: var(--color-n-000);
    font-weight: $fw-regular;
    padding: 16px;
    border-radius: 0;
    box-shadow: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;

    &:hover {
      background-color: var(--color-p-100);
      color: var(--color-p-900);
    }

    &:active {
      background-color: var(--color-p-700);
      color: var(--color-n-000);
    }
  }

  .btn {
    padding: 0 12px;
    box-shadow: none;
  }

  .btn.prev {
    border-radius: 8px 0 0 8px;
  }

  .btn.next {
    border-radius: 0 8px 8px 0;
  }
}

.period-list {
  max-height: 350px;
  max-width: 350px;
  padding: 0 16px;
  margin: 0 auto;
  overflow: auto;

  .period {
    margin: 8px 0;
    padding: 12px;
    text-align: center;
    background-color: var(--color-n-000);
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) ;
    cursor: default;
  }

  .period:hover {
    color: var(--color-p-900);
    background-color: var(--color-p-100);
  }

  .period.selected {
    background-color: var(--color-p-800);
    color: var(--color-p-050);
  }
}

.custom-period {
  max-width: 300px;
  margin: 0 auto;

  label {
    margin-left: 8px;
    color: var(--color-n-500);
  }

  .s-datetime {
    margin-bottom: 16px;
  }

  .s-button {
    margin: 32px auto 0;
  }
}
</style>
