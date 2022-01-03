Ext.define('Admin.view.group.GroupAttributes', {
  extend: 'Ext.grid.Grid',
  xtype: 'groupattributes',
  alias: 'widget.groupattributes',
  requires: [
    'Ext.grid.plugin.RowDragDrop',
    'Ext.grid.plugin.CellEditing'
  ],
  plugins: {
    gridrowdragdrop: true,
    cellediting: {
      triggerEvent: 'tap'
    }
  },
  reference: 'groupattributes',
  infinite: false,
  // no need grouping
  grouped: false,
  columnMenu: null,
  // There is no asymmetric data, we do not need to go to the expense of synching row heights
  syncRowHeight: false,
  userSelectable: 'text',
  cls: 'groupattributes',
  // border: true,
  shadow: true,
  selectable: {
    checkbox: true
  },
  items: [{
    xtype: 'toolbar',
    docked: 'top',
    items: [{
      xtype: 'button',
      text: 'Add',
      iconCls: 'x-fa fa-plus-circle',
      handler: 'addAttribute'
    }, {
      xtype: 'button',
      text: 'Delete',
      iconCls: 'x-fa fa-trash',
      handler: 'deleteAttributes',
      disabled: true,
      bind: {
        disabled: '{!groupattributes.selection}',
        hidden: '{!customAttributes.count}'
      }
    }]
  }, {
    xtype: 'toolbar',
    docked: 'bottom',
    items: [{
      xtype: 'button',
      text: 'Add',
      iconCls: 'x-fa fa-plus-circle',
      handler: 'addAttribute'
    }, {
      xtype: 'button',
      text: 'Delete',
      iconCls: 'x-fa fa-trash',
      handler: 'deleteAttributes',
      disabled: true,
      bind: {
        disabled: '{!groupattributes.selection}',
        hidden: '{!customAttributes.count}'
      }
    }],
    bind: {
      hidden: '{customAttributes.count < 2}'
    }
  }],
  columns: [{
  //   text: '#',
  //   dataIndex: 'position',
  //   menu: null,
  //   width: 50,
  //   editable: false,
  //   draggable: false
  // },{
    text: 'Label',
    dataIndex: 'label',
    menu: null,
    minWidth: 150,
    flex: 1,
    editable: true,
    draggable: false,
    cell: {
      userCls: 'label'
    }
  },{
  //   text: 'Type',
  //   dataIndex: 'type',
  //   align: 'center',
  //   menu: null,
  //   minWidth: 100,
  //   editable: true,
  //   draggable: false,
  //   cell: {
  //     userCls: 'type'
  //   },
  //   editor: {
  //     xtype: 'selectfield',
  //     options: [
  //       {
  //         text: 'String',
  //         value: 'string'
  //       }, {
  //         text: 'Integer',
  //         value: 'int'
  //       }, {
  //         text: 'Float',
  //         value: 'float'
  //       }
  //     ]
  //   },
  //   renderer: function(value) {
  //     switch (value) {
  //       case 'string':
  //       case 'icon':
  //         return 'String';
  //       case 'int':
  //         return 'Integer';
  //       case 'float':
  //         return 'Float';
  //       default:
  //         return null;
  //     }
  //   }
  // },{
    // xtype: 'groupattrvalue',
    text: 'Value',
    dataIndex: 'value',
    menu: null,
    minWidth: 200,
    flex: 2,
    draggable: false,
    editable: true,
    cell: {
      userCls: 'value'
    }
  },{
    width: 40,
    resizable: false,
    menu: null,
    cell: {
      tools: {
        remove: {
          iconCls: 'x-fa fa-trash',
          action: 'deleteAttribute'
        }
      }
    }
  }],
});