Ext.define('Admin.model.Group', {
  extend: 'Admin.model.Base',
  requires: [
    'Admin.ux.TreeProxy',
    'Admin.reader.Groups'
  ],
  labelField: 'label',
  convertOnSet: false,
  fields: [
    {
      name: 'loaded',
      type: 'bool',
      defaultValue: true
    },
    {
      name: 'leaf',
      type: 'bool',
      persist: false
    },
    {
      name: 'parentId',
      persist: false,
      convert: function (value, record) {
        var parent = record.get('parent');
        if (parent) {
          var i = parent.lastIndexOf('/');
          if (i != -1) {
            parent = parent.substring(i + 1);
          }
        }
        return parent;
      },
      depends: 'id'
    },    
    {
      name: 'label',
      sortType: 'asUCString',
      validators: [{
        type: 'presence',
        message: 'Name is mandatory'
      }]
    },
    {
      name: 'parent',
      convert: function (value, record) {
        var parent = value;
        // if (parent && parent.isModel) {
        //   parent = parent.get('iri');
        // }
        return parent;
      }
    },
    {
      name: 'attributes',
      type: 'array'
    }
  ],
  proxy: {
    type: 'treerest',
    reader: {
      type: 'groups'
    },
    writer: {
      writeRecordId: false
    }
  }
});
