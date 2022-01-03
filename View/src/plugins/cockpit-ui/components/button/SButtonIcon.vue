<template>
  <button
    class="s-button-icon"
    :class="color"
    :title="label"
    :aria-label="label"
    @click="e => $emit('click', e)"
  >
      <SIcon :name="icon" fw />
  </button>
</template>

<script>
import SIcon from '../icon/SIcon'

export default {
  name: 'SButtonIcon',

  components: {
    SIcon
  },

  props: {
    icon: String,
    label: {
      required: true,
      type: String
    },
    color: {
      type: String,
      default: 'primary',
      validator: value => {
        return ['primary', 'inverted', 'neutral', 'light-grey'].indexOf(value) !== -1
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.s-button-icon {
  @extend %button-reset;

  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: var(--color-p-500);
  color: var(--color-n-000);
  transition: background-color 300ms ease-in-out,
              transform 150ms ease-in;

  &:hover {
      background-color: var(--color-p-400);
  }

  &:active {
      transform: scale3d(0.9, 0.9, 1);
  }

  .s-icon {
      font-size: 16px;
  }
}

.s-button-icon.inverted {
  background-color: var(--color-p-050);
  color: var(--color-p-700);

  &:hover {
      background-color: var(--color-p-100);
      color: var(--color-p-500);
  }
}

.s-button-icon.neutral {
  background-color: var(--color-n-400);
  color: var(--color-n-050);

  &:hover {
      background-color: var(--color-n-500);
  }
}

.s-button-icon.light-grey {
  background-color: var(--color-n-100);
  color: var(--color-n-600);

  &:hover {
      background-color: var(--color-n-200);
      color: var(--color-n-700);
  }
}
</style>
