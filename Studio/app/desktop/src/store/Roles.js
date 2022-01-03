Ext.define('Studio.store.Roles', {
  extend: 'Ext.data.Store',
  alias: 'store.roles',
  model: 'Studio.model.Role',
  storeId: 'Roles',
  autoLoad: false,
  sorters: ['name']
});
