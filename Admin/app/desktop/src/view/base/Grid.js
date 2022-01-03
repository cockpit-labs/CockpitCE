Ext.define('Admin.view.base.Grid', {
  extend: 'Ext.grid.Grid',
  xtype: 'basegrid',
  userCls: 'basegrid',
  alias: 'widget.simplegrid',
  requires: [
    'Ext.panel.Header',
    'Ext.Toolbar',
    'Ext.field.Search'
  ],
  config: {
    filterEnabled: false
  },
  infinite: false,
  // no need grouping
  grouped: false,
  columnMenu: null,
  // There is no asymmetric data, we do not need to go to the expense of synching row heights
  syncRowHeight: false,
  selectable: {
    // headerCheckbox: true,
    // checkbox: true,
    // mode: 'multi'
    mode: 'single'
  },
  headerBorders: false,
  userSelectable: 'text',
  // enableColumnMove: false,
  // this config does not work with 7.1, hide Menu for each column
  // columnMenu: null,
  // so we set menu: null for all columns
  // headerContainer: {
  //   xtype: 'headercontainer',
  //   // give column default-settings in below defaults object
  //   defaults: {
  //     menu: null, // hide menu for each column
  //     sortable: false
  //   }
  // },
  // selectable: {
  //   mode: 'single'
  // },
  shadow: true,
  bind: '{main}',
  initialize: function() {
    var toolbarItems = [{
      xtype: 'button',
      text: 'Add',
      iconCls: 'x-fa fa-plus-circle',
      handler: 'addItem',
      tooltip: 'Create'
    }];
    if (this.getFilterEnabled()) {
      toolbarItems.push({
        xtype:'searchfield',
        label: 'Search users:',
        labelAlign: 'left',
        labelTextAlign: 'right',
        listeners: {
          buffer: 500,
          change: 'filterItems'
        }
      });
    }
    this.add({
      xtype: 'toolbar',
      docked: 'top',
      items: toolbarItems
    });
    this.callParent(arguments);
  },
  titleBar: {
    xtype: 'panelheader',
    items:[{
      xtype: 'tool',
      userCls: 'refresh-tool',
      type: 'refresh',
      handler: 'reload',
      tooltip: 'Refresh list...'
    }]
  },
  // listeners: {
  //   checkchange: 'updateItem'
  //   // function(col, rowIndex, checked, record) {
  //   //   debugger;
  //   // }
  // }
});