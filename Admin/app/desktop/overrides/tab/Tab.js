/* change Tab width for tablet (see material theme) */
Ext.define('Override.theme.material.tab.Tab', {
  override: 'Ext.tab.Tab',
  platformConfig: {
    tablet: {
      maxWidth: 200
    }
  }
});