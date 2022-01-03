Ext.define('Admin.view.main.Main', {
  extend: 'Ext.tab.Panel',
  xtype: 'main',
  userCls: 'mainview',
  session: true,
  requires: [
    'Admin.view.main.MainController'
  ],
  controller: 'main',
  tabBarPosition: 'left',
  tabRotation: 'none',
  // autoOrientAnimation: true,
  tabBar: {
    padding: '33 0 0 0',
    minWidth: 150,
    items: [{
        xtype: 'spacer'
      },{
        xtype: 'button',
        text: 'Studio ⟫⟫',
        iconCls: 'x-fa fa-file-alt',
        handler: function (btn) {
          window.open('/studio', 'Cockpit-Studio');
        },
        tooltip: {
          align: 'b-t',
          html: 'Open Studio module'
        }
      }
    ]
  },
  defaults: {
    tab: {
      iconAlign: 'left',
      flex: '0 1 auto'
    }
  },
  items: [
    {
      xtype: 'groups',
      title: 'Groups',
      iconCls: 'x-fa fa-users',
      viewId: 'groups',
      tab: {
        userCls: 'groupsBtn',
        tooltip: {
          align: 'l-r',
          html: 'Manage groups'
        }
      }
    },
    {
      xtype: 'roles',
      title: 'Roles',
      iconCls: 'x-fa fa-user-secret',
      viewId: 'roles',
      columnMenu: {
        xtype: 'simplegridmenu'
      },
      tab: {
        userCls: 'rolesBtn',
        tooltip: {
          align: 'l-r',
          html: 'Manage roles'
        }
      }
    },
    {
      xtype: 'users',
      title: 'Users',
      iconCls: 'x-fa fa-user',
      viewId: 'users',
      columnMenu: {
        xtype: 'simplegridmenu'
      },
      tab: {
        userCls: 'usersBtn',
        tooltip: {
          align: 'l-r',
          html: 'Manage users'
        }
      }
    },
    {
      xtype: 'widget',
      title: 'notitle',
      viewId: 'none',
      tab: {
       xtype: 'spacer',
       width: 'auto',
       flex: '10 1 auto'
      }
    }
  ]
});
