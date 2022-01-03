Ext.define('Admin.view.role.Roles', {
  extend: 'Admin.view.base.Grid',
  requires: [
    'Admin.view.role.RolesController',
    'Admin.view.role.RolesModel'
  ],
  xtype: 'roles',
  cls: 'rolelist',
  alias: 'widget.roles',
  controller: 'roles',
  viewModel: {
    type: 'roles'
  },
  itemConfig: {
    viewModel: true
  },
  titleBar: {
    xtype: 'panelheader',
    iconCls: 'x-fa fa-user-secret',
  },
  emptyText: 'No role found',
  columns: [{
    text: 'Name',
    dataIndex: 'name',
    width: 200,
    cell: {
      tools: {
        role: {}
      }
    }
  },{
    text: 'ID',
    dataIndex: 'id',
    hidden: true
  },{
    text: 'Description',
    dataIndex: 'description',
    flex: 1,
    minWidth: 150
  },{
    xtype: 'checkcolumn',
    text: 'System?',
    dataIndex: 'system',
    align: 'center',
    width: 75,
    menu: null,
    sortable: false,
    editable: false,
    cell: {
      userCls: 'customcheck'
    },
    disabled: true
  },{
    width: 40,
    resizable: false,
    cell: {
      tools: {
        remove: {
          iconCls: 'x-fa fa-trash',
          action: 'deleteItem',
          bind: {
            hidden: '{record.system}'
          },
          tooltip: 'Delete role...'
        }
      }
    }
  }]
});