Ext.define('Studio.model.Question', {
  extend: 'Ext.data.TreeModel',
  convertOnSet: false,
  statics: {
    getFake: function(position) {
      return {
        resource: 'FakeQuestionTpl',
        nodeType: 'Studio.model.Question',
        label: 'Drop any question here...',
        hasFakeChild: false,
        fake: true,
        iconCls: 'x-fa fa-download',
        position: position,
        leaf: true
      };
    },
    cleanClone: function(data) {
      delete data['id'];
      delete data['parent'];
      delete data['defaultChoiceTpl'];
      delete data['blockTpl'];
      var choices = data.choiceTpls;
      if (choices) {
        for (var i = 0; i < choices.length; i++) {
          Studio.model.Choice.cleanClone(choices[i]);
        }
      }
      var children = data.children;
      if (children) {
        for (var i = 0; i < children.length; i++) {
          Studio.model.Question.cleanClone(children[i]);
        }
      }
    }
  },
  fields: [
    {
      name: 'iconCls',
      type: 'string',
      convert: null,
      defaultValue: 'x-fa fa-cube'
    },
    {
      name: 'label',
      type: 'string',
      convert: null,
      critical: true
    },
    {
      name: 'description',
      type: 'string',
      convert: null,
      critical: true
    },
    {
      name: 'externalUrl',
      type: 'auto',
      convert: null,
      critical: true
    },
    {
      name: 'externalUrlUrl',
      type: 'string',
      convert: function (value, record) {
        var obj = record.get('externalUrl');
        return obj ? obj.url : '';
      },
      depends: 'externalUrl'
    },
    {
      name: 'externalUrlLabel',
      type: 'string',
      convert: function (value, record) {
        var obj = record.get('externalUrl');
        return obj ? obj.label : '';
      },
      depends: 'externalUrl'
    },
    {
      name: 'resource',
      type: 'string',
      convert: null,
      defaultValue: 'QuestionTpl'
    },
    {
      name: 'hiddenLabel',
      type: 'boolean',
      defaultValue: false,
      critical: true
    },
    {
      name: 'mandatory',
      type: 'boolean',
      defaultValue: false,
      critical: true
    },
    {
      name: 'hasComment',
      type: 'boolean',
      defaultValue: false,
      critical: true
    },
    {
      // calculated field
      name: 'isScored',
      type: 'boolean',
      convert: function (value, record) {
        return !!record.get('weight');
      },
      depends: 'weight'
    },
    {
      // calculated field
      name: 'hasPhoto',
      type: 'boolean',
      calculate: function (data) {
        return data.maxPhotos > 0;
      }
    },
    {
      name: 'position',
      type: 'number',
      defaultValue: 1
    },
    {
      name: 'weight',
      type: 'number',
      defaultValue: 1,
      critical: true
    },
    {
      name: 'minPhotos',
      type: 'number',
      defaultValue: 0,
      critical: true
    },
    {
      name: 'maxPhotos',
      type: 'number',
      defaultValue: 0,
      critical: true
    },
    {
      name: 'readRenderer',
      type: 'auto',
      critical: true
    },
    {
      // calculated field
      name: 'readRendererStr',
      type: 'string',
      convert: function (value, record) {
        var readRenderer = record.get('readRenderer');
        return Ext.isObject(readRenderer) ? JSON.stringify(readRenderer) : null;
      },
      depends: 'readRenderer'
    },
    {
      name: 'writeRenderer',
      type: 'auto',
      critical: true,
      defaultValue: {component:'text'}
    },
    {
      name: 'alias',
      type: 'string',
      convert: null,
      critical: true,
      defaultValue: 'text'
    },
    // {
    //   // calculated field
    //   name: 'writeRendererStr',
    //   type: 'string',
    //   convert: function (value, record) {
    //     var writeRenderer = record.get('writeRenderer');
    //     return Ext.isObject(writeRenderer) ? JSON.stringify(writeRenderer) : null;
    //   },
    //   depends: 'writeRenderer'
    // },
    {
      name: 'validator',
      type: 'auto',
      critical: true
    },
    {
      // calculated field
      name: 'validatorStr',
      type: 'string',
      convert: function (value, record) {
        var validator = record.get('validator');
        return Ext.isObject(validator) ? JSON.stringify(validator) : null;
      },
      depends: 'validator'
    },
    {
      name: 'choiceTpls',
      type: 'auto', // array,
      defaultValue: [{label: '', position: 1, valueFormula:{}}]
    },
    {
      name: 'defaultChoice',
      type: 'auto',
      critical: true
    },
    {
      name: 'children',
      type: 'auto'
    },
    {
      name: 'trigger',
      type: 'auto',
      critical: true
    },
    {
      // calculated field
      name: 'triggerStr',
      type: 'string',
      convert: function (value, record) {
        var trigger = record.get('trigger');
        return Ext.isObject(trigger) ? JSON.stringify(trigger) : null;
      },
      depends: 'trigger'
    },
    {
      name: 'display', type: 'string', persist: false,
      convert: function (value, record) {
        var writeRenderer = record.get('writeRenderer');
        return Ext.isObject(writeRenderer) ? writeRenderer.display : null;
      },
      depends: 'writeRenderer'
    },
    {
      name: 'min', type: 'number', persist: false,
      convert: function (value, record) {
        var writeRenderer = record.get('writeRenderer');
        return Ext.isObject(writeRenderer) ? writeRenderer.min : null;
      },
      depends: 'writeRenderer'
    },
    {
      name: 'max', type: 'number', persist: false,
      convert: function (value, record) {
        var writeRenderer = record.get('writeRenderer');
        return Ext.isObject(writeRenderer) ? writeRenderer.max : null;
      },
      depends: 'writeRenderer'
    },
    {
      name: 'step', type: 'number', persist: false,
      convert: function (value, record) {
        var writeRenderer = record.get('writeRenderer');
        return Ext.isObject(writeRenderer) ? writeRenderer.step : null;
      },
      depends: 'writeRenderer'
    },
    {
      name: 'multiselect', type: 'boolean', persist: false,
      convert: function (value, record) {
        var writeRenderer = record.get('writeRenderer');
        return Ext.isObject(writeRenderer) ? (writeRenderer.multiselect || false) : false;
      },
      depends: 'writeRenderer'
    },
    {
      name: 'time', type: 'boolean', persist: false,
      convert: function (value, record) {
        var writeRenderer = record.get('writeRenderer');
        return Ext.isObject(writeRenderer) ? (writeRenderer.time || false) : false;
      },
      depends: 'writeRenderer'
    },
    'parent',
    {
      name: 'clonedChoices',
      type: 'bool'
    },
    {
      name: 'hasFakeChild',
      type: 'bool',
      persist: false
    },
    {
      // attribute used to simulate a question in Tree, allowing to easily drop a node inside a leaf
      name: 'fake', type: 'bool', persist: false
    }
  ],
  proxy: {
    type: 'rest',
    url: '/api/question_tpls',
    writer: {
      writeRecordId: false
    }
  },
  /* Need to process everything within the question */
  copy: function(newId, session) {
    var copy = this.callParent(arguments);
    Studio.model.Question.cleanClone(copy.data);
    return copy;
  }
});
