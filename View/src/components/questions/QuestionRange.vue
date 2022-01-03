<template>
  <div :class="['answer', {'read-only': readOnly}]">
    <div class="range-wrap">
      <output class="bubble" :style="{'left': bubblePosition}">{{ localValue }}</output>
      <input
        type="range"
        class="range"
        :min="min"
        :max="max"
        :step="step"
        v-model="localValue"
        :disabled="readOnly"
        @input="commitValue"
      >
    </div>
    <div class="limits">
      <div class="min">{{ min }}</div>
      <div class="max">{{ max }}</div>
    </div>
  </div>
</template>

<script>
import questionUtils from '@/mixins/questions'

export default {
  name: 'QuestionRange',

  mixins: [questionUtils],

  data () {
    return {
      localValue: Number(this.getRawValue())
    }
  },

  computed: {
    min () {
      const { min = 0 } = this.question.writeRenderer
      return min
    },

    max () {
      const { max = 100 } = this.question.writeRenderer
      return max
    },

    step () {
      const { step = 1 } = this.question.writeRenderer
      return step
    },

    bubblePosition () {
      const local = Number(this.localValue) < this.min ? this.min : Number(this.localValue)
      const newVal = Number((local - this.min) / (this.max - this.min))
      return 'calc(' + newVal * 100 + '% + (' + (12 - (24 * newVal)) + 'px))'
      // 24 = width of input::-webkit-slider-thumb
    }
  },

  methods: {
    commitValue () {
      this.setRawValue(this.localValue)
    },

    reset () {
      this.localValue = 0
      this.setRawValue(null)
    }
  }
}
</script>

<style lang="scss" scoped>
.answer {
  @include font-stack;
  padding: 32px 16px 0 16px;
}

.range-wrap {
  position: relative;
}

.answer input {
  appearance: none;
  margin: 16px 0;
  width: 100%;
  height: 6px;
  background: var(--color-n-200);
  border-radius: 16px;
  outline: none;

  &:focus::-webkit-slider-thumb {
    border: none;
    box-shadow: 0 0 0 3px var(--color-n-000), 0 0 0 6px var(--color-p-400) ;
  }
}

.answer input::-webkit-slider-thumb {
  appearance: none;
  height: 24px;
  width: 24px;
  border-radius: 50%;
  background: var(--color-p-800);
  // border: 3px solid var(--color-p-400);
  box-shadow: 0 0 4px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: all 200ms ease-in-out;

  &:hover {
    background-color: var(--color-p-400);
    // border-color: var(--color-p-200);
  }
}

.read-only input::-webkit-slider-thumb {
  cursor: not-allowed;
  background: var(--color-n-600);

  &:hover {
    background-color: var(--color-n-600);
  }
}

.answer input::-moz-range-thumb {
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: var(--color-n-000);
  border: 1px solid var(--color-n-200);
  box-shadow: 0 0 4px rgba(0, 0, 0, 0.1);
  cursor: pointer;
}

.read-only input::-moz-range-thumb {
  cursor: not-allowed;
  background: var(--color-n-600);

  &:hover {
    background-color: var(--color-n-600);
  }
}

.limits {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
}

.bubble {
  position: absolute;
  top: -34px;
  left: 50%;
  transform: translateX(-50%);
  background-color: var(--color-p-400);
  color: var(--color-n-000);
  padding: 4px 8px;
  border-radius: 4px;

  .read-only & {
    background-color: var(--color-n-500);
  }
}

.bubble::before {
  content: "";
  position: absolute;
  width: 0;
  height: 0;
  border-top: 5px solid var(--color-p-400);
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  top: 100%;
  left: 50%;
  margin-left: -5px;
  margin-top: -1px;

  .read-only & {
    border-top-color: var(--color-n-500);
  }
}
</style>
