Ext.define('Admin.view.group.GroupRoles', {
  extend: 'Ext.Container',
  xtype: 'grouproles',
  alias: 'widget.grouproles',
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
          tooltip: 'Add role to group'
        }
      }
    },
    onItemDisclosure: 'addGroupRole',
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
    bind: '{groupEffectiveRoles}',
    itemConfig: {
      tools: {
        disclosure: {
          tooltip: 'Remove role from group'
        }
      }
    },
    onItemDisclosure: 'removeGroupRole',
    items: {
      xtype: 'label',
      docked: 'top',
      html: 'Assigned Roles:'
    }
  }]
});