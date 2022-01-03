Ext.define('Admin.view.user.UsersController', {
  extend: 'Admin.view.base.GridController',
  alias: 'controller.users',
  filterItems: function(value) {
    this.getViewModel().set('filterValue', value.getValue());
  }
});