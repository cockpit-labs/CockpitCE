Ext.define('Admin.view.group.GroupProperties', {
  extend: 'Admin.view.base.Properties',
  xtype: 'groupproperties',
  alias: 'widget.groupproperties',
  requires: [
    'Ext.field.Toggle',
    'Ext.form.FieldSet',
    'Ext.list.Tree',
    'Admin.ux.FieldLabelEditor',
    'Ext.field.trigger.SpinUp',
    'Ext.field.trigger.SpinDown',
    'Ext.TitleBar'
  ],
  config: {
    showParentGroupTree: false
  },
  userCls: 'groupproperties',
  destroy: function() {
    // this.labelEditor.destroy();
    this.callParent(arguments);
  },
  initialize: function() {
    var items = [{
      xtype: 'fieldset',
      itemId: 'GroupAttributes',
      layout: {
        type: 'vbox',
        align: 'center'
      },
      defaultType: 'textfield',
      fieldDefaults: {
        labelAlign: 'left',
        errorTarget: 'side'
      },
      items: [{
        label: 'ID',
        // name: 'id',
        disabled: true,
        width: 350,
        bind: {
          hidden: '{record.phantom}',
          value: '{record.id}'
        }
      },{
        itemId: 'firstField',
        label: 'Group name',
        required: true,
        name: 'label',
        // force validation at init
        // not needed if disabled is binded
        validateOnInit: 'all',
        value: '',
        //
        bind: {
          value: '{record.label}'
        },
        width: 350
      }],
      // listeners: {
      //   scope:this,
      //   painted: function(form){
      //     if (this.labelEditor) {
      //       // prevent duplicate references when painted event is raised more times
      //       return;
      //     }
      //     var height = form.child('textfield').getHeight();
      //     this.labelEditor = Ext.create('Admin.ux.FieldLabelEditor', Ext.apply({
      //       height: height,
      //       field: {
      //         required: true
      //       }
      //     }));
      //     form.bodyElement.on('dblclick', function(e, t){
      //       this.labelEditor.startEdit(t);
      //       // Manually focus, since clicking on the label will focus the text field
      //       this.labelEditor.getField().focus(50, true);
      //     }, this, {
      //       delegate: '.editable-label .x-label-el'
      //     });
      //   }
      // }
    }];
    if (this.getShowParentGroupTree()) {
      items.push({
        xtype: 'label',
        html: '&nbsp;Select a parent group :'
      },
      {
        xtype: 'treelist',
        userCls: 'groups-tree-list',
        flex: 1,
        scrollable: true,
        shadow: true,
        singleExpand: true,
        minHeight: 200,
        maxHeight: 400,
        defaults: {
          textProperty: 'label',
        },
        html: 'Loading groups ...',
        listeners: {
          itemclick: function(sender, info) {
            if (info.select) {
              var group = sender.lookupViewModel().get('record');
              if (info.item.getSelected()) {
                sender.setSelection(null);
                group.set('parent', null);
                return false;
              } else {
                group.set('parent', info.node.get('iri'));
                return true;
              }
            }
          },
          refresh: function(sender, info) {
            this.setHtml('');
          }
        },
        store: {
          type: 'groups',
          autoLoad: true
        }
      });
    } else {
      var scope = this.getController();
      items.push({
        xtype: 'groupattributes',
        iconCls: 'x-fa fa-comment-dots',
        title: 'Attributes',
        emptyText: 'No attribute defined',
        minHeight: 150,
        // titleBar: false,
        bind: '{customAttributes}',
        listeners: {
          drop: 'onAttributesMoved'
        }
        // bind: {
        //   store: {
        //     data: '{record.attributes}'
        //   }
        // }
      });
    }
    this.add(items);
    this.callParent(arguments);
  },
  /**
   * when record is changed, list specific attributes
   * @param record 
   */
  updateRecord: function(record) {
    // if (record) {
    //   var groupAttributes = record.get('attributes');
    //   if (!Ext.isEmpty(groupAttributes)) {
    //     var fields = [{
    //       xtype: 'titlebar',
    //       title: 'Editable fields (label and value)',
    //       items: {
    //         text: 'Add field',
    //         iconCls: 'x-fa fa-plus-circle',
    //         align: 'right',
    //         handler: function() {
    //         }
    //       }
    //     }];
    //     for (var i=0; i < groupAttributes.length; i++) {
    //       if (groupAttributes[i].label == 'photo') {
    //         groupAttributes[i].label = 'Test de grande longueur de text pour un champ';
    //       }
    //       fields.push({
    //         xtype: 'textfield',
    //         label: groupAttributes[i].label,
    //         userCls: 'editable-label',
    //         value: groupAttributes[i].value,
    //         labelMinWidth: 100,
    //         labelWidth: 'auto',
    //         minWidth: 350,
    //         width: 'auto',
    //         triggers: {
    //           clear: {
    //             type: 'clear'
    //           },
    //           spindown: {
    //             type: 'spindown'
    //           },
    //           spinup: {
    //             type: 'spinup'
    //           },
    //           drop: {
    //             cls: 'drop-trigger',
    //             handler: function() {
    //               console.log('drop trigger clicked');
    //             }
    //           }
    //         }
    //       });
    //     }
    //   }
    //   this.getComponent('GroupAttributes').add({
    //     xtype: 'fieldset',
    //     userCls: 'editable-fieldset',
    //     minWidth: '50%',
    //     items: fields
    //   });
    //   // this.getComponent('GroupAttributes').add({
    //   //   xtype: 'grid',
    //   //   id: 'testgrid',
    //   //   minHeight: 200,
    //   //   plugins: {
    //   //     gridrowdragdrop: true
    //   //   },
    //   //   columns: [{
    //   //     text: 'Label',
    //   //     dataIndex: 'label',
    //   //     minWidth: 150,
    //   //     draggable: false
    //   //   },{
    //   //     text: 'Type',
    //   //     dataIndex: 'type',
    //   //     minWidth: 100,
    //   //     draggable: false
    //   //   },{
    //   //     text: 'Value',
    //   //     dataIndex: 'value',
    //   //     minWidth: 200,
    //   //     flex: 1,
    //   //     draggable: false
    //   //   }],
    //   //   store: groupAttributes
    //   // });
    // }
  }
});