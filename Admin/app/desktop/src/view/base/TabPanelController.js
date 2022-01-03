Ext.define('Admin.view.base.TabPanelController', {
  extend: 'Ext.app.ViewController',
  alias: 'controller.tabpanelcontroller',
  control: {
    'basetabpanel': {
      beforeactiveitemchange: 'beforeActiveTabChange'
    }
  },
  beforeActiveTabChange: function(tabpanel, tab) {
    var record = this.getViewModel().get('record');
    if (record.isDirty()) {
      var dialog = this.getView().up('dialog');
      // disable ESC key otherwise dialog gets closed and MsgBox will still be in DOM (memory leak)
      dialog.setKeyMapEnabled(false);
      var msgAlert = Ext.Msg.show({
        title: 'Save modifications ?',
        message: 'Current modifications need to be saved or reverted',
        buttons:[{
          text: 'Revert',
          iconCls: 'md-icon-undo',
          itemId: 'revert'
        },{
          text: 'Save',
          iconCls: 'x-fa fa-save',
          itemId: 'save'
        }],
        scope: this,
        fn: function(btn) {
          dialog.setKeyMapEnabled(true);
          if (btn == 'save') {
            dialog.fireEvent('save', dialog, record, false, tabpanel.setActiveItem.bind(tabpanel, tab));
          } else {
            record.reject();
            tabpanel.setActiveItem(tab);
          }
        }
      });
      msgAlert.setAlwaysOnTop(true);
      return false;
    }
  },
  onActiveTabChanged: function(tabpanel, tab) {
    if (tab.getItemId() == 'groups') {
      this.getViewModel().getStore('groups').getRoot().expand(
        false,
        this.checkGroups,
        this
      );
    }
  },
  checkGroups: function() {
    var vm = this.getViewModel();
    var store = vm.getStore('groups');
    var user = vm.get('record');
    var groups = user.get('groups');
    Ext.each(groups, function(item) {
      var node = store.findNode('iri', item);
      if (!node) {
        console.error("Group with id " + item + " not found in groups list");
      } else {
        node.set('checked', true);
        var nodesToExpand = [];
        while (!(node = node.parentNode).isRoot()) {
          nodesToExpand.push(node);
        }
        this.expandNodes(nodesToExpand);
      }
    }, this);
  },
  /**
   * expand a list of nodes, starting from the end of the array
   * @param nodes 
   */
  expandNodes: function(nodes) {
    if (nodes.length > 0) {
      var node = nodes.pop();
      node.expand(false, this.expandNodes.bind(this, nodes));
    }
  },
  addItemRole: function(role, successCallback, scope) {
    var record = this.getViewModel().get('record');
    // need to clone array else it is the same object as in modifiedProperties and so this property seems to be unchanged
    this.saveRoleChanges(record, Ext.Array.push(Ext.clone(record.get('roles')), role.get('iri')), successCallback, scope);
  },
  removeItemRole: function(role, successCallback, scope) {
    var item = this.getViewModel().get('record');
    // need to clone array else it is the same object as in modifiedProperties and so this property seems to be unchanged
    this.saveRoleChanges(item, Ext.Array.remove(Ext.clone(item.get('roles')), role.get('iri')), successCallback, scope);
  },
  saveRoleChanges: function(record, roles, successCallback, scope) {
    var view = this.getView();
    view.setMasked({
      xtype: 'loadmask',
      message: 'Saving record...'
    });
    record.set('roles', roles);
    record.save({
      failure: function(record, operation) {
        Admin.util.ResponseHandler.handleResponse(operation, 'Error', 'An error occured while saving changes');
        record.reject();
      },
      success: successCallback|| Ext.emptyFn,
      callback: function(record, operation, success) {
        view.setMasked(false);
      },
      scope: scope || this
    });
  }
});
