Ext.define('Admin.view.group.GroupsController', {
  extend: 'Admin.view.base.GridController',
  alias: 'controller.groups',
  control: {
    'groups': {
      drop: 'drop'
    }
  },
  expandAll: function() {
    this.getView().expandAll();
  },
  collapseAll: function() {
    this.getView().collapseAll();
  },
  drop: function(node, data, overModel, dropPosition, eOpts) {
    this.saveChanges(data.records);
    return true;
  },
  saveChanges: function(records) {
    if (records.length) {
      var record = records.shift();
      var parent = record.parentNode;
      var parentIri = parent.isRoot() ? null : parent.get('iri');
      if (parentIri != record.get('parent')) {
        this.getView().setMasked({
          xtype: 'loadmask',
          message: 'Saving record changes...'
        });
        record.set('parent', parentIri);
        record.save({
          failure: function(rec, operation) {
            this.getView().setMasked(false);
            var store = this.getViewModel().getStore('groups');
            Admin.util.ResponseHandler.handleResponse(operation, "Error", "An error occured while saving changes", store.reload, store);
          },
          success: function(rec, operation) {
            if (records.length) {
              this.saveChanges(records);
            } else {
              this.getView().setMasked(false);
            }
          },
          scope: this
        });
      }
    }
  }
});
