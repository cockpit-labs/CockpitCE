Ext.define('Studio.view.document.DocumentController', {
  extend: 'Studio.view.base.CommonViewController',
  requires: [
    'Ext.MessageBox'
  ],
  alias: 'controller.document',
  attrCache: {},
  control: {
    'blocktree': {
      select: 'treeSelect',
      deselect: 'treeDeselect',
      beforedrop: 'beforeDrop',
      drop: 'drop'
    },
    'documentdialog': {
      beforehide: 'beforeHideDialog',
      hide: 'onHideDialog'
    },
    'container[itemId=docprop] field': {
      errorchange: 'onErrorChange'
    },
    'blockproperties field': {
      errorchange: 'onPropertiesErrorChange'
    },
    'button[action=save]': {
      tap: 'save'
    },
    'button[action=saveclose]': {
      tap: 'saveClose'
    },
    'button[action=revert]': {
      tap: 'revert'
    },
    'tool[action=moveUpItem]': {
      click: 'moveItem'
    },
    'tool[action=moveDownItem]': {
      click: 'moveItem'
    },
    'tool[action=removeItem]': {
      click: 'removeItem'
    },
    'button[action=addBlock]': {
      tap: 'addBlock'
    },
    'button[action=addQuestion]': {
      tap: 'addQuestion'
    },
    'menuitem[action=useBlock]': {
      click: 'useBlock'
    },
    'menuitem[action=cloneBlock]': {
      click: 'cloneBlock'
    },
    'menuitem[action=cloneQuestion]': {
      click: 'cloneQuestion'
    },
    'selectfield[name=item-alias]': {
      change: 'onQuestionTypeChange'
    },
    'radiogroup[itemId=display]': {
      change: 'onQuestionNonPersistentFieldChange'
    },
    'togglefield[itemId=multiselect]': {
      change: 'onQuestionNonPersistentFieldChange'
    },
    'togglefield[itemId=time]': {
      change: 'onQuestionNonPersistentFieldChange'
    },
    'textfield[name=item-trigger]': {
      change: 'onQuestionTriggerChange'
    },
    'container[itemId=numberspinners] spinnerfield': {
      change: 'onQuestionNonPersistentFieldChange'
    },
    'button[action=revertItemChanges]': {
      tap: 'revertItemChanges'
    }
  },
  /* tree events */
  beforeDrop: function(node, data, overModel, dropPosition, eOpts) {
    // disable tree selection to prevent automatic selection on drop node */
    this.lookupReference('blocktree').getSelectable().setDisabled(true);
    return true;
  },
  drop: function(node, data, overModel, dropPosition, eOpts) {
    // enable tree selection after dropping node */
    this.lookupReference('blocktree').getSelectable().setDisabled(false);
    this.rebuildBlockTpls();
    return true;
  },
  rebuildBlockTpls: function() {
    var vm = this.getViewModel();
    var record = vm.get('currentDocument');
    record.set('blockTpls', this.buildBlockTpls(true));
    vm.set('treeHasChanges', true);
    this.recalculateFakeChildForNode(this.lookupReference('blocktree').getRootNode(), true);
  },
  recalculateFakeChildForNode: function(node, recursive) {
    if (node && node.hasChildNodes()) {
      // all nodes must have at least a fake node
      node.set('hasFakeChild', node.childNodes.length < 2);
      var count = node.childNodes.length;
      if (recursive !== false) {
        for (var i = 0; i < count; i++) {
          this.recalculateFakeChildForNode(node.getChildAt(i));
        }  
      }
    }
  },
  treeSelect: function(tree, treeRecord) {
    this.getViewModel().set('currentItem', treeRecord);
  },
  treeDeselect: function(tree, treeRecords) {
    var selectedRecords = tree.getSelectable().getSelections();
    if (selectedRecords.length === 0) {
      this.clearItemProperties();
    } else {
      this.treeSelect(tree, selectedRecords);
    }
  },
  treeUpdate: function(store, record, operation, modifiedFieldNames, details, eOpts ) {
    if (!store.isLoading()) {
      if (record) {
        var vm = this.getViewModel();
        if (record.isDirty()) {
          if (modifiedFieldNames != null) {
            this.checkTreeState();
          }
        } else if (operation == Ext.data.Model.REJECT) {
          this.checkTreeState();
        }
      }
    }
  },
  /**
   * checkTreeState
   * @description check if store has no more dirty record, then change state
   */
  checkTreeState: function() {
    var vm = this.getViewModel();
    var store = vm.getStore('blocks');
    if ((store.getNewRecords().length === 0)
        && (store.getUpdatedRecords().length === 0)
        && (store.getRemovedRecords().length === 0)) {
      vm.set('treeHasChanges', false);
    } else {
      vm.set('treeHasChanges', true);
    }
  },
  /* end tree events */
  /**
   * buildBlockTpls
   * @param onlyReorder
   * @description rebuild tree with positions
   */
  buildBlockTpls: function(onlyReorder) {
    return this.buildNodes(this.getViewModel().getStore('blocks').getRoot(), onlyReorder);
  },
  buildNodes: function(node, onlyReorder) {
    var nodes = [];
    if (node && node.hasChildNodes()) {
      var isBlock = node.get('resource') == "BlockTpl";
      var children = node.childNodes;
      var count = children.length;
      var pos = 0;
      for (var i = 0; i < count; i++) {
        var child = node.getChildAt(i);
        if (child.get('fake') === true) {
          continue;
        }
        var resource = child.get('resource');
        var item = {
          position: ++pos
        };
        if (isBlock) {
          // fixes issue when moving child question under block (Cockpit#8)
          item.parent = null;
        }
        switch (resource) {
          case 'BlockTpl':
            item.questionTpls = this.buildNodes(child, onlyReorder);
            break;
          // case 'FakeBlockTpl':
          //   console.debug('Ignoring FakeBlockTpl');
          //   break;
          case 'QuestionTpl':
            // node is a Question
            if (!onlyReorder) {
              this.buildQuestion(item, child);
            }
            item.children = this.buildNodes(child, onlyReorder);
            break;
          // case 'FakeQuestionTpl':
          //   console.debug('Ignoring FakeQuestionTpl');
          //   break;
          default:
            // unknown node type
            console.error('Unknown resource type : ', resource);
            continue;
        }
        nodes.push(item);
        if (!onlyReorder && (child.isDirty() || child.isPhantom())) {
          this.copyAttrs(resource, item, child);
        }
        if (!child.isPhantom()) {
          item.id = child.get('id');
        }
      }
    }
    return nodes;
  },
  copyAttrs: function(resource, item, model) {
    if (!this.attrCache[resource]) {
      var fields = model.getCriticalFields();
      fields.shift();
      this.attrCache[resource] = Ext.Array.pluck(fields,'name').join(',');
    }
    if (model.isPhantom()) {
      Ext.copy(item, model.getData(), this.attrCache[resource]);
    } else {
      Ext.copy(item, model.getChanges(), this.attrCache[resource]);
    }
  },
  buildQuestion: function(item, model) {
    var questionType = model.get('alias');
    var writeRenderer = null;
    switch (questionType) {
      case 'none':
        writeRenderer = model.get('writeRenderer');
        break;
      case 'text':
        writeRenderer = model.get('writeRenderer');
        break;
      case 'yesno':
        writeRenderer = model.get('writeRenderer');
        writeRenderer.multiselect = model.get('multiselect');
        writeRenderer.display = model.get('display');
        break;
      case 'select':
        writeRenderer = model.get('writeRenderer');
        writeRenderer.multiselect = model.get('multiselect');
        writeRenderer.display = model.get('display');
        break;
      case 'number':
        writeRenderer = model.get('writeRenderer');
        writeRenderer.min = model.get('min');
        writeRenderer.max = model.get('max');
        writeRenderer.step = model.get('step');
        break;
      case 'range':
        writeRenderer = model.get('writeRenderer');
        writeRenderer.min = model.get('min');
        writeRenderer.max = model.get('max');
        writeRenderer.step = model.get('step');
        break;
      case 'dateTime':
        writeRenderer = model.get('writeRenderer');
        writeRenderer.time = model.get('time');
        break;
      default:
        // unknown question type
        console.error('Unknown question alias : ', questionType);
    }
    model.set('writeRenderer', writeRenderer);
    if (model.isDirty()) {
      if (model.isModified('externalUrlLabel') || model.isModified('externalUrlUrl')) {
        item.externalUrl = {
          url: model.get('externalUrlUrl'),
          label: model.get('externalUrlLabel'),
        };
      }
      // choices
      if (model.isModified('choiceTpls')) {
        var choices = model.get('choiceTpls');
        if (choices) {
          var itemChoices = [];
          for (var i=0; i < choices.length; i++) {
            var choice = choices[i];
            var itemChoice = {};
            if (!choice.isModel) {
              choice = Ext.create('Studio.model.Choice', choice);
            }
            if (choice.isPhantom() || choice.isDirty()) {
              this.copyAttrs('ChoiceTpl', itemChoice, choice);
            }
            if (!choice.isPhantom()) {
              itemChoice.id = choice.get('id');
            }
            itemChoices.push(itemChoice);
          }
          if (itemChoices.length > 0) {
            item.choiceTpls = itemChoices;
          }
        }
      }
    }
    return model;
  },
  /**
   * onErrorChange
   * @description handle empty mandatory fields
   */
  onErrorChange: function(field, error) {
    var vm = this.getViewModel();
    vm.set('hasError', vm.get('currentDocument') && !!error); 
  },
  onPropertiesErrorChange: function(field, error) {
    var vm = this.getViewModel();
    vm.set('hasError', vm.get('currentDocument') && vm.get('currentItem') && !!error); 
  },
  /**
   * saveClose
   * @description handle save + close action
   */
  saveClose: function() {
    this.save(true);
  },
  /**
   * save
   * @description handle save action
   */
  save: function(close) {
    var view = this.getView();
    view.setMasked({
      xtype: 'loadmask',
      message: 'Saving record...'
    });
    this.lookupReference('blocktree').getSelectable().deselectAll();
    var record = this.getViewModel().get('currentDocument');
    var wasPhantom = record.isPhantom();
    if (this.getViewModel().get('treeHasChanges') === true) {
      record.set('blockTpls', this.buildBlockTpls());
    }
    record.save({
      failure: function() {
        this.setMasked(false);
        Ext.Msg.alert("Error", "An error occured while saving record", Ext.emptyFn);
      },
      success: function(record, operation) {
        var me = this;
        me.setMasked(false);
        me.setRecordHasChanged(true);
        if (wasPhantom) {
          // if it was a document creation, then inform any listening parent
          me.fireEvent('savenew', me, record);
        }
        me.getViewModel().set('treeHasChanges', false);
        if (close === true) {
          me.close();
        } else {
          //me.getController().clearItemProperties();
          var store = me.getViewModel().getStore('blocks');
          store.load();
        }
      },
      scope: view
    });
  },
  /**
   * revert
   * @description revert document changes
   */
  revert: function() {
    var vm = this.getViewModel();
    vm.get('currentDocument').reject();
    if (vm.get('treeHasChanges') === true) {
      // reload tree when it has pending changes
      // TODO : may use checkTreeState to avoid reloading
      vm.getStore('blocks').load({
        callback: function(record, operation, success) {
          if (success) {
            vm.set('treeHasChanges', false);
            this.lookupReference('blocktree').getSelectable().deselectAll();
            this.clearItemProperties();
          }
        },
        scope: this
      });
    }
  },
  /**
   * revertItemChanges
   * @description revert block/question changes
   */
  revertItemChanges: function() {
    var vm = this.getViewModel();
    // reject choices changes
    vm.getStore('choices').rejectChanges();
    // keep tree modifications
    var record = vm.get('currentItem');
    var parentId = record.getChanges()['parentId'];
    // reject current item changes (Studio#38 silent = false means that treetore will be notified)
    vm.get('currentItem').reject(false);
    // choices store count must be checked
    vm.set('choiceBindRefresh', vm.get('choiceBindRefresh') + 1);
    if (parentId) {
      record.set('parentId', parentId);
    }
  },
  clearItemProperties: function() {
    this.getViewModel().set('currentItem', null);
  },
  addBlock: function() {
    var tree = this.lookupReference('blocktree');
    var root = tree.getRootNode();
    var block = Ext.create('Studio.model.BlockNode', {
      nodeType: 'Studio.model.BlockNode',
      hasFakeChild: true,
      children: [Studio.model.Question.getFake()]
    });
    // force dirty field
    block.set('label', 'New block');
    // if root has a fake blocknode
    root.insertChild(root.childNodes.length - 1,  block);
    // if root does not have fake blocknode
    // root.appendChild(root.childNodes.length - 1,  block);
    tree.getSelectable().select(block);
    var itemPreview = this.lookupReference('itempreview');
    itemPreview.focus(true);
  },
  addQuestion: function() {
    var vm = this.getViewModel();
    var selectedRecord = vm.get('currentItem');
    var question = Ext.create('Studio.model.Question', {
      nodeType: 'Studio.model.Question',
      hasFakeChild: true,
      children: [Studio.model.Question.getFake()]
    });
    // force dirty field
    question.set('label', 'New question');
    selectedRecord.appendChild(question);
    this.recalculateFakeChildForNode(selectedRecord, false);
    selectedRecord.expand(false, this.onCreateQuestion, this);
  },
  onCreateQuestion: function(childNodes) {
    this.lookupReference('blocktree').getSelectable().select(childNodes[childNodes.length - 1]);

    var itemPreview = this.lookupReference('itempreview');
    itemPreview.focus(true);
  },
  onQuestionTypeChange: function(field, newValue, oldValue) {
    var vm = this.getViewModel();
    var record = vm.get('currentItem');
    if (record && record.get('resource') === 'QuestionTpl') {
      var questionType = record.get('alias');
      if (record.isPhantom() || record.get('alias') !== newValue) {
        var writeRenderer;
        var choices = [];
        switch (newValue) {
          case 'none':
            writeRenderer = null;
            choices.push({label: '', position: 1, valueFormula:{}});
            break;
          case 'text':
            writeRenderer = {component: newValue};
            choices.push({label: '', position: 1, valueFormula:{}});
            break;
          case 'yesno':
            writeRenderer = {component:'select', display:'button'};
            choices.push({label: 'Yes', position: 1, valueFormula:{expression: "1"}}, {label: 'No', position: 2, valueFormula:{expression: "0"}});
            break;
          case 'select':
            writeRenderer = {component:newValue, display:'list', multiselect: false};
            choices.push({label: 'Choice 1', position: 1, valueFormula:{}}, {label: 'Choice 2', position: 2, valueFormula:{}});
            break;
          case 'number':
            writeRenderer = {component: newValue};
            choices.push({label: '', position: 1, alueFormula:{}});
            break;
          case 'range':
            writeRenderer = {component: newValue};
            choices.push({label: '', position: 1, valueFormula:{}});
            break;
          case 'dateTime':
            writeRenderer = {component: newValue};
            choices.push({label: '', position: 1, valueFormula:{}});
            break;
          default:
            // unknown question type
            console.error('Unknown question alias : ', newValue);
            return;
        }
        record.set('writeRenderer', writeRenderer);
        record.set('clonedChoices', false);
        record.set('choiceTpls', choices);
        vm.set('choiceBindRefresh', vm.get('choiceBindRefresh') + 1);
      }
    }
  },
  onQuestionTriggerChange: function(field, newValue, oldValue) {
    if (newValue == '') {
      field.setError(null);
      return;
    }
    var vm = this.getViewModel();
    var record = vm.get('currentItem');
    try {
      record.set('trigger', JSON.parse(newValue));
    } catch (exception) {
      field.setError('Invalid JSON object');
    }
  },
  onQuestionNonPersistentFieldChange: function(field, newValue, oldValue) {
    var vm = this.getViewModel();
    var record = vm.get('currentItem');
    if (record && record.get('resource') === 'QuestionTpl') {
      var fieldReference = field.getItemId();
      // issue with ExtJs7.2 for Spinnerfields field.getInputValue() != field.getValue()
      if (record.get(fieldReference) != newValue) {
        var writeRenderer = record.get('writeRenderer');
        // use of merge() instead of apply() else writeRenderer is not set as a modifiedField
        var value = Ext.merge({}, writeRenderer);
        if (value != null) {
          value[fieldReference] = newValue;        
        } else {
          delete value[fieldReference];
        }
        if (!Ext.Object.equals(writeRenderer)) {
          record.set('writeRenderer', value);
        }
      }
    }
  },
  cloneBlock: function(btn, ev) {
    // Ext.Msg.alert("Not implemented", "Cloning an existing block is not available", Ext.emptyFn);
    // return;
    this.useBlock(btn, ev, true);
  },
  useBlock: function(btn, ev, copy) {
    // Ext.Msg.alert("Not implemented", "Reusing an existing block is not available", Ext.emptyFn);
    // return;
    this.useObject({
      dialogTitle: 'Choose blocks',
      gridEmptyText: 'No block found',
      gridStoreType: 'blocks',
      clone: false,
      copy: (copy === true),
      filterSamples: (copy === true),
      filteredIds: (copy === true) ? [] : this.getViewModel().getStore('blocks').collect('id'),
      callback: this.afterUseBlock
    });
  },
  afterUseBlock: function(records) {
    // we need to use the reader to process data
    var result = this.getViewModel().getStore('blocks').getProxy().getReader().read({
      blockTpls: Ext.Array.pluck(records, 'data')
    });
    // then append children to parentnode
    this.lookupReference('blocktree').getRootNode().appendChild(result.getRecords());
  },
  cloneQuestion: function() {
    this.useObject({
      dialogTitle: 'Choose questions',
      gridEmptyText: 'No question found',
      gridStoreType: 'questions',
      // we just simulate cloning since questions will be created on next questionnaireTpl PATCH
      clone: false,
      copy: true,
      filterSamples: true,
      filteredIds: [],
      callback: this.afterCloneQuestion
    });
  },
  afterCloneQuestion: function(records) {
    // we need to use the reader to process data
    var result = this.getViewModel().getStore('blocks').getProxy().getReader().read({
      blockTpls: [
        {
          questionTpls: Ext.Array.pluck(records, 'data')
        }
      ]
    });
    var selectedRecords = this.lookupReference('blocktree').getSelectable().getSelections();
    if (selectedRecords.length) {
      var selectedNode = selectedRecords[0];
      var questions = result.getRecords()[0].get('children');
      questions.pop();
      selectedNode.appendChild(questions);
      this.rebuildBlockTpls();
      selectedNode.expand();
    }
  },
  moveItem: function(btn, el, cell) {
    var record = cell.getRecord();
    var parent = record.parentNode;
    var pos = parent.indexOf(record);
    // considering there is fake question in block, lastChild is the fake one
    var delta = btn.action == 'moveUpItem' ? (pos > 0 ? -1 : null) : (pos < parent.childNodes.length - 2 ? 2 : null);
    if (delta == null) {
      // just try to move item inside previous or nextsibling
      // find previous sibling
      if (parent.parentNode) {
        var index = parent.parentNode.indexOf(parent);
        if (btn.action == 'moveUpItem') {
          parent = parent.parentNode.getChildAt(index - 1);
          pos = parent ? parent.childNodes.length - 1 : -1;
          delta = pos < 0 ? null : 0;
        } else {
          parent = parent.parentNode.getChildAt(index + 1);
          pos = parent ? 0 : -1;
          delta = pos < 0 ? null : 0;
        }
      }
    }
    if (delta !== null && !parent.get('fake')) {
      parent.insertChild(pos + delta, record);
      this.rebuildBlockTpls();
    }
  },
  removeItem: function(btn, el, cell) {
    var record = cell.getRecord();
    var tree = record.getOwnerTree();
    if (tree.getSelection() == record) {
      tree.getSelectable().deselectAll();
    }
    var parent = record.parentNode;
    record.remove();
    record.destroy();
    if (!parent.isRoot()) {
      this.recalculateFakeChildForNode(parent, false);
    }
    this.checkTreeState();
  },
  /**
   * beforeTreeStoreLoad
   * @description prevent tree store loading when the document is being created
   */
  beforeTreeStoreLoad: function() {
    return !this.getViewModel().get('currentDocument').isPhantom();
  },
  /**
   * beforeHideDialog
   * @description handles dialog beforehide event. Ask user to confirm action.
   */
  beforeHideDialog: function(cmp) {
    var me = this;
    var vm = this.getViewModel();
    var record = vm.get('currentDocument');
    if (record && (record.isDirty() || record.isPhantom() || vm.get('treeHasChanges'))) {
      Ext.Msg.confirm(
          'Close dialog ?',
          'Current modifications will be cancelled. Are you sure you want to close dialog ?',
          function(btn) {
            if (btn == 'yes') {
              record.reject();
              me.clearItemProperties();
              vm.set('currentDocument', null);
              vm.set('treeHasChanges', false);
              cmp.close();
            }
          }
      );
      return false;
    }
    me.clearItemProperties();
    return true;
  },
  /**
   * onHideDialog
   * @description empty tree store when dialog is closed
   */
  onHideDialog: function() {
    this.getViewModel().getStore('blocks').removeAll();
  },
  onChoicesUpdate: function(store, choice, type) {
    var vm = this.getViewModel();
    if (type == Ext.data.Model.REJECT) {
      // do nothing when event is fired for a rejected record
      // else we would recalculate positions
      return;
    } else {
      var record = vm.get('currentItem');
      var newChoices = [];
      var doCloneChoices = !record.get('clonedChoices');
      store.each(function(rec, index) {
        var clone = doCloneChoices ? rec.clone() : rec;
        newChoices.push(clone);
        if (clone.get('position') != (index + 1)) {
          clone.set('position', index + 1);
        }
      });
      if (doCloneChoices) {
        record.set('clonedChoices', true);
      }
      record.set('choiceTpls', newChoices);
      vm.set('choiceBindRefresh', vm.get('choiceBindRefresh') + 1);
    }
  }
});
