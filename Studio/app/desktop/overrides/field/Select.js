Ext.define('Override.field.Select', {
  override: 'Ext.field.Select',
  privates: {
    syncEmptyState: function() {
      var me = this;
      me.callParent();
      // Ext.field.Text does not do the correct check when toggling emptyCls
      me.toggleCls(me.emptyCls, !me.hasValue());
    }
  }
});