<template>
  <div class="s-fullscreen-loader">
    <div class="content">
      <SIcon name="circle-notch" spin />
      <div class="text"><slot /></div>
    </div>
  </div>
</template>

<script>
import SIcon from '../icon/SIcon'

export default {
  name: 'SFullscreenLoader',

  components: {
    SIcon
  }
}
</script>

<style scoped>
.s-fullscreen-loader {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 100;
  background-color: rgba(0, 0, 0, 0.60);
  display: flex;
  align-items: center;
  justify-content: center;
}

.content {
  padding: 16px;
  background-color: var(--color-n-000);
  border-radius: 4px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.s-icon {
  font-size: 24px;
}

.text {
  font-size: 18px;
  margin-top: 8px;
}
</style>
