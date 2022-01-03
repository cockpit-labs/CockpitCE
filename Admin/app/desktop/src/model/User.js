Ext.define('Admin.model.User', {
  extend: 'Admin.model.Base',
  requires: [
    'Ext.data.validator.Presence',
    'Ext.data.validator.Email'
  ],
  labelField: 'username',
  fields: [
    {
      name: 'username',
      sortType: 'asUCString',
      validators: [{
        type: 'presence',
        message: 'Username is mandatory'
      }]
    },
    'firstname',
    'lastname',
    {
      name: 'enabled',
      type: 'bool',
      defaultValue: true
    },
    {
      name: 'email',
      validators: [{
        type: 'presence',
        message: 'Email is mandatory'
      }, {
        type: 'email'
      }]
    },
    {
      name: 'emailVerified',
      type: 'bool'
    },
    'roles',
    'effectiveRoles',
    {
      name: 'expirationDate',
      type: 'date',
      dateFormat: 'c'
    }
  ],
  // manyToMany: {
  //   UserRoles: {
  //     type: 'Role',
  //     role: 'roles',
  //     field: 'id',
  //     right: {
  //         field: 'id',
  //         role: 'users'
  //     }
  //   }
  // }
});
