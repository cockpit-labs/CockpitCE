<template>
  <font-awesome-icon :icon="[fontStyle, name]" class="s-icon" :fixed-width="fw" :spin="spin" />
</template>

<script>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

export default {
  name: 'SIcon',

  components: {
    FontAwesomeIcon
  },

  props: {
    name: {
      required: true,
      type: String
    },
    fw: {
      type: Boolean,
      default: false
    },
    regular: {
      type: Boolean,
      default: false
    },
    spin: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    fontStyle () {
      return this.regular ? 'far' : 'fas'
    }
  }
}
</script>
