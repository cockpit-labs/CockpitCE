Ext.define('Studio.view.document.Documents', {
  extend: 'Studio.view.base.SimpleGrid',
  xtype: 'documents',
  userCls: 'c-documents',
  alias: 'widget.documents',
  config: {
    deletable: true,
    iconable: true
  },
  initialize: function() {
    var columns = [];
    if (this.getIconable()) {
      columns.push({
        width: 40,
        resizable: false,
        cell: {
          tools: {
            approve: {
                iconCls: 'x-fa fa-tasks'
            }
          }
        }
      });
    }
    columns.push({
      text: 'Label',
      dataIndex: 'label',
      width: 300
    },{
      text: 'Description',
      dataIndex: 'description',
      flex: 1
    });
    if (this.getDeletable()) {
      columns.push({
        width: 40,
        resizable: false,
        cell: {
          tools: {
            remove: {
              iconCls: 'x-fa fa-trash',
              action: 'removeDocument',
              tooltip: 'Remove document from folder'
            }
          }
        }
      });
    }
    this.setColumns(columns);
    this.callParent();
  }
});