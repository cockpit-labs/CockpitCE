Ext.define('Admin.view.base.Properties', {
  extend: 'Ext.Container',
  xtype: 'properties',
  alias: 'widget.properties',
  requires: [
    'Ext.Button',
    'Ext.layout.Box',
    'Ext.Toolbar'
  ],
  cls: 'properties',
  scrollable: true
});