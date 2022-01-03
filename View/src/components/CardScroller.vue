<template>
  <div class="card-scroller">
    <div class="track">
      <slot />
      <div class="end-margin"></div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CardScroller',

  computed: {
    cards () {
      const cards = this.$slots.default
      return cards
    }
  }
}
</script>

<style lang="scss" scoped>
.track {
  display: flex;
  column-gap: 8px;
  overflow-x: scroll;
  scroll-snap-type: x mandatory;
  scroll-padding-left: 16px;
  margin: 0 -16px -8px;
  padding: 0 16px 8px;
  scrollbar-width: none;

  &::-webkit-scrollbar {
    display: none;
  }
}

.track > div:not(.end-margin) {
  flex-grow: 1;
  flex-shrink: 0;
  flex-basis: 100%;
  scroll-snap-align: start;
}

.end-margin {
  flex: none;
  width: 8px;
}

.desktop {
  .track {
    flex-direction: column;
    overflow: visible;
    row-gap: 16px;
  }

  .end-margin {
    display: none;
  }
}

// .desktop {
//   .track {
//     display: block;
//   }

//   .track > div:not(.end-margin) {
//     margin-bottom: 16px;
//   }

//   .end-margin {
//     display: none;
//   }
// }
</style>
