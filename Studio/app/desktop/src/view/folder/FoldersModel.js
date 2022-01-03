Ext.define('Studio.view.folder.FoldersModel', {
  extend: 'Studio.view.base.ViewModel',
  alias: 'viewmodel.folders',
  data: {
    selectedDocument: null,
    forcePermissionsRefresh: 0
  },
  formulas: {
    permissionTpls: {
      bind: {
        item: '{record}',
        force: '{forcePermissionsRefresh}'
      },
      get: function(data) {
        if (data.item) {
          var perms = data.item.get('permissions');
          if (perms) {
            return perms;
          } else if (data.item.permissions) {
            return data.item.permissions().getRange();
          }
        }
        return [];
      }
    }
  },
  stores: {
    calendars: {
      source: 'Calendars',
      filters: [{
        filterFn: function(calendar) {
          return !calendar.phantom;
        }
      }]
    },
    list: {
      type: 'folders'
    },
    questionnaires: {
      model: 'Studio.model.Document',
      data: '{record.questionnaireTpls}',
      proxy: {
        type: 'rest',
        url: '/api/questionnaire_tpls',
        writer: {
          writeRecordId: false
        },
        reader: {
          type: 'document'
        }
      }
    },
    permissions: {
      model: 'Studio.model.Permission',
      data: '{permissionTpls}',
      listeners: {
        update: 'onPermissionUpdate',
        remove: 'onPermissionUpdate',
        add: 'onPermissionUpdate'
      }
    }
  }
});
