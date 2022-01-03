Ext.define('Studio.store.Blocks', {
  extend: 'Ext.data.Store',
  alias: 'store.blocks',
  model: 'Studio.model.Block',
  storeId: 'Blocks',
  autoLoad: true,
  sorters: ['label']
});
