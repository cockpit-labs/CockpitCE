Ext.define('Admin.Application', {
  extend: 'Ext.app.Application',
  name: 'Admin',
  requires: ['Admin.*', 'Ext.Anim'],
  stores: [],
  controllers: [
    'Root@Admin.controller'
  ],
  viewport: {
    controller: 'viewport',
    viewModel: 'viewport'
  },
  stores: [
    'Users' // Users store needs to be declared as global since a filter is applied on it (see UsersModel)
  ],
  defaultToken: 'users',
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
        title: 'Cockpit Admin',
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
              text: 'Profile',
              handler: 'profile',
              tooltip: {
                align: 'r-l',
                html: 'Show profile'
              }
            }, {
              iconCls: 'x-fa fa-question-circle',
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
    Admin.ux.ActivityMonitor.init({
      verbose: true,
      isInactive: controller.logout.bind(controller),
      fn: controller.refreshToken,
      scope: controller
    });
    Admin.ux.ActivityMonitor.start();
  },
  loadMandatoryStores: function() {
    Admin.Application.prototype.superclass.onBeforeLaunch.call(this);
  },
  /**
   * Manages overrides that need controllers to be initialized.
   */
  addOverrides: function() {
    // add authorization header on ajax request
    Ext.Ajax.on({
      beforerequest:function(conn, options) {
        var session = Admin.util.State.get('session');
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
      case 403:
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
