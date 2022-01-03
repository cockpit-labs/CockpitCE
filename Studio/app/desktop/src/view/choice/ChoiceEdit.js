Ext.define('Studio.view.choice.ChoiceEdit', {
  extend: 'Studio.view.base.Properties',
  xtype: 'choiceedit',
  alias: 'widget.choiceedit',
  requires: [
    'Ext.field.File',
    'Ext.field.trigger.File',
    'Ext.panel.Resizer',
    'Studio.ux.field.HelpedText'
  ],
  viewModel: {
    type: 'base',
    data: {
      media: null,
      defaultMedia: 'resources/no-image.jpg'
    }
  },
  controller: 'choiceedit',
  userCls: 'c-choiceedit',
  title: 'Edit choice',
  defaultFocus: 'textfield[itemId=choicelabel]',
  iconCls: 'x-fa fa-pencil-alt',
  border: true,
  floated: true,
  modal: true,
  closable: true,
  centered: true,
  resizable: {
    edges: 'all'
  },
  layout: {
    type: 'vbox',
    align: 'stretch',
    pack: 'justify'
  },
  items: [{
    xtype: 'textfield',
    label: 'Label',
    itemId: 'choicelabel',
    clearable: true,
    name: 'choice-label',
    errorTarget: 'side',
    bind: {
      value: '{record.label}'
    }
  },{
    xtype: 'helptextfield',
    label: 'Score expression',
    itemId: 'choicevalue',
    name: 'choice-value',
    clearable: true,
    bind: {
      value: '{record.expression}'
    },
    triggers: {
      help: {
        type: 'help'
      }
    }
  },
  {
    xtype: 'container',
    usercls: 'choice-upload-ct',
    flex: 1,
    layout: {
      type: 'vbox',
      align: 'center',
      pack: 'center'
    },
    items: [{
      xtype: 'component',
      userCls: 'choice-image',
      bind: {
        html: '<img class="choice-media" src="{media || defaultMedia}"/>'
      }
    },{
      xtype: 'filefield',
      label: "Image",
      name: 'photo',
      accept: 'image'
    }]
  }]
});