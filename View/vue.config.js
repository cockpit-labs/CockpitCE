module.exports = {
  devServer: {
    port: 3454,
    proxy: {
      '^/api': {
        target: 'http://localhost',
        ws: true,
        changeOrigin: true
      }
    }
  },
  css: {
    loaderOptions: {
      scss: {
        additionalData: `
          @import "~@/plugins/cockpit-ui/scss/variables.scss";
          @import "~@/plugins/cockpit-ui/scss/mixins.scss";
        `
      }
    }
  }
}
