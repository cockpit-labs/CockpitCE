<template>
  <div :class="['s-select', {disabled}]">
    <div class="selected-option" @click.stop="open">
      <span class="label">{{ selectedLabel }}</span>
      <SIcon v-if="loading" name="circle-notch" spin />
      <SIcon v-else name="chevron-down" />
    </div>
    <div class="options" v-if="opened" v-click-outside="close">
      <div class="selected-option" @click="close">
        <span class="label">{{ selectedLabel }}</span>
        <SIcon name="times" />
      </div>
      <div class="list">
        <div
          v-for="option in options"
          :key="option.id"
          :class="['option', {selected: isSelected(option.id)}]"
          @click="selectOption(option.id)"
        >
          {{ option.label }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SIcon from '../icon/SIcon'
import clickOutside from '../../directives/click-outside'

export default {
  name: 'SSelect',

  components: {
    SIcon
  },

  directives: {
    clickOutside
  },

  props: {
    value: [Array],
    options: {
      type: Array
    },
    multiselect: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    placeholder: {
      type: String
    },
    loading: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      opened: false
    }
  },

  computed: {
    selectedLabel () {
      if (this.multiselect) {
        let value = this.value

        if (!Array.isArray(value)) {
          value = value ? [value] : []
        }

        if (value.length > 0) {
          return this.$tc('placeholder.multipleAnswersSelected', value.length)
        } else {
          return this.$t('placeholder.selectMultipleAnswers')
        }
      } else {
        if (this.value && this.value.length > 0) {
          const selectedOption = this.options.find(option => option.id === this.value[0])
          return selectedOption.label
        } else {
          return this.placeholder
        }
      }
    }
  },

  methods: {
    open () {
      this.opened = true
    },

    close () {
      this.opened = false
    },

    selectOption (id) {
      let val = this.value
      if (this.multiselect) {
        if (!Array.isArray(val)) {
          val = val ? [val] : []
        }
        const index = val.indexOf(id)
        if (index !== -1) {
          this.$delete(val, index)
        } else {
          val.push(id)
        }
        this.$emit('input', val)
      } else {
        this.$emit('input', [id])
        this.close()
      }
    },

    isSelected (id) {
      if (Array.isArray(this.value)) {
        return this.value.indexOf(id) !== -1
      } else {
        return this.value === id
      }
    },

    reset () {
      this.$emit('input', null)
    }
  },

  watch: {
    options: function (newOptions) {
      if (this.value === null) {
        return
      }

      if (Array.isArray(this.value)) {
        const valuesPresent = this.value.filter(value => newOptions.find(option => option.id === value))

        if (valuesPresent.length < this.value) {
          this.$emit('input', valuesPresent)
        }

        if (valuesPresent.length === 0) {
          this.reset()
        }
      } else {
        const valuePresent = newOptions.find(option => option.id === this.value)

        if (!valuePresent) {
          this.reset()
        }
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.s-select {
  @include font-stack;
  position: relative;
  max-width: 400px;
  border-radius: 8px;
  box-shadow: 0 3px 4px rgba($color: #000000, $alpha: 0.12);

  &.disabled {
    pointer-events: none;
    box-shadow: none;
  }

  &.disabled .selected-option {
    color: var(--color-n-500);
    background-color: var(--color-n-200);
    cursor: not-allowed;
  }
}

.selected-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  border-radius: 8px;
  color: var(--color-n-800);
  font-weight: $fw-semibold;
  background-color: var(--color-n-000);
  cursor: default;
  transition: all 150ms ease-in;

  &:hover {
    color: var(--color-p-900);
    background-color: var(--color-p-100);
  }

  .s-icon {
    font-size: 16px;
    margin-left: 16px;
  }
}

.options {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  border-radius: 8px;
  background-color: var(--color-n-000);
  box-shadow: -2px 2px 28px 2px rgba(0, 0, 0, 0.2);
  z-index: 11;
}

.options .selected-option {
  border-radius: 8px 8px 0 0;
}

.list {
  padding: 8px 16px 16px;
  max-height: 300px;
  overflow-y: auto;
}

.option {
  font-weight: $fw-regular;
  padding: 8px;
  margin: 2px 0;
  border-radius: 4px;
  color: var(--color-p-500);
  cursor: default;
  transition: transform 150ms ease-in;

  &:hover {
    color: var(--color-p-900);
    background-color: var(--color-p-100);
  }

  &:active {
    transform: scale3d(0.99, 0.99, 1);
  }

  &.selected {
    background-color: var(--color-p-800);
    color: var(--color-p-050);
  }
}
</style>
