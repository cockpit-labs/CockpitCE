Ext.define('Studio.view.base.SimpleGrid', {
  extend: 'Ext.grid.Grid',
  xtype: 'simplegrid',
  userCls: 'c-simplegrid',
  alias: 'widget.simplegrid',
  infinite:false,
  // no need grouping
  grouped: false,
  enableColumnMove: false,
  // this config does not work with 7.1, hide Menu for each column
  columnMenu: null,
  // so we set menu: null for all columns
  headerContainer: {
    xtype: 'headercontainer',
    // give column default-settings in below defaults object
    defaults: {
      menu: null, // hide menu for each column
      sortable: false
    }
  },
  selectable: {
    mode: 'single'
  },
  shadow: true
});