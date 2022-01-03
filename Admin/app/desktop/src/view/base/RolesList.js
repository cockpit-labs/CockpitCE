Ext.define('Admin.view.base.RolesList', {
  extend: 'Ext.dataview.List',
  xtype: 'roleslist',
  alias: 'widget.roleslist',
  loadingText: 'Loading roles...',
  emptyText: 'No existing role',
  applyMasked: function(mask) {
    this.up().setMasked(mask);
  }
});