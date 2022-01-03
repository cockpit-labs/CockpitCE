Ext.define('Studio.model.Right', {
  extend: 'Ext.data.Model',
  // idProperty: 'id',
  fields: [
    // 'id',
    // {
    //   name: 'id',
    //   type: 'string',
    //   convert: null
    // },
    {
      name: 'description',
      type: 'string',
      convert: null
    },
    {
      // calculated field
      name: 'iri',
      convert: function (value, record) {
        return '/api/rights/' + record.get('id');
      },
      depends: 'id',
      persist: false
    }
  ],
  proxy: {
    type: 'rest',
    url: '/api/rights'
  }
});
