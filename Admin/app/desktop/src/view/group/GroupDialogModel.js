Ext.define('Admin.view.group.GroupDialogModel', {
  extend: 'Admin.view.base.DialogModel',
  alias: 'viewmodel.groupdialog',
  stores: {
    users:  {
      source: 'Users',
      filters: [{
        property: 'iri',
        // we must use formulas because store filters raise an error when record is being set to null when closing dialog
        value: '{bindedUsers}',
        // value: '{record.bindedUsers}', // => fails with nullpointer
        // value: '{record.bindedUsers || Ext.emptyArray}', // => don't work at all
        operator: 'in'
      }]
    },
    roles: {
      source: 'Roles',
      filters: [{
        property: 'iri',
        // we must use formulas because store filters raise an error when record is being set to null when closing dialog
        value: '{bindedRoles}',
        // value: '{record.bindedRoles}', // => fails with nullpointer
        // value: '{record.bindedRoles || Ext.emptyArray}', // => don't work at all
        operator: 'notin'
      }]
    },
    groupEffectiveRoles: {
      sorters: ['name']
    },
    customAttributes: {
      // model: 'Studio.model.Choice',
      data: '{groupAttributes}',
      // data: '{record.attributes}',
      // do not use sorter otherwise drag&drop won't work as expected
      // sorters: ['position'],
      listeners: {
        update: 'onGroupAttributesUpdate',
        remove: 'onGroupAttributesUpdate',
        add: 'onGroupAttributesUpdate'
      }
    }
  },
  formulas: {
    /* we need to check if record is null, else filter will use bindedUsers=null and Filter 'in/notin' will fail with "Array.prototype.indexOf called on null or undefined" when closing dialog */
    bindedUsers: function(get) {
      var record = get('record');
      return record ? record.get('users') : [];
    },
    /* we need to return empty array when record is null, else filter will use bindedRoles=null and Filter 'in/notin' will fail with "Array.prototype.indexOf called on null or undefined" when closing dialog */
    bindedRoles: {
      bind: '{record.roles}',
      get: function (roles) {
        return roles || [];
      }
    },
    groupAttributes: {
      bind: {
        record: '{record}',
        // validCnt: '{validCnt}'
      },
      get: function (data) {
        var record = data.record;
        // return record ? Ext.clone(record.get('attributes')) : [];
        return record ? record.get('attributes') : [];
      }
    }
  }
});