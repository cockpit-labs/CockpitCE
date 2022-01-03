
Ext.define('Override.field.Date', {
  override: 'Ext.field.Date',
  onPickerHide: function() {
    this.callParent();
    // handle Date field destroyPickerOnHide config (misses in 7.3, 7.4)
    if (this.getDestroyPickerOnHide()) {
      this.destroyMembers('picker');
      this.setPicker({
        lazy: true,
        $value: 'auto'
      });
    }
  }
});