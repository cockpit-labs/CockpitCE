Ext.define('Admin.view.group.GroupTabPanel', {
  extend: 'Admin.view.base.TabPanel',
  xtype: 'grouptabpanel',
  alias: 'widget.grouptabpanel',
  controller: 'grouptabpanelcontroller',
  requires: [],
  items: [{
    xtype: 'groupproperties',
    iconCls: 'x-fa fa-info-circle',
    title: 'Properties',
    bind: {
      record: '{record}'
    }
  }, {
    xtype: 'grouproles',
    title: 'Roles',
    emptyText: 'No role for group',
    disableSelection: true,
    titleBar: false,
    itemId: 'roles',
    iconCls: 'x-fa fa-user-secret'
  }, {
    xtype: 'userslist',
    title: 'Members',
    iconCls: 'x-fa fa-user',
    bind: '{users}',
    emptyText: 'No user in group',
    disableSelection: true,
    columnMenu: {
      xtype: 'simplegridmenu'
    }
  }]
});