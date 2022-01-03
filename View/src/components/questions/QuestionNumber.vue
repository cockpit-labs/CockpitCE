<template>
  <div class="answer">
    <div v-if="readOnly" class="read-only">
      <div class="user-answer" v-if="localValue">{{ valueFormat(localValue) }}</div>
      <div class="no-data" v-else>{{ $t('question.noAnswer') }}</div>
    </div>

    <template v-else>
      <button class="btn decrement" @click="decrement"><SIcon name="minus" fw /></button>
      <input
        type="number"
        :min="min"
        :max="max"
        :step="step"
        inputmode="numeric"
        v-model="localValue"
        @input="commitValue" />
      <button class="btn increment" @click="increment"><SIcon name="plus" fw /></button>
    </template>
  </div>
</template>

<script>
import questionUtils from '@/mixins/questions'

export default {
  name: 'QuestionNumber',

  mixins: [questionUtils],

  data () {
    return {
      localValue: Number(this.getRawValue())
    }
  },

  computed: {
    min () {
      const { min } = this.question.writeRenderer
      return min
    },

    max () {
      const { max } = this.question.writeRenderer
      return max
    },

    step () {
      const { step = 1 } = this.question.writeRenderer
      return step
    }
  },

  methods: {
    commitValue () {
      this.setRawValue(this.localValue)
    },

    decrement () {
      this.localValue = Number(this.localValue) - this.step
      this.commitValue()
    },

    increment () {
      this.localValue = Number(this.localValue) + this.step
      this.commitValue()
    },

    reset () {
      this.localValue = 0
      this.setRawValue(null)
    },

    valueFormat (value) {
      return new Intl.NumberFormat(this.$i18n.locale, { maximumFractionDigits: 2 }).format(value)
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

.answer input {
  appearance: none;
  margin: 0;
  border-radius: none;
  width: 110px;
  padding: 16px;
  background-color: var(--color-n-000);
  border-top: 1px solid var(--color-n-200);
  border-bottom: 1px solid var(--color-n-200);
  border-left: none;
  border-right: none;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
  font-size: 16px;
  color: var(--color-n-600);
  box-sizing: border-box;
}

.btn {
  @extend %button-reset;
  padding: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
  color: var(--color-n-000);
  background-color: var(--color-p-500);
  font-size: 14px;

  &:hover {
    background-color: var(--color-p-700);
  }

  &:active {
    box-shadow: none;
    transform: translate3d(0, 1px, 0);
  }

  &.decrement {
    border-radius: 4px 0 0 4px;
  }

  &.increment {
    border-radius: 0 4px 4px 0;
  }
}

.user-answer {
  min-width: 50px;
  padding: 16px;
  background-color: var(--color-n-000);
  border: 1px solid var(--color-n-200);
  border-radius: 4px;
  font-size: 16px;
  color: var(--color-n-600);
  text-align: center;
}
</style>
