Ext.define('Admin.view.group.GroupTabPanelController', {
  extend: 'Admin.view.base.TabPanelController',
  alias: 'controller.grouptabpanelcontroller',
  control: {
    'grouptabpanel': {
      activeitemchange: 'onActiveTabChanged'
    },
    'tool[action=deleteAttribute]': {
      click: 'deleteAttribute'
    }
  },
  onActiveTabChanged: function(tabpanel, tab) {
    if (tab.getItemId() == 'users') {
      var userStore = Ext.getStore('Users');
      if (!userStore.loadCount) {
        userStore.load();
      }
    } else if (tab.getItemId() == 'roles') {
      Ext.getStore('Roles').load({
        callback: function(records, operation, success) {
          if (success) {
            this.refreshGroupRoleStore();
          }
        },
        scope: this
      });
    }
  },
  addGroupRole: function(role) {
    this.addItemRole(role, this.refreshGroupRoleStore, this);
  },
  refreshGroupRoleStore: function() {
    var vm = this.getViewModel();
    var roles = [];
    Ext.each(vm.get('record').get('roles'), function(value) {
      var found = this.findRecord('iri', value);
      if (found) {
        roles.push({
          id: found.get('id'),
          name: found.get('name'),
          iri: found.get('iri')
        });
      } else {
        // something went wrong. Group has a not known role
      }
    }, Ext.getStore('Roles'));
    vm.getStore('groupEffectiveRoles').loadData(roles);
  },
  removeGroupRole: function(role) {
    this.removeItemRole(role, this.refreshGroupRoleStore, this);
  },
  addAttribute: function(btn) {
    var grid = btn.up('grid');
    var store = grid.getStore();
    var newRecords = store.add({
      label: 'New attribute',
      type: 'string',
      position: store.getCount() + 1,
      value: ''
    });
    grid.up('groupproperties').getScrollable().scrollTo(null, -1);
    grid.getPlugin('cellediting').startEdit(newRecords[0],2);
  },
  deleteAttribute: function(tool, el, cell) {
    cell.getRecord().drop();
  },
  deleteAttributes: function(btn) {
    var grid = btn.up('grid');
    grid.getStore().remove(grid.getSelectable().getSelectedRecords());
  },
  onAttributesMoved: function(node, data, overModel, dropPosition, eOpts) {
    data.records[0].store.each(function(rec, index) {
      rec.set('position', index + 1);
    });
  }
});
