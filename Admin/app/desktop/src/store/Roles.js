Ext.define('Admin.store.Roles', {
  extend: 'Ext.data.Store',
  alias: 'store.roles',
  model: 'Admin.model.Role',
  autoLoad: false,
  sorters: ['name'],
  // filters: [{
  //   property: 'system',
  //   value: false
  // }]
});
