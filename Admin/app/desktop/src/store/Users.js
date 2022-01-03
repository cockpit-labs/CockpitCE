Ext.define('Admin.store.Users', {
  extend: 'Ext.data.Store',
  alias: 'store.users',
  model: 'Admin.model.User',
  autoLoad: false,
  sorters: ['username'],
  filters: []
});
