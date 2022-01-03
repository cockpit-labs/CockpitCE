Ext.define('Studio.view.document.DocumentDialog', {
  extend: 'Ext.Panel',
  xtype: 'documentdialog',
  userCls: 'c-document',
  alias: 'widget.documentdialog',
  requires: [
    'Studio.view.document.DocumentController',
    'Studio.view.document.DocumentModel',
    'Studio.view.block.BlockTree',
    'Ext.Toolbar'
  ],
  controller: 'document',
  viewModel: {
    type: 'document'
  },
  config: {
    recordHasChanged: false
  },
  defaultFocus: 'textfield[itemId=doclabel]',
  iconCls: 'x-fa fa-cubes',
  border: true,
  floated: true,
  modal: true,
  centered: true,
  bind: {
    closable: '{!blocks.loading}'
  },
  closeAction: 'hide',
  layout: 'vbox',
  showAnimation: null,
  items: [{
    xtype: 'blocktree',
    reference: 'blocktree',
    hideHeaders: true,
    flex: 1,
    rootVisible: true,
    bind: '{blocks}',
    items: [{
      xtype: 'container',
      docked: 'top',
      cls: 'c-doc-prop-ct',
      itemId: 'docprop',
      defaults: {
        margin: '0 10 10 10' 
      },
      layout: {
        type: 'hbox',
        align: 'stretch',
        pack: 'start'
      },
      items: [{
        xtype: 'textfield',
        label: 'Label',
        itemId: 'doclabel',
        required: true,
        name: 'label',
        bind: '{currentDocument.label}',
        errorTarget: 'side',
        width: 300
      },
      {
        xtype: 'textfield',
        flex: 1,
        label: 'Description',
        name: 'description',
        bind: '{currentDocument.description}'
      }]
    }, {
      xtype: 'toolbar',
      docked: 'top',
      items: [{
        xtype: 'button',
        text: 'Add Block',
        iconCls: 'x-fa fa-cubes',
        action: 'addBlock',
        tooltip: 'Create a new block of questions'
      },{
        xtype: 'button',
        text: 'Add Question',
        iconCls: 'x-fa fa-cube',
        action: 'addQuestion',
        bind: {
          disabled: '{!treeHasSelection}'
        },
        tooltip: 'Create a new question'
      },{
        xtype: 'button',
        text: 'Use existing',
        iconCls: 'x-fa fa-copy',
        tooltip: 'Duplicate an existing block or question',
        menu: [{
        //   text: 'Reuse Block...',
        //   iconCls: 'x-fa fa-cubes',
        //   action: 'useBlock'
        // },{
          text: 'Clone Block...',
          iconCls: 'x-fa fa-cubes',
          action: 'cloneBlock',
          tooltip: 'Duplicate an existing block'
        },{
          text: 'Clone Question...',
          iconCls: 'x-fa fa-copy',
          action: 'cloneQuestion',
          bind: {
            disabled: '{!blockSelected && !questionSelected}'
          },
          tooltip: 'Duplicate an existing question'
        }]
      }]
    },{
      xtype: 'blockproperties',
      reference: 'itempreview',
      title: 'Details',
      docked: 'right',
      border: true,
      bodyPadding: 10,
      width: '50%',
      bind: {
        record: '{currentItem}'
      }
    }]
  },{
    xtype: 'toolbar',
    layout: {
      type: 'hbox',
      pack: 'center'
    },
    docked: 'bottom',
    defaults: {
      ui: 'action'
    },
    bind: {
      hidden: '{blocks.loading}'
    },
    items: [{
      text: 'Revert changes',
      userCls: 'revert-doc-btn',
      iconCls: 'md-icon-undo',
      action: 'revert',
      disabled: true,
      bind: {
        disabled: '{!currentDocument.dirty && !treeHasChanges}'
      },
      tooltip: 'Revert changes'
    },{
      text: 'Save',
      userCls: 'save-doc-btn',
      iconCls: 'x-fa fa-save',
      action: 'save',
      disabled: true,
      bind: {
        disabled: '{hasError || (!treeHasChanges && !currentDocument.phantom && !currentDocument.dirty)}'
      },
      tooltip: 'Save changes'
    },{
      text: 'Save and close',
      userCls: 'saveclose-doc-btn',
      iconCls: 'x-fa fa-save',
      action: 'saveclose',
      disabled: true,
      bind: {
        disabled: '{hasError || (!treeHasChanges && !currentDocument.phantom && !currentDocument.dirty)}'
      },
      tooltip: 'Save changes and close dialog'
    }]
  }]
});