/**
 * @class Admin.ux.FieldLabelEditor
 * @version 1.1
 * An editor for field label
 **/
 Ext.define('Admin.ux.FieldLabelEditor', {
  extend: 'Ext.Editor',
  xtype: 'fieldlabeleditor',
  config: {
    field: {
      name: 'labelfield',
      allowBlank: false,
      xtype: 'textfield',
      selectOnFocus: true,
      enforceMaxLength: true
    }
  },
  offset: [-35, 0],
  shadow: false,
  completeOnEnter: true,
  cancelOnEsc: true,
  updateEl: true,
  ignoreNoChange: true,
  alignment: 'l-l',
  realign: function() {
    var me = this;
    me.getField().setWidth(me.boundEl.getWidth() + 35);
    me.callParent(arguments);
  }
});