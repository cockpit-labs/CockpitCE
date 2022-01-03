import Keycloak from 'keycloak-js'

export default {
  install (Vue, params = {}) {
    const defaultParams = {
      authParams: null,
      init: {
        onLoad: 'login-required'
      },
      loginOptions: {},
      logoutOptions: {},
      updateTokenIntervalDuration: 60000
    }

    const options = Object.assign({}, defaultParams, params)

    options.init.promiseType = 'native'

    const watch = new Vue({
      data () {
        return {
          ready: false,
          authenticated: false,
          userName: null,
          fullName: null,
          locale: null,
          token: null,
          tokenParsed: null,
          logout: null,
          login: null,
          createLoginUrl: null,
          createLogoutUrl: null,
          createRegisterUrl: null,
          register: null,
          accountManagement: null,
          createAccountUrl: null,
          loadUserProfile: null,
          loadUserInfo: null,
          subject: null,
          idToken: null,
          idTokenParsed: null,
          realmAccess: null,
          resourceAccess: null,
          refreshToken: null,
          refreshTokenParsed: null,
          timeSkew: null,
          responseMode: null,
          responseType: null,
          hasRealmRole: null,
          hasResourceRole: null
        }
      }
    })

    init(options, watch)

    Object.defineProperty(Vue.prototype, '$keycloak', {
      get () {
        return watch
      }
    })
  }
}

async function init (options, watch) {
  const keycloak = Keycloak(options.authParams)
  let updateTokenInterval

  keycloak.init(options.init)
    .catch(error => {
      if (typeof options.onInitError === 'function') {
        options.onInitError(error)
      }
    })

  keycloak.onReady = authenticated => {
    console.log('onReady')
    updateWatchVariables(authenticated)
    watch.ready = true
    if (typeof options.onReady === 'function') {
      options.onReady(watch)
    }
  }

  keycloak.onAuthSuccess = () => {
    console.log('onAuthSuccess')
    if (typeof options.onTokenUpdated === 'function') {
      options.onTokenUpdated(keycloak.token)
    }
    // Check token validity every "options.updateTokenIntervalDuration" ms and, if necessary, update the token.
    // Refresh token if it's valid for less then 60 seconds
    updateTokenInterval = setInterval(() => {
      keycloak.updateToken(60)
        .catch(() => {
          keycloak.clearToken()
        })
    }
    , options.updateTokenIntervalDuration)
  }

  keycloak.onAuthRefreshSuccess = () => {
    console.log('onAuthRefreshSuccess')
    if (typeof options.onTokenUpdated === 'function') {
      options.onTokenUpdated(keycloak.token)
    }
    updateWatchVariables(true)
  }

  keycloak.onAuthLogout = () => {
    console.log('onAuthLogout')
    watch.authenticated = false

    if (typeof options.onAuthLogout === 'function') {
      options.onAuthLogout()
    }
  }

  keycloak.onTokenExpired = () => {
    console.log('onTokenExpired')

    if (typeof options.onTokenExpired === 'function') {
      options.onTokenExpired()
    }
  }

  function logout () {
    clearInterval(updateTokenInterval)
    keycloak.logout()
  }

  function updateWatchVariables (isAuthenticated = false) {
    watch.authenticated = isAuthenticated
    watch.logout = logout.bind(this)
    watch.login = keycloak.login.bind(this, options.loginOptions)
    watch.createLoginUrl = keycloak.createLoginUrl
    watch.createLogoutUrl = keycloak.createLogoutUrl
    watch.createRegisterUrl = keycloak.createRegisterUrl
    watch.register = keycloak.register

    if (isAuthenticated) {
      watch.accountManagement = keycloak.accountManagement
      watch.createAccountUrl = keycloak.createAccountUrl
      watch.hasRealmRole = keycloak.hasRealmRole
      watch.hasResourceRole = keycloak.hasResourceRole
      watch.loadUserProfile = keycloak.loadUserProfile
      watch.loadUserInfo = keycloak.loadUserInfo
      watch.token = keycloak.token
      watch.subject = keycloak.subject
      watch.idToken = keycloak.idToken
      watch.idTokenParsed = keycloak.idTokenParsed
      watch.realmAccess = keycloak.realmAccess
      watch.resourceAccess = keycloak.resourceAccess
      watch.refreshToken = keycloak.refreshToken
      watch.refreshTokenParsed = keycloak.refreshTokenParsed
      watch.timeSkew = keycloak.timeSkew
      watch.responseMode = keycloak.responseMode
      watch.responseType = keycloak.responseType
      watch.tokenParsed = keycloak.tokenParsed
      watch.userName = keycloak.tokenParsed.preferred_username
      watch.fullName = keycloak.tokenParsed.name
      watch.locale = keycloak.tokenParsed.locale
    }
  }
}
