Ext.define('Admin.view.base.DialogModel', {
  extend: 'Ext.app.ViewModel',
  alias: 'viewmodel.dialog',
  data: {
    record: null,
    hideSaveCloseButton: false,
    hideButtons: false
  }
});