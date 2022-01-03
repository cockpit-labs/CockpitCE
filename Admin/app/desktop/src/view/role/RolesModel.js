Ext.define('Admin.view.role.RolesModel', {
  extend: 'Ext.app.ViewModel',
  alias: 'viewmodel.roles',
  stores: {
    main: {
      storeId: 'Roles',
      type: 'roles'
      // source: 'Roles'
    }
  }
});
