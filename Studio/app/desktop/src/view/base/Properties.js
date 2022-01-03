Ext.define('Studio.view.base.Properties', {
  extend: 'Ext.form.Panel',
  xtype: 'baseproperties',
  cls: 'c-properties',
  alias: 'widget.baseproperties',
  buttonAlign: 'center',
  buttons: [{
    text: 'Cancel',
    userCls: 'c-cancel-btn',
    ui: 'action',
    handler: 'cancelProperties',
    bind: {
      disabled: '{!record.dirty}'
    },
    tooltip: 'Revert changes'
  },{
    text: 'Save',
    userCls: 'c-save-btn',
    ui: 'action',
    handler: 'saveProperties',
    bind: {
      disabled: '{!record.dirty || hasError}'
    },
    tooltip: 'Save changes'
  }]
});