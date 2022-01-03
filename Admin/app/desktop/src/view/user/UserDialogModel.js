Ext.define('Admin.view.user.UserDialogModel', {
  extend: 'Admin.view.base.DialogModel',
  alias: 'viewmodel.userdialog',
  stores: {
    roles:  {
      source: 'Roles',
      filters: [{
        property: 'iri',
        // we must use formulas because store filters raise an error when record is being set to null when closing dialog
        value: '{effectiveRoles}',
        // value: '{record.effectiveRoles}', // => fails with nullpointer
        // value: '{record.effectiveRoles || Ext.emptyArray}', // => don't work at all
        operator: 'notin'
      }]
    },
    userRoles: {
      sorters: ['name']
    },
    userEffectiveRoles: {
      sorters: ['name']
    },
    groups: {
      type: 'groups',
      rootVisible: false
    }
  },
  formulas: {
    /* we need to return empty array when record is null, else filter will use effectiveRoles=null and Filter 'in/notin' will fail with "Array.prototype.indexOf called on null or undefined" when closing dialog */
    effectiveRoles: {
      bind: '{record.effectiveRoles}',
      get: function (roles) {
        return roles || [];
      }
    },
    expired: {
      bind: '{record.expirationDate}',
      get: function (date) {
        return Ext.Date.diff(new Date(), date, Ext.Date.DAY) < 0;
      }
    },
    superuser: function() {
      return Admin.util.State.get('superuser') == '1';
    }
  }
});