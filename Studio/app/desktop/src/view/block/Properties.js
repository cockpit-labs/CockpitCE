Ext.define('Studio.view.block.Properties', {
  extend: 'Ext.form.Panel',
  requires: [
    'Studio.view.choice.Choices',
    'Ext.field.Container',
    'Ext.field.Url',
    'Ext.data.validator.Url'
  ],
  xtype: 'blockproperties',
  cls: 'c-itemproperties',
  alias: 'widget.blockproperties',
  defaultFocus: 'textfield[itemId=itemlabel]',
  markDirty: true,
  layout: 'vbox',
  items:[{
    xtype: 'label',
    style: 'font-weight: bold; font-size: larger;',
    bind: {
      html: '{detailsHeader}'
    }
  },{
    xtype: 'textfield',
    label: 'Label',
    itemId: 'itemlabel',
    name: 'item-label',
    required: true,
    bind: {
      disabled: '{!currentItem}',
      value: '{currentItem.label}'
    }
  },{
    xtype: 'textfield',
    label: 'Description',
    name: 'item-description',
    bind: {
      disabled: '{!currentItem}',
      value: '{currentItem.description}'
    }
  },{
    xtype: 'fieldset',
    hidden: true,
    cls: 'prop-fieldset',
    bind: {
      hidden: '{!currentItem || currentItem.resource == "BlockTpl"}'
    },
    items: [{
      xtype: 'containerfield',
      label: 'External URL',
      name: 'item-externalUrl',
      items: [{
          name: 'externalUrlLabel',
          placeholder: 'Link label',
          bind: {
            value: '{currentItem.externalUrlLabel}'
          }
      }, {
          flex: 1,
          xtype: 'urlfield',
          margin: '0 0 0 10',
          name: 'externalUrlUrl',
          placeholder: 'URL (example : https://mydomain.com/file.pdf)',
          errorTarget: 'side',
          validators: {
            type: 'url'
          },
          bind: {
            value: '{currentItem.externalUrlUrl}'
          }
      }]
    },{
      xtype: 'container',
      layout: {
        type: 'hbox',
        pack: 'space-between'
      },
      flex: 1,
      margin: '0 5 0 0',
      items: [{
        xtype: 'selectfield',
        width: 120,
        label: 'Type',
        name: 'item-alias',
        displayField: 'name',
        valueField: 'id',
        bind: {
          value: '{currentItem.alias}',
        },
        store: 'QuestionTypes',
        floatedPicker: {
          xtype: 'boundlist',
          infinite: false,
          navigationModel: {
              disabled: true
          },
          scrollToTopOnRefresh: false,
          loadingHeight: 70,
          maxHeight: 300,
          floated: true,
          axisLock: true,
          hideAnimation: null,
          itemConfig: {
            cls: Ext.baseCSSPrefix + 'boundlistitem'
          }
        }
       },{
        xtype: 'spinnerfield',
        label: 'Weight',
        name: 'item-weight',
        minValue: 0,
        bind: '{currentItem.weight}',
        width: 100
      },{
        xtype: 'spinnerfield',
        label: 'Max. photos',
        name: 'item-maxphotos',
        minValue: 0,
        bind: '{currentItem.maxPhotos}',
        width: 100
      },{
        xtype: 'togglefield',
        label: 'Mandatory',
        name: 'item-mandatory',
        bind: '{currentItem.mandatory}'
      },{
        xtype: 'togglefield',
        label: 'Has comment',
        name: 'item-hascomment',
        bind: '{currentItem.hasComment}'
      },{
        xtype: 'togglefield',
        label: 'Hide label',
        name: 'item-hiddenlabel',
        bind: '{currentItem.hiddenLabel}'
      }]
    },{
      xtype: 'fieldset',
      hidden: true,
      cls: 'prop-fieldset',
      layout: {
        type: 'hbox'
      },
      bind: {
        hidden: '{hideSpecificDetails}'
      },
      items: [{
        xtype: 'radiogroup',
        name: 'item-display',
        itemId: 'display',
        label: 'Display as',
        labelAlign: 'left',
        bind: {
          value: '{currentItem.display}',
          hidden: '{hideSelectDetails}'
        },
        items: [
          {label: 'Buttons', name: 'display', value: 'button'},
          {label: 'List', name: 'display', value: 'list'}
        ]
      },{
        xtype: 'togglefield',
        name: 'item-multiselect',
        itemId: 'multiselect',
        label: 'Multiselection',
        labelAlign: 'left',
        bind: {
          value: '{currentItem.multiselect}',
          hidden: '{hideSelectDetails}'
        }
      },{
        xtype: 'container',
        itemId: 'numberspinners',
        layout: {
          type: 'hbox'
        },
        bind: {
          hidden: '{hideNumberDetails}'
        },
        items: [{
          xtype: 'spinnerfield',
          name: 'item-min',
          itemId: 'min',
          label: 'Minimum',
          bind: {
            value: '{currentItem.min}',
            maxValue: '{currentItem.max}',
            decimals: '{numberPrecision}'
          },
          width: 120
        },{
          xtype: 'spinnerfield',
          name: 'item-max',
          itemId: 'max',
          label: 'Maximum',
          bind: {
            value: '{currentItem.max}',
            minValue: '{currentItem.min}',
            decimals: '{numberPrecision}'
          },
          width: 120
        },{
          xtype: 'spinnerfield',
          name: 'item-step',
          itemId: 'step',
          label: 'Step',
          minValue: 0,
          bind: {
            value: '{currentItem.step}',
            decimals: '{numberPrecision}'
          },
          width: 120
        },{
          xtype: 'spinnerfield',
          name: 'item-precision',
          itemId: 'precision',
          label: 'Precision',
          bind: {
            value: '{numberPrecision}'
          },
          hidden: true
        }]
      },{
        xtype: 'togglefield',
        name: 'item-time',
        itemId: 'time',
        label: 'With time selector ?',
        bind: {
          value: '{currentItem.time}',
          hidden: '{currentItem.alias != "dateTime"}'
        }
      }]
    // },{
    //   xtype: 'textfield',
    //   label: 'Write renderer',
    //   name: 'item-writerenderer',
    //   itemId: 'writerenderer',
    //   disabled: true,
    //   bind: {
    //     value: '{currentItem.writeRendererStr}',
    //     hidden: '{currentItem.alias == "none"}'
    //   }
    },{
      xtype: 'textfield',
      label: 'Trigger',
      name: 'item-trigger',
      bind: {
        value: '{currentItem.triggerStr}'
      }
    }]
  }, {
    xtype: 'container',
    layout: 'auto',
    items: {
      xtype: 'choices',
      title: 'Choices',
      hidden: true,
      flex: 1,
      minHeight: 265,
      bind: {
        hidden: '{!currentItem || currentItem.resource == "BlockTpl" || hideChoices}',
        store: '{choices}'
      }
    }
  }],
  buttonToolbar: {
    userCls: 'item-actions',
    docked: ''
  },
  buttons: [{
    text: 'Revert changes',
    userCls: 'c-revert-item',
    iconCls: 'md-icon-undo',
    action: 'revertItemChanges',
    hidden: true,
    bind: {
      hidden: '{hideRevertItemButton}'
    },
    tooltip: 'Revert changes of current item only'
  }]
});