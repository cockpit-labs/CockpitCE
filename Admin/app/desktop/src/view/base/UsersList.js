Ext.define('Admin.view.base.UsersList', {
  extend: 'Ext.grid.Grid',
  requires: [
  ],
  xtype: 'userslist',
  alias: 'widget.userslist',
  userCls: 'basegrid',
  titleBar: false,
  itemId: 'users',
  iconCls: 'x-fa fa-user',
  infinite: false,
  // no need grouping
  grouped: false,
  columnLines: true,
  columnMenu: null,
  // There is no asymmetric data, we do not need to go to the expense of synching row heights
  syncRowHeight: false,
  headerBorders: false,
  userSelectable: 'text',
  shadow: true,
  columns: [{
    xtype: 'checkcolumn',
    text: 'Enabled?',
    dataIndex: 'enabled',
    align: 'center',
    width: 75,
    menu: null,
    sortable: false,
    editable: false,
    draggable: false,
    cell: {
      userCls: 'customcheck activeDataIndex'
    },
    disabled: true
  }, {
    text: 'User name',
    dataIndex: 'username',
    minWidth: 200,
    draggable: false
  },{
    text: 'ID',
    dataIndex: 'id',
    hidden: true,
    draggable: false,
    width: 270
  },{
    text: 'First name',
    dataIndex: 'firstname',
    draggable: false,
    width: 120
  },{
    text: 'Last name',
    dataIndex: 'lastname',
    draggable: false,
    flex: 1,
    width: 120
  }]
});