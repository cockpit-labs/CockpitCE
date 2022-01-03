Ext.define('Override.field.TimeView', {
  override: 'Ext.panel.TimeView',
  applyValue: function(value) {
    if (Ext.isDate(value)) {
      this.initDate = Ext.Date.format(value, 'm-d-Y');
    }
    return this.callParent(arguments);
  }
});