Ext.define('Admin.view.user.Users', {
  extend: 'Admin.view.base.Grid',
  requires: [
    'Admin.view.user.UsersController',
    'Admin.view.user.UsersModel',
    'Admin.view.user.UsersRowModel'
  ],
  xtype: 'users',
  alias: 'widget.users',
  controller: 'users',
  viewModel: {
    type: 'users'
  },
  cls: 'userlist',
  itemConfig: {
    viewModel: {
      type: 'usersrowmodel'
    }
  },
  filterEnabled: true,
  titleBar: {
    xtype: 'panelheader',
    iconCls: 'x-fa fa-user'
  },
  emptyText: 'No user found',
  columns: [{
    xtype: 'checkcolumn',
    dataIndex: 'enabled',
    width: 45,
    resizable: false,
    draggable: false,
    menu: null,
    hidden: true,
    hideable: false,
    cell: {
      userCls: 'activeColumn',
      tools: {
        // Tools can also be configured using an object.
        role: {
        }
    }}
  },{
    text: 'User name',
    dataIndex: 'username',
    flex: 1,
    minWidth: 200,
    cell: {
      tools: {
        user: {}
      }
    }
  },{
    xtype: 'checkcolumn',
    text: 'Enabled?',
    dataIndex: 'enabled',
    align: 'center',
    width: 110,
    // menu: null,
    // sortable: false,,
    cell: {
      bind: {
        userCls: 'customcheck activeDataIndex {userExpiredCls}'
      }
    },
    listeners: {
      checkchange: 'updateItem',
      beforecheckchange: function(me , rowIndex , checked , record , e , eOpts){
        if (record.get('enabled') || Ext.Date.diff(new Date(), record.get('expirationDate'), Ext.Date.DAY) >= 0) {
            return true;
        }
        return false;
      }
    }
  },{
    text: 'ID',
    dataIndex: 'id',
    hidden: true,
    width: 270
  },{
    text: 'First name',
    dataIndex: 'firstname',
    width: 120
  },{
    text: 'Last name',
    dataIndex: 'lastname',
    width: 120
  },{
    text: 'Email',
    dataIndex: 'email',
    width: 250
  },{
    xtype: 'checkcolumn',
    text: 'Email verified?',
    dataIndex: 'emailVerified',
    align: 'center',
    width: 120,
    // menu: null,
    // sortable: false,
    editable: false,
    cell: {
      userCls: 'customcheck'
    },
    disabled: true
  },{
    xtype: 'datecolumn',
    text: 'Expires on',
    align: 'center',
    dataIndex: 'expirationDate',
    format: 'Y-m-d',
    width: 100
  },{
    width: 40,
    resizable: false,
    cell: {
      tools: {
        remove: {
          iconCls: 'x-fa fa-trash',
          action: 'deleteItem',
          tooltip: 'Delete user...'
        }
      }
    }
  }]
});