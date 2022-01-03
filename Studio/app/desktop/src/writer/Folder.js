Ext.define('Studio.writer.Folder', {
  extend: 'Ext.data.writer.Json',
  alias: 'writer.folder',
  config: {
    writeRecordId: false
  },
  writeRecords: function(request, data) {
    for (var i=0; i < data.length; i++) {
      // manage folder documents
      var documents = data[i].questionnaireTpls;
      if (documents) {
        for (var j=0; j < documents.length; j++) {
          var document = documents[j];
          if (document) {
            documents[j] = document.id;
          }
        }
      }
      // manage permissions
      var permissions = data[i].permissions;
      if (permissions) {
        for (var j=0; j < permissions.length; j++) {
          var permission = permissions[j];
          if (permission && permission.isModel) {
            if (permission.isDirty()) {
              permissions[j] = {
                userRole: permission.get('userRole'),
                targetRole: permission.get('targetRole'),
                right: permission.get('right')
              };
              if (!permission.isPhantom()) {
                permissions[j].id = permission.get('id');
              }
            } else {
              permissions[j] = {
                id: permission.get('id')
              };
            }
          }
        }
      }
    }
    return this.callParent(arguments);
  }
});