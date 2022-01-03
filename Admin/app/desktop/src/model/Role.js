Ext.define('Admin.model.Role', {
  extend: 'Admin.model.Base',
  requires: [
    'Ext.data.validator.Presence'
  ],
  labelField: 'name',
  fields: [
    {
      name: 'name',
      sortType: 'asUCString',
      validators: [{
        type: 'presence',
        message: 'Name is mandatory'
      }]
    },
    'description',
    {
      name: 'system',
      type: 'bool'
    },
    {
      name: 'groups',
      defaultValue: []
    },
    {
      name: 'users',
      defaultValue: []
    }
  ]
});