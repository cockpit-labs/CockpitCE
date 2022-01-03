Ext.define('Studio.store.Folders', {
  extend: 'Ext.data.Store',
  alias: 'store.folders',
  model: 'Studio.model.Folder',
  autoLoad: true,
  storeId: 'Folders',
  sorters: ['label']
});
