Ext.define('Studio.model.Document', {
  extend: 'Ext.data.Model',
  requires: [
    'Studio.reader.Document',
    'Ext.data.validator.Presence'
  ],
  convertOnSet: false,
  fields: [
    {
      name: 'resource',
      type: 'string',
      convert: null,
      persist: false
    },
    {
      name: 'locale',
      type: 'string',
      convert: null,
      persist: false
    },
    {
      name: 'label',
      type: 'string',
      convert: null,
      defaultValue: '',
      validators:[{
        type: 'presence',
        message: 'Label is mandatory'
      }]
    },
    {
      name: 'description',
      type: 'string',
      convert: null,
      defaultValue: ''
    },
    {
      name: 'position',
      type: 'number',
      convert: null,
      persist: false
    },
    {
      // only used when showing an empty Tree for questionnaire
      name: 'leaf',
      type: 'boolean',
      persist: false
    },
    'folderTpls',
    {
      name: 'tplQuestionnaireBlocks',
      convert: null,
      persist: false
    },
    'blockTpls',
    // 'tplBlocks' will be replaced by 'children' while reading data for use in tree
    'children'
  ],
  proxy: {
    type: 'rest',
    url: '/api/questionnaire_tpls',
    writer: {
      writeRecordId: false
    }
  },
  /* Need to process blocks within the document */
  copy: function(newId, session) {
    var copy = this.callParent(arguments);
    var blockTpls = copy.data.blockTpls;
    for (var i = 0; i < blockTpls.length; i++) {
      Studio.model.Block.cleanClone(blockTpls[i]);
    }
    return copy;
  }
});
