Ext.define('Studio.view.block.BlockTree', {
  extend: 'Ext.tree.Tree',
  xtype: 'blocktree',
  alias: 'widget.blocktree',
  requires: [
    'Ext.data.TreeStore',
    'Ext.grid.Tree',
    'Ext.grid.plugin.TreeDragDrop',
//    'Studio.ux.grid.BlockTreeRow'
  ],
  plugins: {
    treedragdrop: true
  },
  displayField: 'label',
  itemConfig: {
    viewModel: true
  },
  userCls: 'blockstree',
  emptyText: 'No existing block found in this questionnaire',
  rowLines: true,
  columnLines: true,
  infinite: false,
  striped: true,
  markDirty: true,
  selectable: {
    mode: 'single'
  },
  columns: [{
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
      bind: {
        cls: 'c-row-hasfake-{record.hasFakeChild}'
      }
    }
  }, {
    width: 92,
    resizable: false,
    cell: {
      tools: {
        up: {
          iconCls: 'x-fa fa-arrow-up',
          action: 'moveUpItem',
          tooltip: 'Move up item'
        },
        down: {
          iconCls: 'x-fa fa-arrow-down',
          action: 'moveDownItem',
          tooltip: 'Move down item'
        },
        remove: {
          iconCls: 'x-fa fa-trash',
          action: 'removeItem',
          tooltip: 'Delete item'
        }
      },
      bind: {
        cls: 'c-cell-isfake-{record.fake}'
      }
    }
  }]
});
