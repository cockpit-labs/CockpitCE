Ext.define('Admin.view.role.RoleTabPanelController', {
  extend: 'Admin.view.base.TabPanelController',
  alias: 'controller.roletabpanelcontroller',
  control: {
    'roletabpanel': {
      activeitemchange: 'onActiveTabChanged'
    },
    'roletabpanel groupstree': {
      beforecheckchange: function() {
        return false;
      }
    }
  },
  onActiveTabChanged: function(tabpanel, tab) {
    if (tab.getItemId() == 'users') {
      var userStore = Ext.getStore('Users');
      if (!userStore.loadCount) {
        userStore.load();
      }
    } else {
      this.callParent(arguments);
    }
  }
});
