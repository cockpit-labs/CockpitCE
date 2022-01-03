Ext.define('Studio.view.folder.FoldersController', {
  extend: 'Studio.view.base.CommonViewController',
  alias: 'controller.folders',
  requires: [
    'Studio.view.document.DocumentDialog',
    'Studio.view.document.NewDocumentDialog',
    'Ext.Dialog',
    'Ext.Array',
    'Studio.view.calendar.CalendarProperties',
    'Studio.model.Document',
    'Ext.data.Session'
  ],
  control: {
    'folders': {
      activate: 'onActivate',
      deactivate: 'onDeactivate',
      beforecreateitem: 'onBeforeCreateFolder'
    },
    'chipview': {
      select: 'selectCalendar'
    },
    'documents': {
      select: 'selectDocument',
      drop: 'onDocumentReorder'
    },
    'menuitem[action=createDocument]': {
      click: 'createDocument'
    },
    'menuitem[action=useDocument]': {
      click: 'useDocument'
    },
    'menuitem[action=cloneDocument]': {
      click: 'cloneDocument'
    },
    'tool[action=removeDocument]': {
      click: 'removeDocument'
    },
    'button[action=createPermission]': {
      tap: 'createPermission'
    }
  },
  modalDialog: null,
  destroy: function() {
    if (this.modalDialog !== null) {
      this.modalDialog.destroy();
    }
    this.callParent(arguments);
  },
  /* overrides function to revert permission changes */
  cancelProperties: function(btn) {
    var vm = this.getViewModel();
    // reject permissions changes
    vm.getStore('permissions').rejectChanges();
    this.callParent(arguments);
  },
  onBeforeCreateFolder: function() {
    var calendarsStore = Ext.getStore('Calendars');
    var calendarsCount = calendarsStore.getCount();
    if (calendarsCount == calendarsStore.getPhantomRecords().length) {
      Ext.Msg.alert('Warning', 'Create at least one calendar before creating a new folder', function() {
        this.redirectTo('calendars' + (!calendarsCount ? '/create':''));
      }, this);
      return false;
    }
    return true;
  },
  maxFoldersValidation: function(value) {
    var record = this.getViewModel().get('record');
    if (record) {
      var minFolders = this.getView().down('formpanel').getFields('minFolders').getValue();
      if (minFolders > value) {
        return 'Maximum should at least equal minimum';
      }
    }
    return true;
  },
  onChangeMinFolders: function(field) {
    field.up('formpanel').getFields('maxFolders').validate();
  },
  onDocumentReorder: function(node, data, targetRecord, pos) {
    this.syncFolderDirty('questionnaireTpls', data.view.store);
  },
  selectCalendar: function(view, records) {
    var calendar = records[0];
    var dismissHandler = function() {
      view.deselectAll();
      dialog.destroy();
    };
    var dialog = Ext.create({
      xtype: 'dialog',
      title: calendar.get('label'),
      closable: true,
      maximizable: true,
      height: 405,
      width: 255,
      viewModel: {
        data: {
          record: calendar
        }
      },
      items: {
        xtype: 'calendarproperties',
        disabled: true,
        buttons: null
      },
      dismissHandler: dismissHandler,
      buttons: {
        close: dismissHandler
      }
    }).show();
  },
  selectDocument: function(grid, record) {
    var data = {
        currentDocument: record,
        hasError: !record.isValid()
      };
      var hasModalDialog = (this.modalDialog != null);
      if (!hasModalDialog) {
        this.modalDialog = Ext.create({
          xtype: 'documentdialog',
          modelValidation: true,
          //session: new Ext.data.Session(),
          height: '96%',
          width: '96%',
          bind: {
            title: '{currentDocument.label}'
          },
          listeners: {
            savenew: function(dialog, newrecord) {
              var store = this.getViewModel().getStore('questionnaires');
              store.add(newrecord);
              this.syncFolderDirty('questionnaireTpls', store);
            },
            scope:this
          }
        });
      }
      var vm = this.modalDialog.lookupViewModel();
      vm.set(data);
      vm.notify();

      if (hasModalDialog) {
        if (!record.isPhantom()) {
          var treeStore = this.modalDialog.lookupViewModel().getStore('blocks');
          // binding for store property does not work
          treeStore.defaultRootId = record.get('id');
          // reload tree
          treeStore.load();
        }
      }
      this.modalDialog.show().focus(true);
    },
  createDocument: function(btn) {
    var newDoc = Ext.create('Studio.model.Document', {
      label: 'New Questionnaire'
    });
    Ext.create({
      xtype: 'newdocumentdialog',
      viewModel: {
        data: {
          record: newDoc
        }
      },
      height: 250,
      width: 350,
      title: 'New Questionnaire',
      listeners: {
        cancel: function(dialog, newrecord) {
          dialog.close();
        },
        save: function(dialog, newrecord) {
          var store = this.getViewModel().getStore('questionnaires');
          var records = store.add(dialog.getViewModel().get('record'));
          this.syncFolderDirty('questionnaireTpls', store);
          dialog.close();
          this.selectDocument(null, records[0]);
        },
        scope:this
      }
    }).show().focus(true);
  },
  removeDocument: function(tool, el, cell) {
    var store = this.getViewModel().getStore('questionnaires');
    store.remove(cell.getRecord());
    this.syncFolderDirty('questionnaireTpls', store);    
  },
  createPermission: function(btn) {
    var grid = btn.up('grid');
    var permission = Ext.create('Studio.model.Permission', {});
    grid.getStore().add(permission);
    grid.up('folderproperties').getScrollable().scrollTo(null, Infinity);
    grid.scrollToRecord(permission);
  },
  onPermissionUpdate: function(store, record, type) {
    if (type == Ext.data.Model.REJECT) {
      // do nothing when event is fired for a rejected record
      // else we would recalculate positions
      return;
    }
    var record = this.getViewModel().get('record');
    record.set('permissions', store.getRange());
    // try to force permission validation in order to make Save button disabled
    // record.isValid();
  },
  removePermission: function(grid, opts) {
    opts.record.drop();
    this.syncFolderDirty('permissions', grid.getStore());
  },
  syncFolderDirty: function(property, store) {
    var folder = this.getViewModel().get('record');
    var elements = folder.get(property);
    // just check if ids have changed
    var initialIds = Ext.Array.pluck(elements, 'id');
    if (!Ext.Array.equals(store.collect('id'), initialIds)) {
      folder.set(property, Ext.Array.pluck(store.data.items, 'data'));
    }
  },
  cloneDocument: function(btn, ev) {
    this.useDocument(btn, ev, true);
  },
  afterUseDocument: function(records) {
    this.getViewModel().getStore('questionnaires').add(records);
    this.syncFolderDirty('questionnaireTpls', this.getViewModel().getStore('questionnaires'));
  },
  useDocument: function(btn, ev, clone) {
    this.useObject({
      dialogTitle: 'Choose questionnaires',
      gridEmptyText: 'No questionnaire found',
      gridStoreType: 'documents',
      clone: (clone === true),
      copy: false,
      filterSamples: (clone === true),
      filteredIds: (clone === true) ? [] : this.getViewModel().getStore('questionnaires').collect('id'),
      callback: this.afterUseDocument
    });
    // var store = this.getViewModel().getStore('questionnaires');
    // var selectedIds = (clone === true) ? [] : store.collect('id');
    // var dialog = Ext.create({
    //   xtype: 'objectsreusedialog',
    //   title: 'Choose questionnaires',
    //   items: {
    //     xtype: 'objectgrid',
    //     emptyText: 'No document found',
    //     store: {
    //       type: 'documents',
    //       filters: [function(item) {return selectedIds.indexOf(item.get('id')) === -1;}]
    //     }
    //   },
    //   listeners: {
    //     close: function(cmp) {
    //       // TODO later : we should be able to get ordered selections, but need to use privates things :
    //       // cmp.down('grid').getSelectable().getSelection().getSelected().indices or .map

    //       // we are using selection even if not ordered :
    //       this.addDocuments(clone, store, cmp.down('grid').getSelectable().getSelectedRecords(), 0);
    //     },
    //     scope: this
    //   }
    // }).show();
  },
  // addDocuments: function(clone, store, records, currentIndex) {
  //   if (records.length === 0) {
  //     return;
  //   }
  //   if ((clone !== true) || (currentIndex === (records.length))) {
  //     store.add(records);
  //     this.syncFolderDirty('questionnaireTpls', store);
  //     Ext.Viewport.setMasked(false);
  //   } else {
  //     if (currentIndex == 0) {
  //       Ext.Viewport.setMasked({
  //         xtype: 'loadmask',
  //         message: 'Cloning records...'
  //       });
  //     }
  //     // record is being cloned
  //     var record = records[currentIndex].copy(null);
  //     records[currentIndex] = record;
  //     record.save({
  //       failure: function(record, operation) {
  //         Ext.Msg.alert("Error", "An error occured while saving record " + record.get('label'), this.reload, this);
  //       },
  //       success: function(record, operation) {
  //         this.addDocuments(clone, store, records, currentIndex + 1);
  //       },
  //       scope: this
  //     });
  //   }
  // },
  permissionRenderer: function (value, record, dataIndex, cell, column) {
    if (value == null) {
      return value;
    }
    var editor = column.getEditor(record);
    var field = Ext.ComponentQuery.is(editor,'selectfield') ? editor : editor.getField();
    var storeRecord = field.getStore().findRecord(field.getValueField(), value);
    var newValue = storeRecord ? storeRecord.get(field.getDisplayField()) : null;
    cell.toggleCls('c-permission-invalid', !newValue);
    var tpl = field.getDisplayTpl();
    if (newValue !== null && tpl) {
      newValue = tpl.apply(storeRecord.data);
    }
    return newValue;
  },
  onSave: function(record) {
    var vm = this.getViewModel();
    // change binding forcePermissionsRefresh for Permission grid store refresh
    vm.set('forcePermissionsRefresh', vm.get('forcePermissionsRefresh') + 1);
  }
});
