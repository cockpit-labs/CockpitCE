Ext.define('Override.dataview.List', {
  override: 'Ext.dataview.List',
  privates: {
    stickItem: function(item, options) {
      // prevent error when list is not infinite
      if (this.infinite) {
        this.callParent(arguments);
      }
    }
  }
});