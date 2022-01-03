<template>
  <div class="answer">
    <div v-if="readOnly" class="read-only">
      <div class="user-answer" v-if="localValue">{{ formatValue(localValue) }}</div>
      <div class="no-data" v-else>{{ $t('question.noAnswer') }}</div>
    </div>

    <template v-else>
      <Datetime
        v-model="localValue"
        :type="type"
        :phrases="{ok: $t('ok'), cancel: $t('cancel')}"
        auto
        @input="commitValue"
      />
      <button class="reset" @click="reset"><SIcon name="times" fw /></button>
    </template>
  </div>
</template>

<script>
import questionUtils from '@/mixins/questions'
import { Datetime } from 'vue-datetime'
import 'vue-datetime/dist/vue-datetime.css'
import { DateTime } from 'luxon'

export default {
  name: 'QuestionDatetime',

  components: {
    Datetime
  },

  mixins: [questionUtils],

  data () {
    return {
      localValue: this.getRawValue()
    }
  },

  computed: {
    date () {
      const { date = true } = this.question.writeRenderer
      return date
    },

    time () {
      const { time = false } = this.question.writeRenderer
      return time
    },

    type () {
      if (this.date && this.time) {
        return 'datetime'
      } else if (this.time) {
        return 'time'
      } else {
        return 'date'
      }
    }
  },

  methods: {
    commitValue () {
      this.setRawValue(this.localValue)
    },

    reset () {
      this.localValue = null
      this.setRawValue(null)
    },

    formatValue (datetime) {
      switch (this.type) {
        case 'datetime':
          return DateTime.fromISO(datetime).toLocaleString(DateTime.DATETIME_SHORT)
        case 'time':
          return DateTime.fromISO(datetime).toLocaleString(DateTime.TIME_SIMPLE)
        default:
          return DateTime.fromISO(datetime).toLocaleString()
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.answer {
  @include font-stack;
  display: flex;
  justify-content: center;
}

.reset {
  @extend %button-reset;
  padding: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
  border: 1px solid var(--color-n-200);
  border-left: none;
  border-radius: 0 4px 4px 0;
  font-size: 14px;

  &:hover {
    background-color: var(--color-n-200);
    color: var(--color-n-600);
  }

  &:active {
    box-shadow: none;
    transform: translate3d(0, 1px, 0);
  }
}

.user-answer {
  padding: 16px;
  background-color: var(--color-n-000);
  border: 1px solid var(--color-n-200);
  border-radius: 4px;
  font-size: 16px;
  color: var(--color-n-600);
  text-align: center;
}
</style>

<style lang="scss">
.vdatetime-popup {
  @include font-stack;
  border-radius: 10px 10px 8px 8px;
}

.vdatetime-input {
  @include font-stack;
  appearance: none;
  margin: 0;
  border-radius: none;
  padding: 16px;
  background-color: var(--color-n-000);
  border: 1px solid var(--color-n-200);
  border-radius: 4px 0 0 4px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
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
</style>
