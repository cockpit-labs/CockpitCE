Ext.define('Admin.reader.GroupsTreeList', {
  extend: 'Ext.data.reader.Json',
  alias: 'reader.groupstreelist',
  getResponseData: function() {
    var data = this.callParent(arguments) || [];
    for (var i=0; i < data.length; i++) {
      if (!data[i].parent) {
        console.log("adding parent to " + data[i].label);
        data[i].parent = "/api/groups/fake-root";
      }
    }
    data.push({
      id: 'fake-root',
      label: 'Root',
      expanded: true
    });
    return data;
  }
});