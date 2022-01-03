Ext.define('Override.field.Time', {
  override: 'Ext.field.Time',
  picker: {
    type: 'floated',
    xtype: 'timepanel',
    hourDisplayFormat: 'H'
  },
  parseValue: function(value, errors) {
    var realDateValue = this.getValue();
    // set initDate to real date
    if (realDateValue && Ext.isDate(realDateValue)) {
      this.initDate = Ext.Date.format(realDateValue, this.initDateFormat);
    }
    return this.callParent(arguments);
  }
});