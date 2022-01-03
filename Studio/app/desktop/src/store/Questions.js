Ext.define('Studio.store.Questions', {
  extend: 'Ext.data.Store',
  alias: 'store.questions',
  model: 'Studio.model.Question',
  storeId: 'Questions',
  autoLoad: true,
  sorters: ['label']
});
