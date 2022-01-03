Ext.define('Admin.view.base.DialogController', {
  extend: 'Ext.app.ViewController',
  alias: 'controller.dialogcontroller',
  control: {
    // 'dialog button[action=debug]': {
    //   tap: 'onDebug'
    // },
    // 'dialog button[action=revert]': {
    //   tap: 'onRevert'
    // },
    'dialog button[action=cancel]': {
      tap: 'onCancel'
    },
    'dialog button[action=save]': {
      tap: 'onSave'
    },
    'dialog button[action=saveclose]': {
      tap: 'onSaveClose'
    },
    'basedialog': {
      beforeclose: 'beforeCloseDialog'
    },
    'basedialog tabpanel': {
      activeitemchange: 'onActiveTabChanged'
    }
  },
  beforeCloseDialog: function(dialog, ev) {
    if (ev == null) {
      // we have closed dialog from Cancel/Save button or after user confirmation, so let's do it
      return true;
    } else {
      // coming from dismiss handler (ESC, close button, ...)
      var record = dialog.getViewModel().get('record');
      if (record.isPhantom() || !record.isDirty()) {
        this.onCancel();
      } else {
        var msgAlert = Ext.Msg.show({
          title: 'Save changes?',
          message: 'Your modifications may be lost. Do you still want to close dialog?',
          minWidth:350,
          prompt: false,
          buttons:[{
            text: 'Don\'t save',
            iconCls: 'md-icon-undo',
            itemId: 'reject'
          },{
            text: 'Cancel',
            iconCls: 'x-fa fa-times',
            itemId: 'donothing'
            // itemId: 'cancel' /* Do not use cancel, it is used internally by MsgBox */
          },{
            text: 'Save',
            iconCls: 'x-fa fa-save',
            itemId: 'save'
          }],
          keyMap: {
            // prevent from closing MSgBox since the ESCAPE event may be fired from Dialog ESC key
          },
          scope: this,
          fn: function(btn) {
            if (btn == 'reject') {
              this.onCancel();
            } else if (btn == 'save') {
              this.onSaveClose();
            }
            msgAlert.close();
          }
        });
        msgAlert.setAlwaysOnTop(true);
      }
      return false;
    }
  },
  // onDebug: function() {
  //   debugger;
  // },
  // onRevert: function() {
  //   var dialog = this.getView();
  //   var vm = dialog.getViewModel();
  //   var record = vm.get('record');
  //   record.reject();
  // },
  onCancel: function() {
    var dialog = this.getView();
    var vm = dialog.getViewModel();
    var record = vm.get('record');
    if (record.isPhantom()) {
      record.destroy();
    } else if (record.isDirty()) {
      record.reject();
    }
    // prevent erreur with GroupTreeList selection binding (setting parent = null on null data)
    vm.set('record', null);
    vm.notify();
    // 
    dialog.close();
  },
  onSave: function() {
    var dialog = this.getView();
    if (dialog.down('tabpanel')) {
      // we do not close dialog if the dialog is for modifications (not creation)
      dialog.fireEvent('save', dialog, dialog.getViewModel().get('record'), false);
    } else {
      this.onSaveClose();
    }
  },
  onSaveClose: function() {
    var dialog = this.getView();
    dialog.fireEvent('save', dialog, dialog.getViewModel().get('record'), true);
  },
  onActiveTabChanged: function(panel, tab) {
    // just hide buttons when in properties tab
    panel.lookupViewModel().set('hideButtons', !tab.is('properties'));
  },
  /* this function is only used within GroupDialog */
  onGroupAttributesUpdate: function(store, attribute, type) {
    if (type == Ext.data.Model.REJECT) {
      // do nothing when event is fired for a rejected record
      // else we would recalculate positions
      return;
    } else {
      var newAttributes = [];
      // var doCloneChoices = !record.get('clonedChoices');
      store.each(function(rec, index) {
        // var clone = doCloneChoices ? rec.clone() : rec;
        var data = rec.getData();
        data.position = index + 1;
        delete data['id'];
        newAttributes.push(data);
      });
      this.getViewModel().get('record').set('attributes', newAttributes);
    }
  }
});
