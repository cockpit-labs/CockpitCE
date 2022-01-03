Ext.define('Studio.store.QuestionTypes', {
  extend: 'Ext.data.Store',
  alias: 'store.questiontypes',
  storeId: 'QuestionTypes',
  data: [
    { id: 'none', name: 'None'},
    { id: 'text', name: 'Text' },
    { id: 'yesno', name: 'Yes/No' },
    { id: 'select', name: 'Select' },
    { id: 'number', name: 'Number' },
    { id: 'range', name: 'Range' },
    { id: 'dateTime', name: 'Date/Time' }
  ]
});
