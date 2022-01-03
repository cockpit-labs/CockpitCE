Ext.define('Admin.view.user.UserTabPanel', {
  extend: 'Admin.view.base.TabPanel',
  xtype: 'usertabpanel',
  alias: 'widget.usertabpanel',
  controller: 'usertabpanelcontroller',
  requires: [],
  items: [{
    xtype: 'userproperties',
    iconCls: 'x-fa fa-info-circle',
    title: 'Properties'
  },{
    xtype: 'userroles',
    title: 'Roles',
    itemId: 'roles',
    iconCls: 'x-fa fa-user-secret'
  }, {
    xtype: 'groupstree',
    bind: '{groups}',
    title: 'Groups',
    titleBar: false,
    itemId: 'groups',
    iconCls: 'x-fa fa-users'
  }]
});