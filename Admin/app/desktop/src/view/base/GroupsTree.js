Ext.define('Admin.view.base.GroupsTree', {
  extend: 'Ext.tree.Tree',
  requires: [
    'Ext.data.TreeStore',
    'Ext.grid.Tree'
  ],
  xtype: 'groupstree',
  alias: 'widget.groupstree',
  emptyText: 'No group found',
  displayField: 'label',
  rowLines: true,
  columnLines: true,
  infinite: false,
  striped: true,
  markDirty: true,
  config: {
    allowDelete: false,
    allowCheck: true
  },
  userCls: 'basegrid',
  selectable: {
    mode: 'single'
  },
  initialize: function() {
    var columns = [{
      xtype: 'treecolumn',
      dataIndex: 'label',
      text: 'Label',
      sortable: false,
      cls: 'treecolumnwidget-header',
      flex: 1,
      minWidth: 250,
      cell: {
        // prevent cell from being marked as dirty because it is not removed when item is saved (except if we collapse and expand parent node)
        // the row's x-dirty classname is correctly removed, but not the cell
        dirtyCls: 'none',
        checkable: this.getAllowCheck(),
        enableTri: false,
        autoCheckChildren: false,
        bind: {
          cls: 'c-row-hasfake-{record.hasFakeChild}'
        }
      }
    },{
      text: 'ID',
      dataIndex: 'id',
      hidden: true,
      width: 150
    }];
    if (this.getAllowDelete()) {
      columns.push({
        width: 40,
        resizable: false,
        cell: {
          tools: {
            remove: {
              iconCls: 'x-fa fa-trash',
              action: 'deleteItem',
              tooltip: 'Delete group...'
            }
          }
        }
      });
    }
    this.setColumns(columns);
    this.callParent(arguments);
  }
});