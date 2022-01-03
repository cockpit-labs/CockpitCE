<template>
  <transition name="overlay" @after-enter="showDialog">
    <div v-if="opened" :class="['s-alert', 'overlay', type]">
      <transition name="dialog" @after-leave="hideOverlay">
        <div class="dialog" v-show="dialogVisible">
          <div class="picto">
            <SIcon :name="typeIcon" />
          </div>
          <div class="header">
            <slot name="title"></slot>
          </div>
          <div class="body">
            <slot name="content"></slot>
          </div>
          <div class="footer">
            <slot name="actions">
              <SButton @click="close">{{ $t('ok') }}</SButton>
            </slot>
          </div>
        </div>
      </transition>
    </div>
  </transition>
</template>

<script>
import SIcon from '../icon/SIcon'
import SButton from '../button/SButton'

export default {
  name: 'SAlert',

  components: {
    SIcon,
    SButton
  },

  props: {
    type: {
      type: String,
      default: 'alert',
      validator: function (value) {
        return ['alert', 'info', 'error'].indexOf(value) !== -1
      }
    }
  },

  data () {
    return {
      opened: false,
      dialogVisible: false
    }
  },

  computed: {
    typeIcon () {
      const icons = {
        alert: 'bell',
        info: 'info',
        error: 'exclamation'
      }
      return icons[this.type]
    }
  },

  methods: {
    open () {
      this.opened = true
    },

    close () {
      this.dialogVisible = false
    },

    showDialog () {
      this.dialogVisible = true
    },

    hideOverlay () {
      this.opened = false
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
  display: flex;
  align-items: center;
  justify-content: center;
}

.overlay-enter-active, .overlay-leave-active {
  transition: all .3s ease;
}
.overlay-enter, .overlay-leave-to {
  opacity: 0;
}

.dialog {
  position: relative;
  min-width: 250px;
  max-width: 350px;
  margin: 16px;
  padding: 24px 24px 24px 48px;
  background-color: var(--color-n-000);
  border-radius: 8px;
}

.dialog-enter-active {
  transition: all .3s cubic-bezier(0.175, 0.885, 0.320, 1.275);
}
.dialog-enter {
  transform: scale(0.6);
  opacity: 0.2;
}

.dialog-leave-active {
  transition: all 200ms ease-out;
}

.dialog-leave-to {
  opacity: 0.5;
  transform: scale(0.8);
}

.picto {
  position: absolute;
  width: 48px;
  height: 48px;
  left: 0;
  top: 0;
  transform: translate(-24%, -24%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 6px 1px rgba(0,0,0,0.1);

  .s-icon {
    font-size: 24px;
  }
}

.s-alert.error {
  .picto {
    color: var(--color-s-5-dark);
    background-color: var(--color-s-5-light);
  }

  .header {
    color: var(--color-s-5);
  }
}

.s-alert.info {
  .picto {
    color: var(--color-p-800);
    background-color: var(--color-p-100);
  }

  .header {
    color: var(--color-p-500);
  }
}

.s-alert.alert {
  .picto {
    color: var(--color-s-1-dark);
    background-color: var(--color-s-1-light);
  }

  .header {
    color: var(--color-s-1);
  }
}

.header {
  font-size: 18px;
  font-weight: $fw-semibold;
}

.body {
  margin: 16px 0 40px;
}

.footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;

  &::v-deep .s-button {
    box-shadow: none;
    background-color: var(--color-n-500);

    &:hover {
      background-color: var(--color-n-700);
    }
  }
}
</style>
