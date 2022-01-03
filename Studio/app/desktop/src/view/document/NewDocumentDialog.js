Ext.define('Studio.view.document.NewDocumentDialog', {
  extend: 'Ext.Panel',
  xtype: 'newdocumentdialog',
  userCls: ['c-document', 'c-new-document'],
  alias: 'widget.newdocumentdialog',
  padding: 10,
  requires: [
    'Ext.Toolbar',
    'Ext.Button'
  ],
  iconCls: 'x-fa fa-cubes',
  border: true,
  floated: true,
  modal: true,
  centered: true,
  closable: true,
  layout: 'vbox',
  showAnimation: null,
  defaultFocus: 'textfield[itemId=doclabel]',
  items: [{
    xtype: 'textfield',
    label: 'Label',
    itemId: 'doclabel',
    required: true,
    bind: '{record.label}',
    errorTarget: 'side',
    listeners: {
      errorchange: function(field, error) {
        var vm = this.up('panel').getViewModel();
        vm.set('hasError', !!error); 
      }
    }
  },
  {
    xtype: 'textfield',
    flex: 1,
    label: 'Description',
    bind: '{record.description}'
  }],
  buttons:{
    ok: {
      text: 'Add',
      userCls: 'c-save-btn',
      iconCls: 'x-fa fa-save',
      handler: function(btn) {
        var dialog = btn.up('panel');
        dialog.setMasked({
          xtype: 'loadmask',
          message: 'Saving record...'
        });
        dialog.getViewModel().get('record').save({
          failure: function(record, operation) {
            Ext.Msg.alert("Error", "An error occured while saving new record", Ext.emptyFn);
          },
          success: function(record, operation) {
            dialog.fireEvent('save', dialog, record);
          },
          callback: function(record, operation, success) {
            dialog.setMasked(false);
          }
        });
      },
      bind: {
        disabled: '{record.label == ""}'
      },
      tooltip: 'Add questionnaire and edit content'
    },
    cancel: {
      userCls: 'c-cancel-btn',
      iconCls: 'x-fa fa-times',
      handler: function(btn) {
        var view = btn.up('panel');
        view.fireEvent('cancel', view, view.getViewModel().get('record'));
      },
      tooltip: 'cancel creation'
    }
  }
});