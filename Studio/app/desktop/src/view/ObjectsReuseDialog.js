Ext.define('Studio.view.ObjectsReuseDialog', {
  extend: 'Ext.Dialog',
  requires: [],
  xtype: 'objectsreusedialog',
  alias: 'widget.objectsreusedialog',
  titleBar: {
    innerCls: 'x-fa fa-tasks'
  },
  closable: false,
  dismissHandler: true,
  maximizable: true,
  height: '70%',
  width: '85%',
  layout: 'fit',
  buttonAlign: 'center',
  buttons: {
    ok: {
      handler: function() {
        this.up('dialog').close();
      },
      tooltip: 'Add selected items'
    },
    cancel: {
      handler: function() {
        var dialog = this.up('dialog');
        var selectable = dialog.down('grid').getSelectable().deselectAll();
        dialog.close();
      },
      scope: 'this',
      tooltip: 'Close dialog'
    }
  }
});