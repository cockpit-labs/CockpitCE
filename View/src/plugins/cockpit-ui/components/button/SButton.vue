<template>
  <button class="s-button" :class="{rounded, ghost}" :disabled="disabled" @click="e => $emit('click', e)">
    <SIcon v-if="icon && (iconPosition === 'left')" :name="icon" class="left" />
    <slot />
    <SIcon v-if="icon && (iconPosition === 'right')" :name="icon" class="right" />
  </button>
</template>

<script>
import SIcon from '../icon/SIcon'

export default {
  name: 'SButton',

  components: {
    SIcon
  },

  props: {
    icon: String,
    rounded: {
      type: Boolean,
      default: false
    },
    iconPosition: {
      type: String,
      default: 'right'
    },
    disabled: Boolean,
    ghost: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.s-button {
  @extend %button-reset;
  @include font-stack;

  display: flex;
  align-items: center;
  padding: 12px 24px;
  border-radius: 4px;
  box-shadow: 0 6px 8px rgba(0,0,0,0.10);
  color: var(--color-n-000);
  background-color: var(--color-p-500);
  font-size: 16px;
  font-weight: $fw-semibold;
  line-height: 1.3;
  white-space: nowrap;
  transition: all 150ms ease-in;

  &:hover {
    background-color: var(--color-p-700);
  }

  &:active {
    box-shadow: none;
    transform: translate3d(0, 1px, 0);
  }
}

.s-button.ghost {
  box-shadow: none;
  background-color: transparent;
  color: var(--color-n-500);
  border: 1px solid var(--color-n-400);

  &:hover {
    color: var(--color-n-700);
    box-shadow: 0 3px 4px rgba(0,0,0,0.10);
    background-color: var(--color-n-000);
  }

  &:disabled {
    background-color: var(--color-n-100);
  }
}

.s-button:disabled {
  background-color: var(--color-n-050);
  color: var(--color-n-200);
  pointer-events: none;
}

.s-button.rounded {
  font-size: 14px;
  line-height: 1.1;
  padding: 8px 16px;
  border-radius: 20px;
  box-shadow: none;

  &:active {
    transform: translate3d(0, 1px, 0);
  }
}

.s-button .s-icon.right {
  padding-left: 12px;
}

.s-button .s-icon.left {
  padding-right: 12px;
}
</style>
