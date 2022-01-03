<template>
  <div class="template-main">
    <svg id="header-shape" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 802.84 376" preserveAspectRatio="none">
      <path d="M0 0h784.273s23.75 66.642 17.528 134.912-174.474 151.067-174.474 151.067S430.523 376 288.187 376 0 313.427 0 313.427z"/>
    </svg>
    <div class="container">
      <MainNav :sticky="mainNavIsSticky" />
      <div class="filters">
        <div class="tracker" ref="intersectionTracker"></div>
        <portal-target name="filters"></portal-target>
      </div>
      <router-view />
    </div>
  </div>
</template>

<script>
import MainNav from '@/components/MainNav'

export default {
  name: 'template-main',

  components: {
    MainNav
  },

  data () {
    return {
      observer: null,
      mainNavIsSticky: false
    }
  },

  mounted () {
    this.observer = new IntersectionObserver(entries => {
      this.mainNavIsSticky = !entries[0].isIntersecting
    },
    {
      rootMargin: '-70px 0px 0px 0px',
      threshold: 0
    })

    this.observer.observe(this.$refs.intersectionTracker)
  },

  destroyed () {
    if (this.observer) {
      this.observer.disconnect()
    }
  }
}
</script>

<style lang="scss" scoped>
#header-shape {
  position: fixed;
  top: 0;
  left: 0;
  width: 155%;
  max-width: 1400px;
  max-height: 45vh;
  z-index: -1;

  path {
    fill: var(--color-n-100);
  }
}

.template-main {
  position: relative;
  box-sizing: border-box;
  height: 100%;
  overflow-x: hidden;
  display: flex;
  flex-direction: column;
}

.container {
  display: grid;
  grid-template-columns: 100vw;
  grid-template-rows: max-content;
  grid-template-areas: "nav"
                       "filters"
                       "content";
}

.main-nav {
  grid-area: nav;
}

.filters {
  grid-area: filters;
  padding: 16px 16px 24px;
}

.content {
  grid-area: content;
  padding: 24px 16px;
}

// .vue-portal-target {
//   padding-top: 16px;
// }

.desktop {
  .container {
    grid-template-columns: minmax(auto, 380px) 1fr;
    grid-template-rows: max-content 1fr;
    grid-template-areas: "nav content"
                         "filters content";
    column-gap: 80px;
    padding: 0 42px;
    overflow: hidden;
    height: 100%;
  }

  .content {
    padding: 96px 16px;
    overflow-y: auto;
  }

  // .vue-portal-target {
  //   padding-top: 32px;
  // }
}
</style>
