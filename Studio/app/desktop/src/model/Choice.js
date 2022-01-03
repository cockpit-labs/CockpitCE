Ext.define('Studio.model.Choice', {
  extend: 'Ext.data.Model',
  requires: [
    'Ext.data.validator.Presence'
  ],
  convertOnSet: false,
  statics: {
    cleanClone: function(data) {
      delete data['id'];
      delete data['questionTpl'];
    }
  },
  constructor: function(data, session, skipStoreAddition) {
    // we need to clone the data otherwise Question.choiceTpls === Question.modified.choiceTpls and the revert won't work
    var newData = Ext.clone(data);
    this.callParent([newData, session, skipStoreAddition]);
  },
  fields: [
    {
      name: 'id',
      type: 'string',
      convert: null
    },
    {
      name: 'label',
      type: 'string',
      convert: null,
      critical: true
    },
    {
      name: 'position',
      type: 'number',
      convert: null,
      critical: true
    },
    {
      name: 'valueFormula',
      type: 'auto',
      critical: true,
      convert: function (value) {
        if (!Ext.isObject(value)) {
          return {};
        }
        return value;
      }
    },
    {
      name: 'expression',
      type: 'string',
      persist: false,
      convert: function (value, record) {
        var valueFormula = record.get('valueFormula');
        return Ext.isObject(valueFormula) && valueFormula.expression ? valueFormula.expression : '';
      },
      depends: 'valueFormula'
    },
    {
      name: 'media',
      critical: true
    }
  ]
});
