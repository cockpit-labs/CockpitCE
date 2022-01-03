Ext.define('Admin.store.Groups', {
  extend: 'Ext.data.TreeStore',
  alias: 'store.groups',
  model: 'Admin.model.Group',
  autoLoad: false,
  sorters: ['label'],
  filters: [],
  // storeId: 'Groups',
  trackRemoved: false,
  clearRemovedOnLoad: false, // prevent nullpointer in TreeStore.flushLoad (TreeStore.js:1858)
  parentIdProperty: 'parentId',
  root: {
    label: 'ROOT',
    loaded: false,
    expanded: false
  },
  listeners: {
    update: function(store, record, operation, modifiedFieldNames) {
      var me = this;
      var fieldsMap = record.fieldsMap;
      if (modifiedFieldNames) {
        for (var i = 0; i < modifiedFieldNames.length; i ++) {
          var fieldName = modifiedFieldNames[i];
          var field = fieldsMap[fieldName];
          if (field && field.isArrayField) {
            var newValue = record.get(fieldName);
            if (newValue === record.previousValues) {
              if (Ext.isDefined(newValue)) {
                // ne
              }
            }
          }
        }
      }
      
        //     var field = fieldsMap[name];
        //     if (field && field.isArrayField) {
        //       newModifiedArrayFields[name] = true;
        //     }
        //   }
    }
  }
});