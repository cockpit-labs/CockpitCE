Ext.define('Studio.model.BlockNode', {
  //extend: 'Ext.data.Model',
  extend: 'Ext.data.TreeModel',
  convertOnSet: false,
  statics: {
    getFake: function(position) {
      // just exists to simplify the move action, never displayed
      return {
        resource: 'FakeBlockTpl',
        nodeType: 'Studio.model.BlockNode',
        label: 'Drop any block here...',
        hasFakeChild: false,
        fake: true,
        iconCls: 'x-fa fa-download',
        leaf: true
      };
    }
  },
  fields: [
    {
      name: 'iconCls',
      type: 'string',
      convert: null,
      defaultValue: 'x-fa fa-cubes'
    },
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
    'children',
    {
      name: 'hasFakeChild',
      type: 'bool',
      persist: false
    }
  ],
  proxy: {
    type: 'rest',
    url: '/api/block_tpls',
    writer: {
      writeRecordId: false
      //writeAllFields: true
    },
    reader: {
      type: 'blocks',
      typeProperty: 'nodeType'
    }
  }
});
