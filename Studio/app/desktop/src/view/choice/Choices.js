Ext.define('Studio.view.choice.Choices', {
  extend: 'Studio.view.base.SimpleGrid',
  xtype: 'choices',
  alias: 'widget.choices',
  userCls: 'c-choices',
  requires: [
    'Studio.view.choice.ChoicesController'
  ],
  titleBar: {
    items: {
      align: 'right',
      iconCls: 'x-fa fa-question-circle',
      action: 'help'
    }
  },
  controller: 'choices',
  emptyText: 'No choice defined',
  markDirty: true,
  itemConfig: {
    viewModel: true
  },
  items: [{
    docked: 'top',
    xtype: 'toolbar',
    items:[{
      xtype: 'button',
      action: 'create',
      text: 'Add',
      userCls: 'c-add-choice',
      iconCls: 'x-fa fa-plus-circle',
      tooltip: 'Create a choice'
    }]
  }],
  columns:[{
    text: 'Label',
    dataIndex: 'label',
    flex: 1
  },{
    text: 'Score',
    dataIndex: 'expression',
    width: 250
  },{
    width: 112,
    resizable: false,
    cell: {
      selectable: false,
      tools: {
        edit: {
          iconCls: 'x-fa fa-pencil-alt',
          userCls: 'choice-edit',
          action: 'edit',
          tooltip: 'Edit choice'
        },
        up: {
          iconCls: 'x-fa fa-arrow-up',
          userCls: 'choice-up',
          action: 'up',
          bind: {
            disabled: '{record.position == 1}'
          },
          tooltip: 'Move up choice'
        },
        down: {
          iconCls: 'x-fa fa-arrow-down',
          userCls: 'choice-down',
          action: 'down',
          bind: {
            disabled: '{record.position == choicesCount}' // here we use the owner ViewModel 'choicesCount' formula to get store count
          },
          tooltip: 'Move down choice'
        },
        remove: {
          iconCls: 'x-fa fa-trash',
          userCls: 'choice-delete',
          action: 'delete',
          tooltip: 'Delete choice'
        }
      }
    }
  }]
});