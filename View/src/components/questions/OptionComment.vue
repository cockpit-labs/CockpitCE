<template>
  <div v-if="readOnly" class="read-only">
    <div class="user-comment">{{ localValue }}</div>
  </div>

  <div v-else class="option-comment">
    <div class="comment">
      <textarea
        ref="textarea"
        v-model="localValue"
        :placeholder="placeholder"
        :maxlength="maxLength"
        @blur="commitText"
        @focus="showCharCounter"
      ></textarea>
      <transition>
        <div class="char-counter" v-show="charCounterVisible">
          {{ maxLength - localValue.length }}
        </div>
      </transition>
    </div>
    <div class="close" @click="reset">
      <SIcon name="times" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'OptionComment',

  props: {
    value: String,
    maxLength: {
      type: [Number, String],
      default: 250
    },
    placeholder: String,
    readOnly: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      localValue: this.value || '',
      charCounterVisible: false
    }
  },

  methods: {
    commitText () {
      this.$emit('input', this.localValue)
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
      this.$emit('input', null)
      this.$emit('close')
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
.option-comment {
  display: grid;
  grid-template-columns: 1fr 46px;

  &:hover .comment,
  &:focus-within .comment {
   border-color: var(--color-n-200);
  }

  &:hover .close,
  &:focus-within .close {
    background-color: var(--color-n-200);
    border: 1px solid var(--color-n-200);
    color: var(--color-n-500);

    &:hover {
      background-color: var(--color-s-5-light);
      border-color: var(--color-s-5-light);
      color: var(--color-s-5-dark);
    }
  }
}

.comment {
  position: relative;
  margin-right: -21px;
  border-radius: 12px;
  background-color: var(--color-n-050);
  border: 1px solid var(--color-n-050);
  transition: all 150ms ease-in-out;
}

.close {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  background-color: var(--color-n-050);
  border: 1px solid var(--color-n-050);
  color: var(--color-n-400);
  border-radius: 0 12px 12px 0;
  transition: all 150ms ease-in-out;

  .s-icon {
    margin-right: 8px;
  }
}

.option-comment textarea {
  @include font-stack;
  appearance: none;
  outline: none;
  width: 100%;
  padding: 8px 8px 16px;
  background-color: var(--color-n-050);
  border: none;
  border-radius: 12px;
  box-shadow: none;
  font-size: 14px;
  color: var(--color-n-600);
  resize: none;
  overflow: hidden;
  box-sizing: border-box;
}

.char-counter {
  position: absolute;
  right: 8px;
  bottom: 0;
  font-size: 14px;
}

.v-enter, .v-leave-to {
  opacity: 0;
  transform: translateY(4px);
}

.v-enter-active, .v-leave-active {
  transition: all 250ms ease-in-out;
}

.user-comment {
  font-size: 14px;
  color: var(--color-n-600);
  background-color: var(--color-n-050);
  padding: 8px 8px 16px;
  border-radius: 12px;
}
</style>
