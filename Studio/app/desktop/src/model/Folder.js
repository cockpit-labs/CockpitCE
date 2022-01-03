Ext.define('Studio.model.Folder', {
  extend: 'Ext.data.Model',
  requires: [
    'Studio.reader.Folder',
    'Studio.writer.Folder',
    'Studio.model.validators.PermissionsValidator',
    'Studio.model.validators.MinMaxValidator',
    'Ext.data.validator.Presence'
  ],
  convertOnSet: false,
  fields: [
    {
      name: 'id',
      type: 'string',
      convert: null
    },
    {
      name: 'label',
      type: 'string',
      convert: null,
      defaultValue: '',
      validators:[{
        type: 'presence',
        message: 'Label is mandatory'
      }]
    },
    {
      name: 'description',
      type: 'string',
      convert: null,
      defaultValue: ''
    },
    {
      name: 'calendars',
      type: 'array',
      validators:[{
        type: 'presence',
        message: 'Calendar is mandatory'
      }]
    },
    // 'calendars',
    {
      name: 'minFolders',
      type: 'number',
      convert: null
    },
    {
      name: 'maxFolders',
      type: 'number',
      convert: null,
      validators:[{
        type: 'minmax',
        minField: 'minFolders',
        message: 'Maximum folders count should at least equal minimum'
      }]
    },
    { 
      name: 'questionnaireTpls',
      defaultValue: []
    },
    {
      name: 'permissions',
      validators: [{
        type: 'permissions',
        message: 'Permissions must have target role, user role and right'
      }],
      defaultValue: []
    },
    {
      // calculated field
      name: 'iri',
      type: 'string',
      convert: function (value, record) {
        return '/api/folders_tpls/' + record.get('id');
      },
      depends: 'id',
      persist: false
    }
  ],
  // hasMany: {
  //   model: 'Studio.model.Permission',
  //   name: 'permissions'
  // },
  proxy: {
    type: 'rest',
    url: '/api/folder_tpls',
    writer: {
      type: 'folder'
    },
    reader: {
      type: 'folder'
    }
  }
});
