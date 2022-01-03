Ext.define('Studio.model.Block', {
  extend: 'Ext.data.Model',
  convertOnSet: false,
  statics: {
    cleanClone: function(data) {
      delete data['id'];
      var questionTpls = data['questionTpls'];
      if (questionTpls) {
        for (var i = 0; i < questionTpls.length; i++) {
          Studio.model.Question.cleanClone(questionTpls[i]);
        }
      }
    }
  },
  fields: [
    {
      name: 'resource',
      type: 'string',
      convert: null,
      defaultValue: 'BlockTpl'
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
    'questionTpls',
    {
      name: 'sample',
      type: 'bool'
    }
  ],
  proxy: {
    type: 'rest',
    url: '/api/block_tpls',
    writer: {
      writeRecordId: false
      //writeAllFields: true
    },
    // reader: {
    //   type: 'blocks',
    //   typeProperty: 'nodeType'
    // }
  },
  /* Need to process everything within the block */
  copy: function(newId, session) {
    var copy = this.callParent(arguments);
    var data = copy.data;
    Studio.model.Block.cleanClone(data);
    var questionTpls = data.questionTpls;
    for (var i = 0; i < questionTpls.length; i++) {
      Studio.model.Question.cleanClone(questionTpls[i]);
    }
    return copy;
  }
});
