Ext.define('Studio.view.document.DocumentModel', {
  extend: 'Ext.app.ViewModel',
  alias: 'viewmodel.document',
  data: {
    currentDocument: null,
    currentItem: null,
    treeHasChanges: false,
    numberPrecision: 10,
    choiceBindRefresh: 1
  },
  formulas: {
    treeHasSelection: function(get) {
      var record = get('currentItem');
      return (record != null && !record.get('fake'));
    },
    blockSelected: function(get) {
      var record = get('currentItem');
      return record && record.get('resource') == 'BlockTpl';
    },
    questionSelected: function(get) {
      var record = get('currentItem');
      return record && record.get('resource') == 'QuestionTpl';
    },
    detailsHeader: function(get) {
      return get('treeHasSelection') ? null : 'Please select an item to see details!';
    },
    hideRevertItemButton: {
      bind: {
        treeHasSelection: '{treeHasSelection}',
        generation: '{currentItem.generation}', // force binding refresh on each modification
        currentItemDirty: '{currentItem.dirty}'
      },
      get: function(data) {
        if (!data.treeHasSelection || !data.currentItemDirty) {
          return true;
        } else if (data.currentItemDirty) {
          var currentItem = this.get('currentItem');
          if (currentItem) {
            var changes = currentItem.getChanges();
            return (changes && changes.parentId && Object.keys(changes).length == 1);
          }
          return true;
        }
      }
    },
    choicesCount: {
      bind: {
        // currentItem binding is needed to make Choices arrow up/down correct refresh
        currentItem: '{currentItem}',
        // choiceBindRefresh is needed to make Choices arrow up/down correct refresh after deleting choice
        choiceBindRefresh: '{choiceBindRefresh}'
      },
      get: function (data) {
        var record = data.currentItem;
        if (record && (record.get('resource') == 'QuestionTpl')) {
          var choices = record.get('clonedChoiceTpls');
          if (!choices) {
            choices = record.get('choiceTpls');
          }
          return choices ? choices.length : 0;
        }
        return 0;
      }
    },
    hideSpecificDetails: {
      bind: {
        selected: '{questionSelected}',
        alias: '{currentItem.alias}'
      },
      get: function(data) {
        if (data.selected) {
          switch (data.alias) {
            case 'none':
            case 'text':
              return true;
            default:
              return false;
          }
        }
        return true;
      }
    },
    hideNumberDetails: {
      bind: {
        selected: '{questionSelected}',
        alias: '{currentItem.alias}'
      },
      get: function(data) {
        if (data.selected) {
          switch (data.alias) {
            case 'number':
            case 'range':
              return false;
          }
        }
        return true;
      }
    },
    hideSelectDetails: {
      bind: {
        selected: '{questionSelected}',
        alias: '{currentItem.alias}'
      },
      get: function(data) {
        if (data.selected) {
          switch (data.alias) {
            case 'yesno':
            case 'select':
              return false;
          }
        }
        return true;
      }
    },
    hideChoices: {
      bind: {
        selected: '{questionSelected}',
        alias: '{currentItem.alias}'
      },
      get: function(data) {
        if (data.selected) {
          return (data.alias != 'select');
        }
        return true;
      }
    }
  },
  stores: {
    blocks: {
      type: 'tree',
      model: 'Studio.model.Document',
      defaultRootId: '{currentDocument.id}', // use documentId as root id (endpoint /questionnaire_tpls/{document_id} will return the whole tree)
      trackRemoved: false,
      clearRemovedOnLoad: false, // prevent nullpointer in TreeStore.flushLoad (TreeStore.js:1858)
      rootVisible: false,
      filters: [function(item) {
        // if (item.parentNode && item.get('resource') === 'FakeQuestionTpl') {
          // never display Block fake node (just exists to simplify the move action)
        if (item.parentNode && item.get('fake') === true) {
          if (item.get('resource') === 'FakeBlockTpl') {
            return false;
          } else {
            // only show fake question when it is the only child
            return item.parentNode.firstChild == item && item.parentNode.lastChild == item;
          }
        }
        return true;
        // return true;
      }],
      sorters: ['position'],
      proxy: {
        type: 'rest',
        url: '/api/questionnaire_tpls',
        writer: {
          writeRecordId: false
        },
        reader: {
          type: 'document'
        }
      },
      listeners: {
        beforeload: 'beforeTreeStoreLoad',
        update: 'treeUpdate'
      }
    },
    choices: {
      model: 'Studio.model.Choice',
      data: '{currentItem.choiceTpls}',
      listeners: {
        update: 'onChoicesUpdate',
        remove: 'onChoicesUpdate',
        add: 'onChoicesUpdate'
      }
    }
  }
});
