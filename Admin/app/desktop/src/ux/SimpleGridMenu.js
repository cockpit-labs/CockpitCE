/**
 * @class Admin.ux.SimpleGridMenu
 * @version 1.1
 * A simple grid menu with no grouping item
 **/
 Ext.define('Admin.ux.SimpleGridMenu', {
  extend: 'Ext.menu.Menu',
  xtype: 'simplegridmenu',
  config: {
    weighted: true,
    align: 'tl-bl?',
    hideOnParentHide: false,  // Persists when owning Column is hidden
  },
  items: {
    sortAsc: {
      xtype: 'gridsortascmenuitem',
      group: 'sortDir',
      weight: -100 // Wants to be the first
    },
    sortDesc: {
      xtype: 'gridsortdescmenuitem',
      group: 'sortDir',
      weight: -90 // Wants to be the second
    }
  }
});