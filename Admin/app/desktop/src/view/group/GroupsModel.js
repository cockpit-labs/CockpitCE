Ext.define('Admin.view.group.GroupsModel', {
  extend: 'Ext.app.ViewModel',
  alias: 'viewmodel.groups',
  stores: {
    groups: {
      type: 'groups',
      rootVisible: false
    }
  }
});
