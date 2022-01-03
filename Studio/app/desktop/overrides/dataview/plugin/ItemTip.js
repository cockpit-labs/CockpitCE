Ext.define('Override.dataview.plugin.Itemtip', {
  override: 'Ext.dataview.plugin.ItemTip',
  applyData: function(data) {
    if (data) {
      if (data.isEntity) {
        data = data.getData(true);
      }     
    }
    return data; 
  }
});