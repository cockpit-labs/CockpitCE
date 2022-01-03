Ext.define('Studio.view.base.ObjectGrid', {
  extend: 'Studio.view.base.SimpleGrid',
  xtype: 'objectgrid',
  alias: 'widget.objectgrid',
  deletable: false,
  iconable: false,
  columns: [{
    text: 'Label',
    dataIndex: 'label',
    width: 300
  },{
    text: 'Description',
    dataIndex: 'description',
    flex: 1
  }],
  selectable: {
    headerCheckbox: false,
    checkbox: true,
    mode: 'multi'
  }
});