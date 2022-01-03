Ext.define('Admin.view.role.RoleTabPanel', {
  extend: 'Admin.view.base.TabPanel',
  xtype: 'roletabpanel',
  alias: 'widget.roletabpanel',
  controller: 'roletabpanelcontroller',
  requires: [],
  items: [{
    xtype: 'roleproperties',
    iconCls: 'x-fa fa-info-circle',
    title: 'Properties'
  }, {
    xtype: 'groupstree',
    bind: '{groups}',
    title: 'Groups in role',
    emptyText: 'No group in role',
    disableSelection: true,
    titleBar: false,
    itemId: 'groups',
    iconCls: 'x-fa fa-users'
  }, {
    xtype: 'userslist',
    title: 'Users in role',
    bind: '{users}',
    emptyText: 'No user in role',
    disableSelection: true,
    columnMenu: {
      xtype: 'simplegridmenu'
    }
  }]
});