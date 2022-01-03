Ext.define('Studio.store.Documents', {
  extend: 'Ext.data.Store',
  alias: 'store.documents',
  model: 'Studio.model.Document',
  storeId: 'Documents',
  autoLoad: true
});
