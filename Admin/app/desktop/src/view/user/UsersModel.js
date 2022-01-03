Ext.define('Admin.view.user.UsersModel', {
  extend: 'Ext.app.ViewModel',
  alias: 'viewmodel.users',
  data: {
    filterValue: ''
  },
  stores: {
    main: {
      // storeId: 'Users',
      // source: 'Users',
      // type: 'users',
      // filters: [{
      //   id: 'UserFilter',
      //   filterFn: '{filterUsers}'
      // }]
      storeId: 'UsersChained',
      source: 'Users',
      // type: 'users',
      filters: [{
        id: 'UserFilter',
        filterFn: '{filterUsers}'
      }]
    }
  },
  formulas: {
    filterUsers: {
      bind: {
        value: '{filterValue}',
        deep: true
      },
      get: function (data) {
        return function(item) {
          var val = data.value;
          return (item.get('username') || '').indexOf(val)  != -1
            || (item.get('firstname') || '').indexOf(val)  != -1
            || (item.get('lastname') || '').indexOf(val)  != -1
            || (item.get('email') || '').indexOf(val)  != -1
        };
      }
    }
  }
});
