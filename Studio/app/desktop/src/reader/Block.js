Ext.define('Studio.reader.Block', {
    extend: 'Ext.data.reader.Json',
    alias: 'reader.blocks',
    getResponseData: function() {
        var data = this.callParent(arguments);
        // for each block
        for (var i=0; i < data.length; i++) {
          this.loadNested(data[i]);
        }
        return data;
    },
    loadNested: function(block) {
      if (!block) { return block; }
      if (block.hasOwnProperty('questionTpls')) {
        block.nodeType = 'Studio.model.BlockNode';
      } else {
        block.nodeType = 'Studio.model.Question';
      }
      if (block.questionTpls && block.questionTpls.length > 0) {
        var childCount = block.questionTpls.length;
        for (var j=0; j < childCount; j++) {
          var child = block.questionTpls[j];
          child = this.loadNested(child);
        }
        block.children = block.questionTpls;
      } else {
        block.leaf = true;
      }
      delete block.questionTpls;
      return block;
    },
    loadQuestion: function(block) {
      if (!block) { return block; }
      if (block.children && (childCount = block.children.length) > 0) {
        for (var j=0; j < childCount; j++) {
          var child = block.children[j];
          child = this.loadQuestion(child);
        }
      } else {
        block.leaf = true;
        delete block.children;
      }
      return block;
    }
});