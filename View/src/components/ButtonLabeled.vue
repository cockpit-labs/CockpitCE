<template>
  <button class="button-labeled" :class="color" @click="e => $emit('click', e)">
    <slot />
    <SIcon v-if="icon" :name="icon" class="right" />
  </button>
</template>

<script>
export default {
  name: 'ButtonLabeled',

  props: {
    icon: String,
    color: String
  }
}
</script>

<style lang="scss" scoped>
.button-labeled {
  @extend %button-reset;
  @include font-stack;

  display: flex;
  align-items: center;
  padding: 12px 24px;
  border-radius: 20px;
  box-shadow: 0 3px 4px rgba(0,0,0,0.10);
  color: var(--color-n-000);
  background-color: var(--color-p-500);
  font-size: 14px;
  font-weight: $fw-semibold;
  line-height: 1.1;
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

.button-labeled .s-icon.right {
  padding-left: 16px;
}

.button-labeled.yellow {
  color: var( --color-s-1-dark);
  background-color: var( --color-s-1-light);

  &:hover {
    background-color: var(--color-s-1);
  }
}

.button-labeled.indigo {
  color: var( --color-n-000);
  background-color: var( --color-s-4);

  &:hover {
    background-color: var(--color-s-4-dark);
  }
}
</style>
