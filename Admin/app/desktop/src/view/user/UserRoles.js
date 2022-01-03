Ext.define('Admin.view.user.UserRoles', {
  extend: 'Ext.Container',
  xtype: 'userroles',
  alias: 'widget.userroles',
  requires: [
    'Ext.Label'
  ],
  layout: {
    type: 'hbox',
    pack: 'space-between'
  },
  defaults: {
    shadow: true,
    flex: 1
  },
  items: [{
    xtype: 'roleslist',
    userCls: 'allroles',
    itemTpl: '{name}',
    bind: '{roles}',
    itemConfig: {
      tools: {
        disclosure: {
          tooltip: 'Add role to user'
        }
      }
    },
    onItemDisclosure: 'addUserRole',
    items: {
      xtype: 'label',
      docked: 'top',
      html: 'Available Roles:'
    }
  },{
    // xtype: 'grid',
    xtype: 'list',
    userCls: 'assigned-roles',
    emptyText: 'No assigned role',
    deferEmptyText: false,
    itemTpl: '{name}',
    // columnLines: true,
    // columns: [{
    //   text: 'Id',
    //   dataIndex:'name'
    // }],
    bind: '{userRoles}',
    itemConfig: {
      tools: {
        disclosure: {
          tooltip: 'Remove role from user'
        }
      }
    },
    onItemDisclosure: 'removeUserRole',
    items: {
      xtype: 'label',
      docked: 'top',
      html: 'Assigned Roles:'
    }
  },{
    xtype: 'list',
    userCls: 'effective-roles',
    itemConfig: {
      tpl: '{name}',
      viewModel: true,
      bind: {
        userCls: '{record.cls}'
      }
    },
    emptyText: 'No effective role',
    deferEmptyText: false,
    bind: '{userEffectiveRoles}',
    onItemDisclosure: 'roleInfo',
    items: {
      xtype: 'label',
      docked: 'top',
      html: 'Effective Roles:'
    }
  }]
});