Ext.define('Admin.reader.Groups', {
  extend: 'Ext.data.reader.Json',
  alias: 'reader.groups',
  getResponseData: function() {
    var data = this.callParent(arguments);
    if (Ext.isObject(data)) {
      data = [data];
    }
    // // for each block
    // for (var i=0; i < data.length; i++) {
    //   // var children = data[i].children;
    //   // if (children && children.length == 0) {
    //   //   data[i].leaf = true;
    //   // }
    // }
    return data;
  }
});