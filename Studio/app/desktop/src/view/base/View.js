Ext.define('Studio.view.base.View', {
  extend: 'Ext.Container',
  requires: [
    'Ext.panel.Resizable',
    'Ext.panel.Resizer',
    'Ext.layout.Center',
    'Studio.view.base.Dataview'
  ],
  config: {
    listTitle: 'Items',
    emptyMessage: 'Select an item or create a new one',
    route: null
  },
  cls: 'c-base-view',
  defaults: {
  },
  layout: {
    type: 'hbox'
  },
  initialize: function() {
    this.insert(0, [{
      xtype: 'panel',
      resizable: {
        split: true,
        edges: 'east'
      },
      layout: 'fit',
      items: [{
        xtype: 'basedataview',
        reference: 'itemsList',
        selectable: {
          single: true,
          // do not use it because when cancelling creation on phantom record, it stays on screen
          //deselectable: false
        },
        emptyText: true,
        cls: 'c-list',
        itemTpl: '<div class="x-fa c-text">{label}</div>' +
           '<div class="x-fa fa-copy c-clone-btn" data-qtip="Duplicate folder"></div>' +
           '<div class="x-fa fa-trash c-delete-btn" data-qtip="Delete folder"></div>',
        bind: {
          store: '{list}',
          selection: '{record}'
        },
        listeners: {
          childtap: 'itemTap'
        },
        items: {
          docked: 'top',
          xtype: 'panelheader',
          iconCls: this.getListIconCls(),
          title: this.getListTitle(),
          defaults: {
            xtype: 'tool',
            cls: 'x-paneltool'
          },
          items: [{
            type: 'plus',
            userCls: 'add-tool',
            zone: 'end',
            handler: 'create',
            tooltip: 'Create a folder'
          },{
            type: 'refresh',
            userCls: 'refresh-tool',
            zone: 'end',
            handler: 'reload',
            tooltip: 'Reload folders'
          }]
        }
      }]
    }, {
      layout: 'center',
      cls: 'c-empty-text',
      flex: 1,
      html: '<p>' + this.getEmptyMessage() + '</p>',
      bind: {
        hidden: '{record}'
      }
    }]);
    this.callParent(arguments);
  }
});
