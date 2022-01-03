Ext.define('Studio.view.base.Dataview', {
  extend: 'Ext.dataview.DataView',
  xtype: 'basedataview',
  config: {
    previousSelectedItemIndex: -1
  },
  applyMasked: function(mask) {
    if (!mask && Ext.Viewport.getController().hasPendingLoads()) {
      return;
    }
    Ext.Viewport.setMasked(mask);
  }
});
