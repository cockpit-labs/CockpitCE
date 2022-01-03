<template>
  <div class="page-not-found">
    <div class="title">{{ $t('pageNotFound.title') }}</div>
    <div class="subtitle">{{ $t('pageNotFound.subtitle') }}</div>
    <SButton @click="goToHome">{{ $t('pageNotFound.goToHome') }}</SButton>
  </div>
</template>

<script>
export default {
  methods: {
    goToHome () {
      this.$router.push({ name: 'home' })
    }
  }
}
</script>

<style lang="scss" scoped>
.page-not-found {
  height: 100vh;
  width: 100vw;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.title {
  font-size: 32px;
}

.subtitle {
  margin: 24px 0 48px;
}
</style>
