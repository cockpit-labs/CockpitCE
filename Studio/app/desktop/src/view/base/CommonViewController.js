Ext.define('Studio.view.base.CommonViewController', {
  extend: 'Studio.view.base.ViewController',
  requires: [
    'Ext.MessageBox'
  ],
  onSave: Ext.emptyFn,
  onActivate: function() {
    var list = this.lookupReference('itemsList');
    var store = list.getStore();
    if (!store || !store.isLoaded() || store.isLoading()) {
      list.on('refresh', this.selectFirstItem, this, {
        single: true
      });
    } else {
      this.selectFirstItem();
    }
    
    var view = this.getView();
    var route = view.getRoute();
    if (route) {
      if (typeof this[route] == 'function') {
        this[route].call(this);
      }
    }
  },
  onDeactivate: function() {
    var list = this.lookupReference('itemsList');
    var store = list.getStore();
    list.setPreviousSelectedItemIndex(list.getSelectable().getSelectionCount() ? store.indexOf(list.getSelectable().getSelectedRecord()) : -1);
    list.getSelectable().deselectAll();
  },
  reload: function() {
    Ext.Viewport.getController().reloadStores(this.getViewModel().getStore('list'));
  },
  itemTap: function(dataview, loc) {
    if (loc.event.getTarget('.c-delete-btn')) {
      loc.event.stopSelection = true;
      Ext.Msg.confirm("Confirmation", "Are you sure you want to delete this item ?<br/><b>" + loc.record.get('label') + '</b>', this.confirmRemove, {record:loc.record, ctrl: this});
    } else if (loc.event.getTarget('.c-clone-btn')) {
      loc.event.stopSelection = true;
      var clone = loc.record.copy(0);
      clone.phantom = true;
      this.getViewModel().getStore('list').add(clone);
      clone.set('label', clone.get('label') + ' (Copy)');
      this.lookupReference('itemsList').getSelectable().select(clone);
      setTimeout(() => {
        this.lookupReference('properties').getFields('label').focus(true);
      }, 200);
    }
  },
  confirmRemove: function(btnId) {
    var record = this.record;
    var me = this.ctrl;
    var store = record.store;
    if (btnId == 'yes') {
      if (!record.isPhantom()) {
        Ext.Viewport.setMasked({
          xtype: 'loadmask',
          message: 'Deleting record...'
        });
      }
      record.erase({
        failure: function(record, operation) {
          Ext.Msg.alert("Error", "An error occured while deleting record", Ext.emptyFn);
          store.load();
        },
        success: function(record, operation) {
          if (store.getCount()) {
            this.selectFirstItem();
          }
        },
        callback: function() {
          Ext.Viewport.setMasked(false);
        },
        scope: me
      });
    }
  },
  selectFirstItem: function(list) {
    var list = this.lookupReference('itemsList');
    var previousSelectedItemIndex = list.getPreviousSelectedItemIndex();
    var store = list.getStore();
    if (store && store.isLoaded() && !store.isLoading()) {
      var index = (previousSelectedItemIndex > -1) ? previousSelectedItemIndex : 0;
      if (store.getCount() > index) {
        list.getSelectable().select(store.getAt(0));
      }
    }
  },
  create: function() {
    var container = this.getView();
    if (container.fireEvent('beforecreateitem', container) !== false) {
      var store = this.getViewModel().getStore('list');
      var entityName = store.getModel().entityName;
      var displayName = entityName.substring(entityName.lastIndexOf('.') + 1);
      var tpl = Ext.create(entityName, {});
      store.on('add', function() {
        // set field after insert to make model dirty
        tpl.set('label', 'New ' + displayName);
        // force list selection
        this.lookupReference('itemsList').getSelectable().select(tpl);
        // try to focus field and select content
        var properties = this.lookupReference('properties');
        setTimeout(() => {
          properties.getFields('label').focus(true);
        }, 200);
      }, this, {single: true});
      store.insert(0,tpl);
    }
  },
  cancelProperties: function(btn) {
    var record = btn.up('formpanel').getRecord();
    if (record.isPhantom()) {
      var store = this.getViewModel().getStore('list');
      if (store.isChainedStore) {
        store = store.getSource();
      }
      if (store.getCount() > 1) {
        // auto select first element after removal
        store.on('remove', function() {
          // force list selection
          this.selectFirstItem();
        }, this, {single: true, delay: 300});        
      }
      store.remove(record);
    } else {
      record.reject();
    }
  },
  saveProperties: function(btn) {
    var record = btn.up('formpanel').getRecord();
    if (!record.isValid()) {
      var validation = record.getValidation();
      var messages = [];
      Ext.Object.eachValue(validation.getData(), function(value) {
        if (value !== true) {
          // means that we have a validation message
          messages.push(value);
        }
      });
      if (messages.length > 0) {
        Ext.Msg.alert("Validation failed", "Please fix following errors before saving : <br />- " + messages.join('<br />- '), Ext.emptyFn);
        return;
      }
    }
    Ext.Viewport.setMasked({
      xtype: 'loadmask',
      message: 'Saving record...'
    });
    record.save({
      success: this.onSave,
      failure: function() {
        Ext.Msg.alert("Error", "An error occured while saving record", Ext.emptyFn);
      },
      callback: function(record, operation, success) {
        Ext.Viewport.setMasked(false);
      },
      scope: this
    });
  },
  /**
   * useObject: opens a dialogbox to clone or reuse an existing object
   * @params options: {
      dialogTitle: 'Choose blocks',
      gridEmptyText: 'No block found',
      fromStore: 'blocks',
      filterSamples
      parentNode: node (used for tree)
      gridStoreType: 'blocks',
      clone: true,
      copy: false,
      filterSamples: (copy === true),
      callback: null
    }
    */
   /* function used by FoldersController and DocumentController */
  useObject: function(options) {
    var filteredIds = options.filteredIds;
    Ext.create({
      xtype: 'objectsreusedialog',
      title: options.dialogTitle || 'Choose objects',
      items: {
        // xtype: 'container',
        // layout: 'card',
        // shadow: true,
        // items: {
          xtype: 'objectgrid',
          // shadow: false,
          emptyText: options.gridEmptyText || 'No object found',
          deletable: false,
          iconable: false,
          store: {
            type: options.gridStoreType,
            filters: [
              function(item) {return filteredIds.indexOf(item.get('id')) === -1;},
              {
                id: 'SampleFilter',
                property: 'sample',
                operator: '=',
                value: false,
                disabled: !options.filterSamples
              }
            ]
          },
          items: {
            xtype: 'toolbar',
            docked: 'top',
            hidden: !options.filterSamples,
            items: ['->',{
              xtype: 'togglefield',
              name: 'sample',
              label: 'Show samples?',
              labelAlign: 'left',
              value: false,
              listeners: {
                change: this.onFilterSamples
              }
            }]
          },
          listeners: {
            disclose: function(record) {
              console.log(arguments);
            },
            scope: this
          }
        // }
      },
      listeners: {
        close: function(cmp) {
          this.addObjects(options.clone, options.copy, cmp.down('grid').getSelectable().getSelectedRecords(), 0, options.callback, options.scope);
        },
        scope: this
      }
    }).show();
  },
  addObjects: function(clone, copy, records, currentIndex, callback, scope) {
    if (records.length === 0) {
      return;
    }
    if ((clone === false && copy === false) || (currentIndex === (records.length))) {
      if (callback) {
        callback.call(scope || this, records);
      }
      Ext.Viewport.setMasked(false);
    } else {
      var record = records[currentIndex].copy(null);
      records[currentIndex] = record;
      if (copy) {
        // record is just being duplicated
        this.addObjects(clone, copy, records, currentIndex + 1, callback, scope);
      } else {
        // record is being cloned
        if (currentIndex == 0) {
          Ext.Viewport.setMasked({
            xtype: 'loadmask',
            message: 'Cloning records...'
          });
        }
        record.save({
          failure: function(record, operation) {
            Ext.Msg.alert("Error", "An error occured while saving record " + record.get('label'), this.reload, this);
          },
          success: function(record, operation) {
            this.addObjects(clone, copy, records, currentIndex + 1, callback, scope);
          },
          scope: this
        });
      }
    }
  },
  onFilterSamples: function(field, value) {
    var store = field.up('grid').getStore();
    var filters = store.getFilters();
    var filter = store.getFilters().getByKey('SampleFilter');
    filter.setValue(value);
    filters.itemChanged(filter, ['value']);
  }
});
