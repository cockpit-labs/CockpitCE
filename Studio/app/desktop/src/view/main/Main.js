Ext.define('Studio.view.main.Main', {
  extend: 'Ext.tab.Panel',
  xtype: 'main',
  userCls: 'mainview',
  session: true,
  requires: [
    'Studio.view.main.MainController'
  ],
  controller: 'main',
  tabBarPosition: 'left',
  tabRotation: 'none',
  tabBar: {
    padding: '33 0 0 0',
    minWidth: 150,
    items: [{
        xtype: 'spacer'
      },{
        xtype: 'button',
        text: 'Admin ⟫⟫',
        iconCls: 'x-fa fa-users',
        handler: function (btn) {
          window.open('/admin', 'Cockpit-Admin');
        },
        tooltip: {
          align: 'b-t',
          html: 'Open Administration module'
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
  items: [{
    xtype:'calendars',
    title: 'Calendars',
    iconCls: 'x-fa fa-calendar-alt',
    iconAlign: 'left',
    viewId: 'calendars',
    tab: {
      userCls: 'calendarsBtn',
      tooltip: {
        align: 'l-r',
        html: 'Manage calendars'
      }
    }
  },
  {
    xtype:'folders',
    title: 'Folders',
    iconCls: 'x-fa fa-folder',
    iconAlign: 'left',
    viewId: 'folders',
    tab: {
      userCls: 'foldersBtn',
      tooltip:  {
        align: 'l-r',
        html: 'Manage folders'
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
  }]
});
