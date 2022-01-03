Ext.define('Studio.model.Role', {
  extend: 'Ext.data.Model',
  idProperty: 'id',
  fields: [
    'id',
    {
      name: 'name',
      type: 'string',
      convert: null
    },
    {
      name: 'resource',
      type: 'string',
      convert: null
    },
    {
      // calculated field
      name: 'iri',
      convert: function (value, record) {
        return '/api/roles/' + record.get('id');
      },
      depends: 'id',
      persist: false
    }
  ],
  proxy: {
    type: 'rest',
    url: '/api/roles'
  }
});
