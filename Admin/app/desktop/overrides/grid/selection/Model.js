Ext.define('Override.grid.selection.Model', {
  override: 'Ext.grid.selection.Model',
  select: function() {
    // prevent selection when selection model is disabled
    if (!this.getDisabled()) {
      this.callParent(arguments);
    }
  }
});