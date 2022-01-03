<template>
  <div :class="['main-nav', {sticky}]">
    <img class="logo" src="@/assets/logo.svg" alt="Logo">
    <div class="title-select" v-if="currentRoute" @click.stop="openMenu">
      <span class="title">{{ currentRoute.label }}</span>
      <SIcon name="caret-down" />
    </div>
    <div class="main-nav-menu">
      <!-- Use @click.stop to stop the event bubbling that closes the menu immediately after it opens -->
      <div class="menu" v-if="opened" v-click-outside="closeMenu">
        <div class="nav">
          <!-- <div class="title">Name</div> -->
          <router-link v-for="item in mainMenu" :key="item.label" :to="item.url" @click.native="closeMenu">{{ item.label }}</router-link>
          <div class="user-menu-seperator">{{ $keycloak.fullName }}</div>
          <router-link v-for="item in userMenu" :key="item.label" :to="item.url">{{ item.label }}</router-link>
        </div>
        <SButtonIcon icon="times" class="close" :label="$t('mainMenu.closeMenu')" color="neutral" @click="closeMenu" />
      </div>
    </div>
  </div>
</template>

<script>
import clickOutside from '@/plugins/cockpit-ui/directives/click-outside'

export default {
  name: 'MainNav',

  props: {
    sticky: {
      type: Boolean,
      default: false
    }
  },

  directives: {
    clickOutside
  },

  data () {
    return {
      opened: false
    }
  },

  computed: {
    mainMenu () {
      const menu = [
        { label: this.$t('mainMenu.home'), url: '/' },
        { label: this.$t('mainMenu.questionnaires'), url: '/questionnaires' },
        { label: this.$t('mainMenu.answers'), url: '/answers' },
        { label: this.$t('mainMenu.dashboard'), url: '/dashboard' },
        { label: this.$t('mainMenu.gallery'), url: '/gallery' }
      ]

      if (this.$store.state.showTargetData) {
        menu.splice(2, 0, { label: this.$t('mainMenu.targetData'), url: '/target-data' })
      }

      return menu
    },

    userMenu () {
      return [
        { label: this.$t('mainMenu.logout'), url: '/logout' }
      ]
    },

    currentRoute () {
      return this.mainMenu.find(item => item.url === this.$route.path)
    }
  },

  methods: {
    openMenu () {
      this.opened = true
    },

    closeMenu () {
      this.opened = false
    }
  }
}
</script>

<style lang="scss" scoped>
.main-nav {
  position: sticky;
  top: 0;
  z-index: 10;
  display: flex;
  align-items: center;
  padding: 24px 16px 16px;
  background-color: var(--color-n-100);
  transition: all 200ms cubic-bezier(0, 0, 0.2, 1);

  &.sticky {
    box-shadow: 0 8px 16px rgba($color: #000000, $alpha: 0.12);
  }

  // &.sticky .title-select {
  //   background-color: var(--color-n-100);
  // }

  .logo {
    height: 40px;
  }

  .title-select {
    margin-left: auto;
    padding: 4px 16px 4px 18px;
    background-color: var(--color-n-200);
    color: var(--color-n-700);
    border-radius: 20px;
    font-size: 18px;
    transition: all 200ms ease-out;

    @media (hover: hover) and (pointer: fine) {
      cursor: default;

      &:hover {
        background-color: var(--color-n-300);
        color: var(--color-n-900);
      }
    }
  }

  .title {
    font-size: 20px;
    font-weight: $fw-semibold;
    padding-right: 16px;
  }
}

.main-nav-menu {
  position: relative;

  .menu {
    position: absolute;
    right: -4px;
    top: -28px;
    padding: 12px;
    display: flex;
    background-color: var(--color-n-000);
    box-shadow: -2px 2px 28px 2px rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    z-index: 100;
  }

  .menu .close {
    align-self: flex-start;
  }

  .menu .nav {
    padding: 24px 16px 0 8px;
    width: max-content;
    min-width: 180px;
    max-width: 220px;
  }

  .menu a {
    display: block;
    font-size: 24px;
    padding: 8px 0 8px;
    text-decoration: none;
    color: var(--color-n-600);

    &:hover {
      color: var(--color-n-800);
    }
  }

  .user-menu-seperator {
    margin-top: 12px;
    padding-left: 2px;
    font-variant-caps: all-small-caps;
    color: var(--color-p-500);
    font-size: 18px;
  }
}

.desktop {
  .main-nav {
    padding: 16px;

    .logo {
      height: 48px;
    }

    .title-select {
      margin-left: 32px;
    }
  }

  .menu .nav {
    padding-top: 8px;
    min-width: 160px;
  }

  .menu a {
    font-size: 18px;
  }

  .user-menu-seperator {
    font-size: 16px;
  }
}
</style>
