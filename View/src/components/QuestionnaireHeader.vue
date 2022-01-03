<template>
  <div class="questionnaire-header">
    <svg id="pageHeaderShape" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 414 94.324" preserveAspectRatio="none">
      <path d="M0 0h414v87.875s-103.5 6.449-207 6.449S0 87.875 0 87.875z" />
    </svg>

    <div class="action-left">
      <slot name="action-left" />
    </div>
    <div class="title">
        <slot />
    </div>
    <div class="actions-right">
      <slot name="action-right" />
    </div>
  </div>
</template>

<style lang="scss">
.questionnaire-header {
  position: relative;
  width: 100vw;
  padding: 44px 16px 0;
  display: grid;
  grid-template-columns: 40px 1fr 40px;
  align-items: center;
  z-index: 5;
  box-sizing: border-box;
}

#pageHeaderShape {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height:106px;
  filter: drop-shadow(0 8px 12px rgba(0, 0, 0, .16));
  z-index: -1;

  path {
    fill: var(--color-n-700);
  }
}

.questionnaire-header .title {
  text-align: center;
  color: var(--color-n-100);
  font-weight: $fw-semibold;
  text-transform: uppercase;
}

.desktop {
  .questionnaire-header {
    max-height: 94px;
    padding: 44px 58px 0;
  }
}
</style>
