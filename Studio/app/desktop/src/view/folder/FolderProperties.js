Ext.define('Studio.view.folder.FolderProperties', {
  extend: 'Studio.view.base.Properties',
  xtype: 'folderproperties',
  userCls: 'folderproperties',
  alias: 'widget.folderproperties',
  requires: [
    'Studio.store.Calendars',
    'Studio.view.base.SimpleGrid',
    'Studio.store.Roles',
    'Studio.store.Rights',
    'Ext.grid.plugin.CellEditing',
    'Ext.field.ComboBox',
    'Ext.field.Spinner',
    'Ext.grid.plugin.RowDragDrop'
  ],
  items: [
    {
      /* Label & description fields */
      xtype: 'container',
      padding: 4,
      layout: {
        type: 'hbox',
        align: 'stretch',
        pack: 'start'
      },
      defaults: {
        margin: 5
      },
      items: [{
        xtype: 'textfield',
        label: 'Label',
        required: true,
        name: 'label',
        bind: '{record.label}',
        width: 250
      },
      {
        xtype: 'textfield',
        flex: 1,
        label: 'Description',
        name: 'description',
        bind: '{record.description}'
      }]
    }, {
      /* min/max */
      xtype: 'container',
      padding: 4,
      margin: '0 5',
      layout: {
        type: 'hbox',
        align: 'stretch',
        pack: 'justify'
      },
      items: [{
        xtype: 'containerfield',
        label: 'Occurences in a period',
        // labelTextAlign: 'center',
        items: [{
          xtype: 'spinnerfield',
          minValue: 0,
          label: 'Min.',
          labelAlign: 'left',
          labelWidth: 60,
          labelTextAlign: 'right',
          width: 124,
          name: 'minFolders',
          bind: '{record.minFolders}',
          listeners: {
            change: 'onChangeMinFolders'
          }
        },
        {
          xtype: 'spinnerfield',
          label: 'Max.',
          labelAlign: 'left',
          labelWidth: 60,
          labelTextAlign: 'right',
          width: 124,
          minValue: 0,
          name: 'maxFolders',
          bind: '{record.maxFolders}',
          validators: {
            type: 'controller',
            fn: 'maxFoldersValidation'
          },
          errorTarget: 'side'
        }]
      },{
        /* Calendar field */
        xtype: 'selectfield',
        flex: 1, 
        label: 'Calendars',
        multiSelect: true,
        chipView: {
          iconCls: 'x-fa fa-check'
        },
        filterPickList: true,
        queryMode: 'local',
        displayField: 'label',
        valueField: 'iri',
        editable: false,
        required: true,
        bind: {
          value: '{record.calendars}',
          store: '{calendars}',
        },
        itemTpl: '{label}',
        displayTpl: '{label}'
      }]
    },
    {
      /* Documents */
      xtype: 'documents',
      title: 'Documents',
      editable: true,
      disableSelection: true,
      maxHeight: '40%',
      titleBar: {
        innerCls: 'x-fa fa-tasks'
      },
      plugins: {
        gridrowdragdrop: {
          dragText: 'Drag and drop to reorganize',
        }
      },
      bind: {
        store: '{questionnaires}',
        selection: '{selectedDocument}'
      },
      emptyText: 'This folder has no document',
      items: [{
        docked: 'top',
        xtype: 'toolbar',
        items:[{
          xtype: 'button',
          text: 'Add',
          iconCls: 'x-fa fa-plus-circle',
          userCls: 'add-document-menu',
          tooltip: 'Create or clone a questionnaire',
          menu: [{
          //   text: 'Action Plan',
          //   disabled: true
          // },{
            text: 'Questionnaire',
            userCls: 'create-document',
            action: 'createDocument',
            tooltip: 'Create a questionnaire'
          },
          '-',
          // {
          //   text: 'Reuse existing...',
          //   userCls: 'reuse-document',
          //   action: 'useDocument'
          // },
          {
            text: 'Clone existing...',
            userCls: 'clone-document',
            action: 'cloneDocument',
            tooltip: 'Duplicate an existing questionnaire'
          }]
        }]
      }]
    },{
      /* Permissions */
      xtype: 'simplegrid',
      // itemId: 'permissions',
      title: 'Permissions',
      markDirty: true,
      maxHeight: '40%',
      titleBar: {
        innerCls: 'x-fa fa-lock'
      },
      bind: '{permissions}',
      emptyText: 'No defined permission',
      plugins: {
        gridcellediting: {
          selectOnEdit: true,
          triggerEvent: 'tap'
        }
      },
      items: [{
        docked: 'top',
        xtype: 'toolbar',
        items:[{
          xtype: 'button',
          action: 'createPermission',
          text: 'Add',
          iconCls: 'x-fa fa-plus-circle',
          tooltip: 'Create a permission set'
        }]
      }],
      columns: [{
        width: 40,
        resizable: false,
        cell: {
          tools: {
            lock: {
              iconCls: 'x-fa fa-lock'
            }
          }
        }
      },{
        text: 'Users with role',
        dataIndex: 'userRole',
        width: 200,
        editor: {
          xtype: 'selectfield',
          required: true,
          field: {
            allowBlank: false
          },
          displayField: 'name',
          valueField: 'iri',
          store: 'Roles'
        },
        renderer: 'permissionRenderer'
      },{
        text: 'have right',
        dataIndex: 'right',
        flex: 1,
        editor: {
          xtype: 'selectfield',
          required: true,
          field: {
            allowBlank: false
          },
          // displayTpl: '{id} - ({description})',
          // displayField: 'id',
          itemTpl: '<b>{id}</b><tpl if="description"><div>{description}</div></tpl>',
          // tpl: Ext.create('Ext.XTemplate', '<tpl for=".">', '<div class="x-boundlist-item" style="border-bottom:1px solid #f0f0f0;">', '<div>{id}</div>', '<div><b>Category:</b> {category}</div>', '<div><b>Vendor:</b> {vendor}</div></div>', '</tpl>'),
          displayTpl: '{id}<tpl if="description"> ({description})</tpl>',
          // DO NOT REMOVE displayField property since it is used in permisionRenderer
          displayField : 'id',
          // 
          valueField: 'iri',
          store: 'Rights'
        },
        renderer: 'permissionRenderer'
      },{
        text: 'on this group\'s role',
        dataIndex: 'targetRole',
        width: 200,
        editor: {
          xtype: 'selectfield',
          required: true,
          field: {
            allowBlank: false
          },
          displayField: 'name',
          valueField: 'iri',
          store: 'Roles'
        },
        renderer: 'permissionRenderer'
      },{
        width: 40,
        resizable: false,
        cell: {
          tools: {
            remove: {
              iconCls: 'x-fa fa-times',
              handler: 'removePermission',
              tooltip: 'Delete permission'
            }
          }
        }
      }]
    }
  ]
});