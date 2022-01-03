<template>
  <div class="answer">
    <div v-if="readOnly" class="read-only">
      <div class="user-answer" v-if="localValue">{{ localValue }}</div>
      <div class="no-data" v-else>{{ $t('question.noAnswer') }}</div>
    </div>

    <div v-else class="textarea-and-counter">
      <textarea
        ref="textarea"
        v-model="localValue"
        :placeholder="placeholder"
        :maxlength="maxLength"
        @blur="commitText"
        @focus="showCharCounter"
      ></textarea>
      <transition>
        <div
          class="char-counter"
          v-show="charCounterVisible"
        >
          {{ maxLength - localValue.length }}
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
import questionUtils from '@/mixins/questions'

export default {
  name: 'QuestionText',

  mixins: [questionUtils],

  data () {
    return {
      localValue: this.getRawValue() || '',
      charCounterVisible: false
    }
  },

  computed: {
    maxLength () {
      const { maxLength = 300 } = this.question.writeRenderer
      return maxLength
    },
    placeholder () {
      const { placeholder } = this.question.writeRenderer
      return placeholder
    }
  },

  methods: {
    commitText () {
      this.setRawValue(this.localValue || null)
      this.charCounterVisible = false
    },

    resizeTextarea (event) {
      event.target.style.height = 'auto'
      event.target.style.height = (event.target.scrollHeight) + 'px'
    },

    showCharCounter () {
      this.charCounterVisible = true
    },

    reset () {
      this.localValue = ''
      this.commitText()
    }
  },

  mounted () {
    if (!this.readOnly) {
      this.$nextTick(() => {
        this.$refs.textarea.setAttribute('style', 'height:' + (this.$refs.textarea.scrollHeight) + 'px;overflow-y:hidden;')
      })

      this.$refs.textarea.addEventListener('input', this.resizeTextarea)
    }
  },

  beforeDestroy () {
    if (!this.readOnly) {
      this.$refs.textarea.removeEventListener('input', this.resizeTextarea)
    }
  }
}
</script>

<style lang="scss" scoped>
.answer{
  max-width: 500px;
  margin: 0 auto;
  padding-bottom: 4px;
}

.textarea-and-counter {
  position: relative;
}

.answer textarea {
  @include font-stack;
  appearance: none;
  width: 100%;
  padding: 16px;
  background-color: var(--color-n-000);
  border: 1px solid var(--color-n-200);
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
  font-size: 16px;
  color: var(--color-n-600);
  resize: none;
  overflow: hidden;
  box-sizing: border-box;
}

.char-counter {
  position: absolute;
  right: 2px;
  font-size: 14px;
}

.v-enter, .v-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.v-enter-active, .v-leave-active {
  transition: all 250ms ease-in-out;
}

.user-answer {
  padding: 16px;
  background-color: var(--color-n-000);
  border: 1px solid var(--color-n-200);
  border-radius: 4px;
  font-size: 16px;
  color: var(--color-n-600);
}
</style>
