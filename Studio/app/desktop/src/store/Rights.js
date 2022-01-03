Ext.define('Studio.store.Rights', {
  extend: 'Ext.data.Store',
  alias: 'store.rights',
  model: 'Studio.model.Right',
  storeId: 'Rights',
  sorters: ['id'],
  autoLoad: false
});
