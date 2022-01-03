import Vue from 'vue'
import VueI18n from 'vue-i18n'
import axios from 'axios'

Vue.use(VueI18n)

export const i18n = new VueI18n()

export async function loadLanguage (lang = 'fr') {
  let response

  try {
    response = await axios.get('/lang/' + lang + '.json')
  } catch {
    // if file no exist
    response = await axios.get('/lang/fr.json')
    lang = 'fr'
  }

  const messages = response.data
  i18n.setLocaleMessage(lang, messages)
  return setLanguage(lang)
}

function setLanguage (lang) {
  i18n.locale = lang
  document.querySelector('html').setAttribute('lang', lang)
  return lang
}
