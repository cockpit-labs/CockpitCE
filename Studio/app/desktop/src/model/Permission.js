Ext.define('Studio.model.Permission', {
  extend: 'Ext.data.Model',
  convertOnSet: false,
  fields: [
    {
      name: 'id',
      type: 'string',
      convert: null
    },
    {
      name: 'userRole',
      type: 'string',
      convert: null,
      validators:[{
        type: 'presence',
        message: 'Label is mandatory'
      }]
    },
    {
      name: 'targetRole',
      type: 'string',
      convert: null,
      validators:[{
        type: 'presence',
        message: 'Label is mandatory'
      }]
    },
    {
      name: 'right',
      type: 'string',
      convert: null,
      validators:[{
        type: 'presence',
        message: 'Label is mandatory'
      }]
    }
  ],
  validators: {
    name: 'presence'
  },
  proxy: {
    type: 'rest',
    url: '/api/folder_tpl_permissions',
    writer: {
      writeRecordId: false
    }
  }
});
