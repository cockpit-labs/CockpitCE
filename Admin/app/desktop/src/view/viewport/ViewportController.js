Ext.define('Admin.view.viewport.ViewportController', {
  extend: 'Ext.app.ViewController',
  alias: 'controller.viewport',
  config: {
    pendingLoads: 0
  },
  listen: {
    controller: {
      '*': {
        logout: 'onLogout',
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
    var defaultRoute = Admin.getApplication().getDefaultToken();
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
    Admin.util.State.set('session', null);
  },
  updateToken: function () {
    var authProvider = this.getAuthProvider();
    Admin.util.State.set('session', {token: authProvider.token, tokenParsed: authProvider.tokenParsed});
    this.getViewModel().set('user', {id: authProvider.tokenParsed.preferred_username, name: authProvider.tokenParsed.name});
  },
  clearToken: function() {
    Admin.util.State.set('session', null);
  },
  checkRoles: function() {
    var authProvider = this.getAuthProvider();
    if (authProvider.tokenParsed && authProvider.tokenParsed.realm_access) {
      var roles = authProvider.tokenParsed.realm_access.roles;
      if (roles.indexOf('CKP_Superuser') != -1) {
        Admin.util.State.set('superuser', '1');
      }
      return roles.indexOf('CKP_Admin') != -1;
    } else {
      console.warn('No information for user realm_access');
    }
    return false;
    // return (authProvider.tokenParsed && authProvider.tokenParsed.realm_access && authProvider.tokenParsed.realm_access.roles && );
    // 
  },
  onLogout: function () {
    if (!arguments) {
      // logout called from keycloak
    }
    Admin.ux.ActivityMonitor.stop();
    this.logout();
  },
  logout: function() {
    this.clearToken();
//    Ext.defer(this.getAuthProvider().logout, 3000)
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
    var token = Admin.util.State.get('session').tokenParsed;
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
