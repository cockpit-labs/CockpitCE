Ext.define('Admin.view.user.UsersRowModel', {
  extend: 'Ext.app.ViewModel',
  alias: 'viewmodel.usersrowmodel',
  formulas: {
    userExpiredCls: {
      bind: {
        enabled: '{record.enabled}',
        date: '{record.expirationDate}'
      },
      get: function (args) {
        return (!args.enabled && Ext.Date.diff(new Date(), args.date, Ext.Date.DAY) < 0) ? 'x-disabled' : '';
      }
    }
  }
});