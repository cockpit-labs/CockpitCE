Ext.define('Studio.Application', {
  extend: 'Ext.app.Application',
  name: 'Studio',
  requires: ['Studio.*'],
  stores: ['Calendars', 'Roles', 'Rights', 'QuestionTypes'],
  viewport: {
    controller: 'viewport',
    viewModel: 'viewport'
  },
  defaultToken: 'folders',
  onBeforeLaunch: function() {
    var me = this;
    // remove loader
    const initialLoader = Ext.getDom('loader');
    initialLoader && initialLoader.remove();

    me.addOverrides();

    var controller = Ext.Viewport.getController();
    controller.refreshToken(
      function() {
        if (!controller.checkRoles()) {
          Ext.Msg.alert('Permission denied', 'You do not have sufficient permissions to access this application', controller.logout, controller);
          return;
        }

        if (me.stores) {
          me.loadMandatoryStores();
        } else {
          me.callParent();
        }
      },
      controller
    );
  },
  launch: function () {
    Ext.Viewport.add({
      xtype: 'panel',
      header: {
        title: 'Cockpit Studio',
        // userCls: 'app-title',
        items: {
          xtype: 'button',
          iconCls: 'x-fa fa-user',
          bind: '{user.name}',
          userCls: 'profile',
          // menuAlign: 'tr-br',
          stretchMenu: true,
          menu: {
            xtype: 'menu',
            // indented: false,
            items: [{
              iconCls: 'x-fa fa-id-card',
              userCls: 'userProfileBtn',
              text: 'Profile',
              handler: 'profile',
              tooltip: {
                align: 'r-l',
                html: 'Show profile'
              }
            }, {
              iconCls: 'x-fa fa-question-circle',
              userCls: 'aboutBtn',
              text: 'About',
              handler: 'about',
              tooltip: {
                align: 'r-l',
                html: 'About Cockpit Administration module'
              }
            }, {
              xtype: 'menuseparator'
            }, {
              text: 'Logout',
              iconAlign: 'left',
              iconCls: 'x-fa fa-power-off',
              userCls: 'logoutBtn',
              handler: function (btn) {
                Ext.Viewport.down('main').setActiveItem({
                  userCls: 'goodbye',
                  viewId: 'logout',
                  tab: {
                    title: 'Goodbye',
                    hidden: true
                  }
                });
              },
              tooltip: {
                align: 'r-l',
                html: 'Logout'
              }
            }]
          }
        }
      },
      userCls: 'viewport',
      layout: 'fit',
      items:{
        xtype: 'main'
      }
    });
    var controller = Ext.Viewport.getController();
    Studio.ux.ActivityMonitor.init({
      verbose: true,
      isInactive: controller.logout.bind(controller),
      fn: controller.refreshToken,
      scope: controller
    });
    Studio.ux.ActivityMonitor.start();
  },
  loadMandatoryStores: function() {
    // load necessary stores before loading viewport
    var allStoresLoadCheck = new Array(this.stores.length);
    Ext.Viewport.setMasked({
      xtype: 'loadmask',
      message: 'Loading configuration...'
    });
    var keepWaitingStoreLoad = function(storeIndex) {
      allStoresLoadCheck[storeIndex] = true;
      if (allStoresLoadCheck.indexOf(false) == -1) {
        Ext.Viewport.setMasked(false);
        Studio.Application.prototype.superclass.onBeforeLaunch.call(this);
      };
    };

    for (var i=0; i < this.stores.length; i++) {
      var store = this.getStore(this.stores[i]);
      if (!store.isLoaded()) {
        allStoresLoadCheck[i] = false;
        store.load({
          callback: Ext.Function.pass(keepWaitingStoreLoad, [i], this)
        });
      }
    }
  },
  /**
   * Manages overrides that need controllers to be initialized.
   */
  addOverrides: function() {
    // add authorization header on ajax request
    Ext.Ajax.on({
      beforerequest:function(conn, options) {
        var session = Studio.util.State.get('session');
        var token = session ? session.token : null;
        options.cors = true;
        options.headers = Ext.apply({
          'Authorization' : 'Bearer ' + token
        }, options.headers || {});
        return true;
      },
      requestexception: this.requestException
    });

    /* added getGeneration on Model function in order to make bindings work (See data/Model override) */
    Ext.app.bind.Stub.prototype.bindMappings.model.generation = 'getGeneration';
  },
  requestException: function(conn, response, options, eOpts) {
    switch (response.status) {
      case 401:
      case 400:
      case 404:
        message = response.statusText;
        break;
      default:
        message = 'Connexion failed' + ((response.status == 0)?'.':' [' + response.status + ' : ' + response.statusText + '].');
        break;
    }
    Ext.Viewport.setMasked(false);
    Ext.log({
      level: 'error',
      msg: message
    });
    if (options.failure) {
      Ext.callback(options.failure, options.scope, [response, options.operation, false]);
      return;
    } else if (options.operation && options.operation.failure) {
      // for Models load/save : failure is automatically called
      //Ext.callback(options.operation.failure, options.operation.scope, [response, options.operation, false]);
      return;
    }
    var msgAlert = Ext.Msg.show({
      title:'Error',
      message:message,
      minWidth:350,
      prompt: false,
      buttons:[{
        text: 'Cancel',
        itemId: 'cancel'
      }],
      scope: this,
      fn: function(btn) {
        msgAlert.close();
      }
    });
    msgAlert.setAlwaysOnTop(true);
  },
  onAppUpdate: function () {
    console.log("Auto-updating application... Reloading...");
    window.location.reload(true);
  }
});
