<template>
  <button class="s-button-text" :disabled="disabled" @click="e => $emit('click', e)">
    <SIcon v-if="icon" :name="icon" fw />
    <slot />
  </button>
</template>

<script>
import SIcon from '../icon/SIcon'

export default {
  name: 'SButtonText',

  components: {
    SIcon
  },

  props: {
    icon: String,
    disabled: Boolean
  }
}
</script>

<style lang="scss" scoped>
.s-button-text {
  @extend %button-reset;
  @include font-stack;

  display: flex;
  align-items: center;
  padding: 4px;
  font-size: 16px;
  color: var(--color-n-500);
  white-space: nowrap;
  cursor: default;
  transition: color 150ms ease-in,
              transform 150ms ease-in;

  &:hover {
      color: var(--color-n-700);
  }

  &:active {
      transform: scale3d(0.99, 0.99, 1);
  }

  .s-icon {
      font-size: 16px;
      margin-right: 8px;
  }
}
</style>
