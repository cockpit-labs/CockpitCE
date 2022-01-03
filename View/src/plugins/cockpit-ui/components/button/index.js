import SButton from './SButton'
import SButtonIcon from './SButtonIcon'
import SButtonText from './SButtonText'

export default {
  install (Vue) {
    Vue.component(SButton.name, SButton)
    Vue.component(SButtonIcon.name, SButtonIcon)
    Vue.component(SButtonText.name, SButtonText)
  }
}
