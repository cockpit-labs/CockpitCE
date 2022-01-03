Ext.define('Admin.view.user.UserTabPanelController', {
  extend: 'Admin.view.base.TabPanelController',
  alias: 'controller.usertabpanelcontroller',
  control: {
    'usertabpanel': {
      activeitemchange: 'onActiveTabChanged'
    },
    'usertabpanel groupstree': {
      checkchange: 'saveGroupChanges'
    }
  },
  handleUserRoles: function(allRoles, user) {
    var rolesIds = Ext.Array.pluck(allRoles, 'id');
    var roles = [];
    var userRoleIds = '';
    Ext.each(user.get('roles'), function(value) {
      var found = rolesIds.indexOf(value.substring(value.lastIndexOf('/') + 1));
      if (found != -1) {
        var role = allRoles[found];
        userRoleIds += role.get('id') + ';';
        roles.push({
          id: role.get('id'),
          name: role.get('name'),
          iri: role.get('iri')
        });
      } else {
        // something went wrong. User has a not known role
      }
    });
    var effectiveRoles = [];
    Ext.each(user.get('effectiveRoles'), function(value) {
      var found = rolesIds.indexOf(value.substring(value.lastIndexOf('/') + 1));
      if (found != -1) {
        var role = allRoles[found];
        effectiveRoles.push({
          id: role.get('id'),
          name: role.get('name'),
          cls: userRoleIds.indexOf(role.get('id')) == -1 ? 'usereffectiverole' : 'userrole'
        });
      } else {
        // something went wrong. User has a not known role
      }
    });
    return {
      roles: roles,
      effectiveRoles: effectiveRoles
    };
  },
  refreshUserRoleStores: function() {
    var vm = this.getViewModel();
    var record = vm.get('record');
    var data = this.handleUserRoles(Ext.getStore('Roles').getRange(), record);
    vm.getStore('userRoles').loadData(data.roles);
    vm.getStore('userEffectiveRoles').loadData(data.effectiveRoles);
  },
  onActiveTabChanged: function(tabpanel, tab) {
    var vm = this.getViewModel();
    if (tab.getItemId() == 'roles') {
      Ext.getStore('Roles').load({
        callback: function(records, operation, success) {
          if (success) {
            this.refreshUserRoleStores();
            // var vm = this.getViewModel();
            // var record = vm.get('record');
            // var data = this.handleUserRoles(records, record);
            // vm.getStore('userRoles').loadData(data.roles);
            // vm.getStore('userEffectiveRoles').loadData(data.effectiveRoles);
            // // vm.set({
            //   'userRoles': this.handleUserRoles(records, record.get('roles')),
            //   'userEffectiveRoles': this.handleUserRoles(records, record.get('effectiveRoles'))
            // });
          }
        },
        scope: this
      });
      // tab.setMasked({
      //   xtype: 'loadmask',
      //   message: 'Loading roles...'
      // });
      // Ext.getStore('Roles').load({
      //   callback: function() {
      //     tab.setMasked(false);
      //   }
      // });
    } else {
      this.callParent(arguments);
    }
  },
  addUserRole: function(role) {
    this.addItemRole(role, this.refreshUserRoleStores, this);
  },
  removeUserRole: function(role) {
    this.removeItemRole(role, this.refreshUserRoleStores, this);
  },
  // function needed for ontemDisclosure icon
  roleInfo: function() {
    // console.log(arguments);
  },
  saveGroupChanges: function(cell, checked, record) {
    var groups = [];
    record.getTreeStore().getRoot().cascade({
      before: function(userGroups) {
        if (this.get('checked') === true) {
          userGroups.push(this.get('iri'));
          // console.log('adding group ' + this.get('label') );
        }
      },
      args: [groups]
    });
    var view = this.getView();
    var user = this.getViewModel().get('record');
    view.setMasked({
      xtype: 'loadmask',
      message: 'Saving user...'
    });
    user.set('groups', groups);
    user.save({
      failure: function(record, operation) {
        Admin.util.ResponseHandler.handleResponse(operation, 'Error', 'An error occured while saving changes');
        user.reject();
      },
      success: function(record, operation) {
        // this.refreshUserRoleStores(Ext.getStore('Roles').getRange());
      },
      callback: function(record, operation, success) {
        view.setMasked(false);
      },
      scope: this
    });
  }
});
