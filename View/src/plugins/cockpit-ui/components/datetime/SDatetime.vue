<template>
  <div :class="['s-datetime', {disabled}]">
    <Datetime
      :input-id="null"
      v-model="localValue"
      type="date"
      :phrases="{ok: $t('ok'), cancel: $t('cancel')}"
      :placeholder="placeholder"
      :min-datetime="min"
      :max-datetime="max"
      @input="commitValue"
      auto
    />
  </div>
</template>

<script>
import { Datetime } from 'vue-datetime'
import 'vue-datetime/dist/vue-datetime.css'

export default {
  name: 'SDatetime',

  components: {
    Datetime
  },

  props: {
    value: String,
    disabled: {
      type: Boolean,
      default: false
    },
    placeholder: {
      type: String
    },
    min: {
      type: String,
      default: null
    },
    max: {
      type: String,
      default: null
    }
  },

  data () {
    return {
      localValue: this.value
    }
  },

  methods: {
    commitValue () {
      this.$emit('input', this.localValue)
    },

    reset () {
      this.localValue = null
      this.commitValue()
    }
  },

  watch: {
    value (newValue) {
      this.localValue = newValue
    }
  }
}
</script>

<style lang="scss">
.s-datetime {
  .vdatetime-popup {
    @include font-stack;
    border-radius: 10px 10px 8px 8px;
  }

  .vdatetime-input {
    @include font-stack;
    width: 100%;
    appearance: none;
    margin: 0;
    border-radius: none;
    padding: 16px 24px;
    background-color: var(--color-n-000);
    border: none;
    border-radius: 12px;
    box-shadow: 0 3px 4px rgba(0, 0, 0, 0.12);
    font-size: 16px;
    color: var(--color-n-600);
    box-sizing: border-box;
  }

  .vdatetime-popup__header {
    border-radius: 8px 8px 0 0;
  }

  .vdatetime-popup__header,
  .vdatetime-calendar__month__day--selected > span > span,
  .vdatetime-calendar__month__day--selected:hover > span > span {
    background: var(--color-p-500);
  }

  .vdatetime-year-picker__item--selected,
  .vdatetime-time-picker__item--selected,
  .vdatetime-popup__actions__button {
    color: var(--color-p-500);
  }
}
</style>
