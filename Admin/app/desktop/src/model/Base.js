Ext.define('Admin.model.Base', {
  extend: 'Ext.data.Model',
  requires:[
    'Ext.data.proxy.Rest',
  ],
  idProperty: 'id',
  fields: [
    'id',
    {
    name: 'iri',
    convert: function (value, record) {
      return '/api/' + record.entityName.toLowerCase() + 's/' + record.get('id');
    },
    depends: 'id',
    persist: false
  }],
  schema: {
    namespace: 'Admin.model',
    proxy: {
      type: 'rest',
      url: '/api/{entityName:lowercase}s',
      writer: {
        writeRecordId: false
      }
    }
  },
  getLabel: function() {
    var me = this;
    return me.get(me.labelField || me.idProperty);
  }
});