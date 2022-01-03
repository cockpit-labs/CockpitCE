<template>
  <div class="photo-viewer overlay" v-if="modalOpened">
    <div class="header">
      <SButtonIcon
        class="close"
        icon="times"
        :label="$t('close')"
        color="neutral"
        @click="close"
      />
    </div>

    <img class="photo" :src="photoSrc" />

    <div class="footer">
      <slot name="footer" />
    </div>

    <GlobalEvents
      v-if="modalOpened"
      @keyup.esc="close"
    />
  </div>
</template>

<script>
export default {
  name: 'PhotoViewer',

  props: {
    photoSrc: {
      type: String
    }
  },

  data () {
    return {
      modalOpened: false
    }
  },

  methods: {
    open () {
      this.modalOpened = true
    },
    close () {
      this.modalOpened = false
    }
  }
}
</script>

<style lang="scss" scoped>
.overlay {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  background-color: rgba($color: #000000, $alpha: 0.8);
  backdrop-filter: blur(4px);
  z-index: 10;
  display: grid;
  grid-template-columns: 100vw;
  grid-template-rows: max-content auto max-content;
}

.header {
  padding: 24px 16px;
}

.close {
  margin-left: auto;
}

.photo {
  max-width: 100%;
  max-height: 100%;
  min-width: 0;
  min-height: 0;
  align-self: center;
  justify-self: center;
}

.footer {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px 16px;
}

.footer .s-button:first-child {
  margin-bottom: 16px;
}
</style>
