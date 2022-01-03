<template>
  <div class="card-mini">
    <div class="context" v-if="$slots.context">
      <slot name="context" />
    </div>
    <div :class="['card', {'with-context': $slots.context}]">
      <div class="content">
        <div class="title"><slot name="title" /></div>
        <div class="subtitle" v-if="$slots.subtitle"><slot name="subtitle" /></div>
      </div>
      <SButtonIcon class="btn" icon="angle-right" label="Continuer" @click="emitClick" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'CardMini',

  methods: {
    emitClick (event) {
      this.$emit('click', event)
    }
  }
}
</script>

<style lang="scss" scoped>
.card-mini {
    @include font-stack;
    display: grid;
    max-width: 670px;
    cursor: default;
    transition: all 200ms ease-in-out;
    box-sizing: border-box;
}

.context {
  grid-column: 1;
  grid-row: 1;
  padding: 4px 16px;
  font-size: 14px;
  background-color: var(--color-n-200);
  color: var(--color-n-700);
  border-radius: 12px;
}

.card {
  grid-column: 1;
  grid-row: 1;
  display: grid;
  grid-template-columns: 1fr min-content;
  padding: 20px 16px;
  background-color: var(--color-n-000);
  border-radius: 12px;
  box-shadow: 0 0px 4px rgba($color: #000000, $alpha: 0.12);

  &.with-context {
    margin-top: 24px;
  }
}

.content {
  grid-column: 1;
  align-self: center;
  overflow: hidden;
  padding-right: 24px;
}

.title {
  font-size: 16px;
  font-weight: $fw-bold;
  color: var(--color-n-600);
  // one line
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.subtitle {
  margin-top: 2px;
  font-size: 14px;
  font-weight: $fw-regular;
  color: var(--color-n-500);
  // one line
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.btn {
  grid-column: 2;
  align-self: center;
}
</style>
