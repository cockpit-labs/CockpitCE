<template>
  <img :class="['lazy-image', orientation]" :src="src" @click="$emit('selected', src)" />
</template>

<script>
import { http } from '@/plugins/http'

export default {
  name: 'LazyImage',

  props: {
    dataSrc: String,
    height: Number,
    width: Number
  },

  data () {
    return {
      src: this.placeholderSrc(this.width, this.height),
      loaded: false,
      observer: null,
      intersected: false
    }
  },

  computed: {
    orientation () {
      return this.height > this.width ? 'portrait' : 'landscape'
    }
  },

  methods: {
    async loadImage () {
      const src = await this.download()
      this.src = src
      this.loaded = true
    },

    placeholderSrc (w, h) {
      return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + w + ' ' + h + '"%3E%3C/svg%3E'
    },

    async download () {
      const id = this.dataSrc

      try {
        const response = await http.get('user_media/' + id + '/content', {
          responseType: 'blob'
        })
        const imageUrl = URL.createObjectURL(response.data)
        return Promise.resolve(imageUrl)
      } catch (error) {
        return Promise.reject(error)
      }
    },

    getImageSrc () {
      return this.src
    }
  },

  mounted () {
    this.observer = new IntersectionObserver(entries => {
      const image = entries[0]
      if (image.isIntersecting) {
        this.intersected = true
        this.loadImage()
        this.observer.disconnect()
      }
    })

    this.observer.observe(this.$el)
  },

  destroyed () {
    this.observer.disconnect()
    URL.revokeObjectURL(this.src)
  }
}
</script>

<style lang="scss" scoped>
.lazy-image {
  background-color: var(--color-n-200);
}
</style>
