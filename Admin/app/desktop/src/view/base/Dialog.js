Ext.define('Admin.view.base.Dialog', {
  extend: 'Ext.Dialog',
  xtype: 'basedialog',
  userCls: 'basedialog',
  alias: 'widget.basedialog',
  requires: [
    'Ext.panel.Resizable',
    'Ext.panel.Resizer'
  ],
  controller: 'dialogcontroller',
  layout: 'fit',
  defaultFocus: 'textfield[itemId=firstField]',
  border: true,
  floated: true,
  modal: true,
  centered: true,
  closable: true,
  showAnimation: null,
  maximizable: true,
  resizable: {
    edges: 'all'
  },
  buttonAlign: 'center',
  buttonToolbar: {
    bind: {
      hidden: '{hideButtons}'
    }
  },
  buttons: [{
    text: 'Save',
    iconCls: 'x-fa fa-check',
    action: 'save',
    bind: {
      disabled: '{!record.valid || !record.dirty}'
    },
    weight: 300,
    tooltip: 'Save'
  }, {
    text: 'Save & close',
    iconCls: 'x-fa fa-check-square',
    action: 'saveclose',
    bind: {
      disabled: '{!record.valid || !record.dirty}',
      hidden: '{hideSaveCloseButton}'
    },
    weight: 200,
    tooltip: 'Save and close dialog'
  }, {
    text: 'Cancel',
    iconCls: 'x-fa fa-times',
    action: 'cancel',
    tooltip: 'Cancel'
  // }, {
  //   text: 'Debug',
  //   iconCls: 'x-fa fa-times',
  //   action: 'debug'
  // }, {
  //   text: 'Revert',
  //   iconCls: 'x-fa fa-times',
  //   action: 'revert'
  }]
});