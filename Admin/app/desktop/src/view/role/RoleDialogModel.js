Ext.define('Admin.view.role.RoleDialogModel', {
  extend: 'Admin.view.base.DialogModel',
  alias: 'viewmodel.roledialog',
  stores: {
    users:  {
      source: 'Users',
      filters: [{
        property: 'iri',
        // we must use formulas because store filters raise an error when record is being set to null when closing dialog
        value: '{legacyUsers}',
        // value: '{record.legacyUsers}', // => fails with nullpointer
        // value: '{record.legacyUsers || Ext.emptyArray}', // => don't work at all
        operator: 'in'
      }]
    },
    groups: {
      type: 'groups',
      rootVisible: false
    }
  },
  formulas: {
    /* we need to check if record is null, else filter will use legacyUsers=null and Filter 'in/notin' will fail with "Array.prototype.indexOf called on null or undefined" when closing dialog */
    legacyUsers: function(get) {
      var record = get('record');
      return record ? record.get('legacyUsers') : [];
    }
  }
});