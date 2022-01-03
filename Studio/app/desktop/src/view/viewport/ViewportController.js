Ext.define('Studio.view.viewport.ViewportController', {
  extend: 'Ext.app.ViewController',
  alias: 'controller.viewport',
  config: {
    pendingLoads: 0
  },
  listen: {
    controller: {
      '*': {
        logout: {
          delay: 200,
          fn: 'onLogout'
        },
        unmatchedroute: 'handleUnmatchedRoute'
      }
    }
  },
  getAuthProvider: function() {
    return window.keycloak;
  },
  showView: function (view) {
    var look = this.lookup(view);
    var viewport = this.getView();
    if (!look) {
      viewport.removeAll(true);
      look = viewport.add({
        xtype: view,
        reference: view
      });
    }
    viewport.setActiveItem(look);
  },
  handleUnmatchedRoute: function (route) {
    var me = this;
    var defaultRoute = Studio.getApplication().getDefaultToken();
    Ext.log.warn('Route unknown: ', route);
    if (route !== defaultRoute) {
      this.redirectTo(defaultRoute, {
        replace: true
      });
    }
  },
  reloadStores: function(store) {
    // TODO, check if there is a dirty store that will be reloaded and ask user for confirmation
    var storesToReload = [];
    Ext.StoreManager.each(function(st) {
      if (st.getProxy().type !== 'memory') {
        if (st.isChainedStore) {
          st = st.getSource();
        }
        storesToReload.push(st);
      }
    });
    this.reloadSingleStores(storesToReload);
  },
  reloadSingleStores: function(stores) {
    this.pendingLoads = stores.length;
    if (stores.length > 0) {
      stores.shift().load({
        callback: this.reloadSingleStores.bind(this, stores)
      });
    }
  },
  hasPendingLoads: function() {
    return this.pendingLoads > 1;
  },
  refreshToken: function(success, scope) {
    this.getAuthProvider().updateToken(30).then(Ext.Function.createSequence(this.updateToken.bind(this), success, scope)).catch(this.onLogout.bind(this));
  },
  clearToken: function() {
    Studio.util.State.set('session', null);
  },
  updateToken: function () {
    var authProvider = this.getAuthProvider();
    Studio.util.State.set('session', {token: authProvider.token, tokenParsed: authProvider.tokenParsed});
    this.getViewModel().set('user', {id: authProvider.tokenParsed.preferred_username, name: authProvider.tokenParsed.name});
  },
  clearToken: function() {
    Studio.util.State.set('session', null);
  },
  checkRoles: function() {
    var authProvider = this.getAuthProvider();
    return (authProvider.tokenParsed && authProvider.tokenParsed.realm_access && authProvider.tokenParsed.realm_access.roles && Ext.Array.contains(authProvider.tokenParsed.realm_access.roles, 'CKP_Studio'));
  },
  onLogout: function () {
    if (!arguments) {
      // logout called from keycloak
    }
    Studio.ux.ActivityMonitor.stop();
    this.logout();
  },
  logout: function() {
    this.clearToken();
    this.getAuthProvider().logout();
  },
  getFileReader: function() {
    var me = this;
    if (!me.fileReader) {
      me.fileReader = new FileReader();
    }
    return me.fileReader;
  },
  profile: function() {
    var token = Studio.util.State.get('session').tokenParsed;
    Ext.Msg.alert('Profile', '<b>Name: </b>' + token.name + '<br /><b>Login: </b>' + token.preferred_username + '<br /><b>Email: </b>' + token.email, Ext.emptyFn);
  },
  about: function() {
    Ext.Msg.alert('About ' + Ext.manifest.name, 'Version: ' + Ext.manifest.version, Ext.emptyFn);
  },
  destroy: function() {
    this.fileReader = null;
    this.callParent();
  }
});
