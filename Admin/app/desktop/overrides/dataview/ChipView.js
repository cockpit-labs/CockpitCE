Ext.define('Override.dataview.Chipview', {
  override: 'Ext.dataview.ChipView',
  config: {
    iconCls: null
  },
  privates: {
    getChipIconCls: function(values) {
      var iconClsField = this.getIconClsField(),
          // uses iconCls on chipview for selected items
          //iconCls = iconClsField ? values[iconClsField] : '';
          defaultIconCls = this.getIconCls(),
          iconCls = iconClsField ? values[iconClsField] : (defaultIconCls ? defaultIconCls : '');

      return this.iconElCls + ' ' + iconCls;
    }
  }
});