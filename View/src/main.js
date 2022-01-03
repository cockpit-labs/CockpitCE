import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'

import VueKeycloak from './plugins/vue-keycloak'
import { updateAuthorizationToken } from './plugins/http'
import { i18n, loadLanguage } from './plugins/i18n'
import './plugins/fontawesome'
import CockpitUi from './plugins/cockpit-ui'

import VueMq from 'vue-mq'
import PortalVue from 'portal-vue'
import GlobalEvents from 'vue-global-events'
import { Settings } from 'luxon'
import VueFuse from 'vue-fuse'

Vue.config.productionTip = false

Vue.use(VueMq, {
  breakpoints: {
    mobile: 450,
    tablet: 900,
    desktop: Infinity
  }
})

Vue.use(CockpitUi)
Vue.use(PortalVue)
Vue.component('GlobalEvents', GlobalEvents)
Vue.use(VueFuse)

fetch('/api/configs/view')
  .then(response => response.json())
  .then(data => {
    store.state.targetListWithTiles = data.params?.tile_mode || false
    store.state.showTargetData = true

    Vue.use(VueKeycloak, {
      authParams: data.keycloak,
      onReady: async (keycloak) => {
        Settings.defaultLocale = keycloak.locale || 'fr'
        await loadLanguage(keycloak.locale)
        store.commit('setUser', {
          username: keycloak.userName,
          fullname: keycloak.fullName
        })

        new Vue({
          router,
          store,
          i18n,
          render: h => h(App)
        }).$mount('#app')
      },
      onTokenUpdated: token => {
        updateAuthorizationToken(token)
      }
    })
  })
