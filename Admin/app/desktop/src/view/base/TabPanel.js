Ext.define('Admin.view.base.TabPanel', {
  extend: 'Ext.tab.Panel',
  xtype: 'basetabpanel',
  userCls: 'tabpanel',
  alias: 'widget.basetabpanel',
  // requires: [
  //   'Ext.layout.Center'
  // ],
  // layout: {
  //   type: 'center'
  // },
  // layout: {
  //   type: 'hbox',
  //   pack: 'center'
  // },
  tabBar: {
    layout: {
      pack: 'center'
    }
  },
  defaults: {
    iconAlign: 'left'
  },
  autoOrientAnimation: false,
  layout: {
    align: 'center',
    animation: null
  }
});