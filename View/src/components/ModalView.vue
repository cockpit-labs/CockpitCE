<template>
  <portal to="modal-view">
    <transition name="overlay" @after-enter="openModalView">
      <div class="overlay" v-if="modalOpened" @click.self="close">
        <transition name="modal" @leave="closeCompletly">
          <div class="modal-view" v-show="modalVisible">

            <div class="header">
              <div class="title" v-if="$slots.title">
                <slot name="title" />
              </div>
              <div class="subtitle" v-if="$slots.subtitle">
                <slot name="subtitle" />
              </div>
              <SButtonIcon
                class="close"
                icon="times"
                :label="$t('close')"
                color="neutral"
                @click="close"
              />
            </div>

            <div class="main" ref="mainContainer">
              <div v-if="contentLoading" class="loading">{{ $t('loading') }}</div>
              <slot v-else />
            </div>

            <div class="footer" v-if="$slots.footer">
              <slot name="footer" />
            </div>

          </div>
        </transition>

        <GlobalEvents
          v-if="modalOpened"
          @keyup.esc="close"
        />
      </div>
    </transition>
  </portal>
</template>

<script>
export default {
  props: {
    contentLoading: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      modalVisible: false,
      modalOpened: false
    }
  },

  methods: {
    open () {
      this.modalOpened = true
    },

    close () {
      this.modalVisible = false
    },

    openModalView () {
      this.modalVisible = true
    },

    closeCompletly () {
      this.modalOpened = false
    },

    scrollMainTop () {
      this.$refs.mainContainer.scrollTop = 0
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
  background-color: rgba($color: #000000, $alpha: 0.6);
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-view {
  position: relative;
  align-self: flex-end;
  width: 100vw;
  max-height: 100vh;
  margin-bottom: -5px;
  box-sizing: border-box;
  background-color: var(--color-n-050);
  border-radius: 24px 24px 0 0;
  box-shadow: 0 -2px 12px rgba($color: #000000, $alpha: 0.12);
  overflow: hidden;
  display: grid;
  grid-template-columns: 1fr;
  grid-template-rows: max-content 1fr max-content;
}

.header {
  background-color: var(--color-n-050);
  display: grid;
  grid-template-columns: 1fr max-content;
  grid-template-areas: "title close"
                       "subtitle close";
  padding: 24px 24px 16px 24px;
}

.title {
  grid-area: title;
  font-size: 24px;
  font-weight: $fw-bold;
  color: var(--color-n-700);
}

.subtitle {
  grid-area: subtitle;
  padding-top: 4px;
}

.close {
  grid-area: close;
}

.main {
  overflow: auto;
  margin-bottom: 40px;
  padding: 32px 24px;
}

.footer {
  background-color: var(--color-n-050);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 0 24px 40px 24px;
}

.loading {
  text-align: center;
  height: 80px;
}

.overlay-enter-active, .overlay-leave-active {
  transition: all .3s ease;
}
.overlay-enter, .overlay-leave-to {
  opacity: 0;
}

.modal-enter-active {
  transition: all .3s cubic-bezier(0.175, 0.885, 0.320, 1.275);
}
.modal-enter {
  transform: translateY(50px)
}

.modal-leave-active {
  transition: opacity 200ms ease-out;
}

.modal-leave-to {
  transform: translateY(20px);
  opacity: 0.3;
}

.desktop {
  .modal-view {
    align-self:unset;
    max-height: 80%;
    max-width: 80%;
    min-width: 600px;
    width: min-content;
    border-radius: 16px;
  }
}
</style>
