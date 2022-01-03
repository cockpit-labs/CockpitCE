import './scss/cockpit-ui.scss'

import * as components from './components'

const cockpitUi = {
  install (Vue, options) {
    for (const componentKey in components) {
      Vue.use(components[componentKey])
    }
  }
}

export default cockpitUi
