Ext.define('Admin.view.group.Groups', {
  extend: 'Admin.view.base.GroupsTree',
  requires: [
    'Admin.view.group.GroupsController',
    'Admin.view.group.GroupsModel',
    'Ext.grid.plugin.TreeDragDrop'
  ],
  xtype: 'groups',
  alias: 'widget.groups',
  controller: 'groups',
  viewModel: {
    type: 'groups'
  },
  userCls: 'groupstree',
  plugins: {
    treedragdrop: true
  },
  titleBar: {
    xtype: 'panelheader',
    iconCls: 'x-fa fa-users',
    items:[{
      xtype: 'tool',
      type: 'refresh',
      userCls: 'refresh-tool',
      handler: 'reload',
      tooltip: 'Refresh list...'
    }]
  },
  allowDelete: true,
  allowCheck: false,
  bind: '{groups}',
  items: {
    xtype: 'toolbar',
    docked: 'top',
    items: [{
      xtype: 'button',
      text: 'Add',
      iconCls: 'x-fa fa-plus-circle',
      handler: 'addItem'
    }, {
      xtype: 'button',
      text: 'Expand all',
      iconCls: 'x-fa fa-caret-down',
      handler: 'expandAll'
    }, {
      xtype: 'button',
      text: 'Collapse all',
      iconCls: 'x-fa fa-caret-up',
      handler: 'collapseAll'
    }]
  }
});