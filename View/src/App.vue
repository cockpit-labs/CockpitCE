<template>
  <div id="app" class="s-font" :class="$mq">
    <router-view/>

    <portal-target name="modal-view" multiple />

    <SAlert ref="alert" type="error">
      <template #title>{{ $t('alert.errorTitle', {code: error.code}) }}</template>
      <template #content>
        <div v-if="error.code === 403" v-html="$t('alert.forbidden')"></div>
        <div v-else-if="error.code === 401" v-html="$t('alert.unauthorized')"></div>
        <div v-else v-html="error.message || $t('alert.errorOccurred')"></div>
      </template>
      <template #actions>
        <SButton @click="$refs.alert.close()">{{ $t('close') }}</SButton>
      </template>
    </SAlert>
  </div>
</template>

<script>
import { http } from '@/plugins/http'
import { isEmpty } from 'lodash'

export default {
  data () {
    return {
      error: {
        code: null,
        message: null
      }
    }
  },

  created () {
    http.interceptors.response.use(response => {
      const contentType = response.headers['content-type']
      if ((contentType?.indexOf('application/json') !== -1) && isEmpty(response.data)) {
        response.data = []
      }
      return response
    }, error => {
      if (error.response) {
        this.error.code = error.response.status
        this.error.message = error.response.data?.detail
      }
      this.$refs.alert.open()
      return Promise.reject(error)
    })
  },

  mounted () {
    this.$store.commit('setFolderDataLoading', true)
    Promise.all([
      this.$store.dispatch('getUsers'),
      this.$store.dispatch('getGroups'),
      this.$store.dispatch('getTemplates'),
      this.$store.dispatch('getDraftFolders')
    ]).finally(() => {
      this.$store.commit('setFolderDataLoading', false)
    })
  }
}
</script>

<style lang="scss">
html {
  height: 100%;
}

body {
  margin: 0;
  background-color: var(--color-n-050);
  overflow: hidden;
  height: 100%;
}

#app {
  height: 100%;
  width: 100vw;
  // display:flex;
  // flex-direction: column;
  box-sizing: border-box;
  // overflow: hidden;
}

// .page {
//   padding: 126px 16px 32px;
//   overflow: auto;
// }

.link {
  color: $color-n-600;
  text-decoration: none;
  font-size: 16px;
}

#app.desktop {
  .page {
    padding: 126px 62px 32px;
  }
}
</style>
