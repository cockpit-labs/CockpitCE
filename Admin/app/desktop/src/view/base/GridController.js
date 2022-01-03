Ext.define('Admin.view.base.GridController', {
  extend: 'Ext.app.ViewController',
  alias: 'controller.gridcontroller',
  control: {
    'grid': {
      'select': 'editItem'
    },
    'tool[action=deleteItem]': {
      click: 'deleteItem'
    }
  },
  reload: function() {
    var store = this.getView().getStore();
    if (store.isTreeStore && !store.getRoot().isExpanded()) {
      store.getRoot().expand();
    } else {
      if (store.type == 'chained') {
        store = store.getSource();
      }
      store.load();
    }
  },
  updateItem: function(cell, rowIndex, checked, viewrecord) {
    var view = this.getView();
    view.setMasked({
      xtype: 'loadmask',
      message: 'Saving record...'
    });
    viewrecord.save({
      failure: function(record, operation) {
        Admin.util.ResponseHandler.handleResponse(operation, "Error", "An error occured while saving record");
        viewrecord.reject();
      },
      success: function(record, operation) {},
      callback: function(record, operation, success) {
        view.setMasked(false);
      },
      scope: this
    });
  },
  addItem: function(btn) {
    var store = this.getView().getStore();
    var model = store.getModel();
    Ext.create({
      xtype: 'basedialog',
      iconCls: 'x-fa fa-plus-circle',
      // modelValidation: true,
      //session: this.getSession(),
      title: 'New ' + model.entityName,
      // height: 400,
      width: 800,
      viewModel: {
        // do not use Dialog viewmodel since it declares stores that useless for creation
        // type: 'dialog',
        data: {
          record: Ext.create(model.getName()),
          hideSaveCloseButton: true,
          hideButtons: false,
          superuser: Admin.util.State.get('superuser') == '1'
        }
      },
      items: {
        xtype: model.entityName.toLowerCase() + 'properties',
        // this is only used by group creation
        showParentGroupTree: true
      },
      listeners: {
        save: function(dialog, newrecord) {
          dialog.setMasked({
            xtype: 'loadmask',
            message: 'Saving record...'
          });
          newrecord.save({
            failure: function(record, operation) {
              Admin.util.ResponseHandler.handleResponse(operation, "Error", "An error occured while saving new record");
            },
            success: function(record, operation) {
              dialog.close();
              var grid = this.getView();
              var store = grid.getStore();
              if (store.isTreeStore) {
                this.addGroup(record, store, function(parent) {
                  parent.appendChild(record);
                  // tree store is not sorted when appending child
                  store.sort();
                  grid.scrollToRecord(record);
                  grid.getSelectable().select(record, false, true);
                  Ext.Anim.run(grid.getViewItems()[store.indexOf(record)].el, 'pop', {duration:500});
                }, this);
              } else {
                store.add(record);
                grid.scrollToRecord(record);
                grid.getSelectable().select(record, false, true);
                Ext.Anim.run(grid.getViewItems()[store.indexOf(record)].el, 'pop', {duration:500});
              }
            },
            callback: function(record, operation, success) {
              dialog.setMasked(false);
            },
            scope: this
          })
          
        },
        scope:this
      }
    })
    .show()
    .focus(true);
  },
  editItem: function(grid, selection) {
    var store = this.getView().getStore();
    var model = store.getModel();
    // reload User
    var view = this.getView();
    view.setMasked({
      xtype: 'loadmask',
      message: 'Loading ' + model.entityName + '...'
    });
    selection.load({
      scope: this,
      success: function(record, operation) {
        Ext.create({
          xtype: 'basedialog',
          iconCls: 'x-fa fa-pen',
          modelValidation: true,
          //session: this.getSession(),
          title: 'Edit ' + model.entityName.toLowerCase() + ' ' + record.getLabel(),
          height: 450,
          width: 800,
          viewModel: {
            type: model.entityName.toLowerCase() + 'dialog',
            data: {
              record: selection
            }
          },
          items: {
            xtype: model.entityName.toLowerCase() + 'tabpanel'
          },
          listeners: {
            save: function(dialog, editedRecord, close, callback, scope) {
              dialog.setMasked({
                xtype: 'loadmask',
                message: 'Saving record...'
              });
              // if (model.entityName == 'Group') {
              //   var attrs = editedRecord.get('attributes');
              //   if (attrs) {
              //     for (var i=0;i<attrs.length;i++) 
              //     {
              //       delete attrs[i]['id'];
              //     }
              //   }
              // }
              editedRecord.save({
                failure: function(record, operation) {
                  Admin.util.ResponseHandler.handleResponse(operation, "Error", "An error occured while saving record");
                },
                success: function(record, operation) {
                  if (callback) {
                    Ext.callback(callback, scope || this);
                  }
                  if (close === true) {
                    dialog.close();
                    Ext.Anim.run(grid.getViewItems()[store.indexOf(record)].el, 'pop', {duration:500});
                  }
                },
                callback: function(record, operation, success) {
                  dialog.setMasked(false);
                },
                scope: this
              })
            },
            scope:this
          }
        })
        .show()
        .focus(true);
      },
      callback: function(record, operation, success) {
        view.setMasked(false);
      }
    });
  },
  deleteItem: function(tool, el, cell) {
    var record = cell.getRecord();
    Ext.Msg.confirm("Confirmation", "Are you sure you want to delete this item ?<br/><b>" + record.get(record.labelField) + '</b>', this.confirmDeleteItem, {record:record, scope: this});
  },
  confirmDeleteItem: function(btn) {
    if (btn == 'yes') {
      var args = this;
      var view = args.scope.getView();
      view.setMasked({
        xtype: 'loadmask',
        message: 'Deleting item ...'
      });
      args.record.erase({
        callback: function(record, operation, success) {
          view.setMasked(false);
        }
      });
    }
  },
  addGroup: function(record, store, callback, scope) {
    var path = record.get('idPath').split('/');
    if (path.length) {
      path.pop();
    }
    this.expandItems(store, path, callback, scope);
  },
  expandItems: function(store, itemsId, callback, scope, lastNode) {
    if (itemsId) {
      if (itemsId.length) {
        var id = itemsId.shift();
        if (id) {
          var node = store.getById(id);
          if (node) {
            node.expand(false, this.expandItems.bind(this, store, itemsId, callback, scope, node));
          }
        } else {
          this.expandItems(store, itemsId, callback, scope, lastNode);
        }
      } else {
        callback.call(this, lastNode || store.getRoot());
      }
    }
  }
});
