Ext.define('Studio.reader.Document', {
  extend: 'Ext.data.reader.Json',
  alias: 'reader.document',
  typeProperty: 'nodeType',
  config: {
    transform: function(data) {
      //var data = this.callParent(arguments);
        if (Ext.isObject(data)) {
          this.readRecord(data);
        } else {
          // for each document
          for (var i=0; i < data.length; i++) {
            this.readRecord(data[i]);
          }
        }
        return data;
    }
  },
  readRecord: function(data) {
    if (data.blockTpls) {
      // for each document
      for (var i=0; i < data.blockTpls.length; i++) {
        this.readBlock(data.blockTpls[i]);
      }
      data.children = data.blockTpls;
      data.children.push(Studio.model.BlockNode.getFake());
      data.expanded = true;
      delete data.blockTpls;
    } else {
      data.expanded = true;
      data.children = [];
      data.leaf = false;
      block.children = [Studio.model.BlockNode.getFake()];
    }
  },
  readBlock: function(block) {
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
        child = this.readQuestion(child);
      }
      block.children = block.questionTpls;
      block.children.push(Studio.model.Question.getFake(childCount + 1));
    } else {
//      block.leaf = true;
      // set block has fake child
      block.hasFakeChild = true;
      block.children = [Studio.model.Question.getFake(1)];
    }
    delete block.questionTpls;
    return block;
  },
  readQuestion: function(question) {
    if (!question) { return question; }
    if (question.children && question.children.length > 0) {
      var childCount = question.children.length;
      for (var j=0; j < childCount; j++) {
        var child = question.children[j];
        question.children[j] = this.readQuestion(child);
      }
      question.children.push(Studio.model.Question.getFake(childCount + 1));
    } else {
//      question.leaf = true;
//      delete question.children;
      // set question has fake child
      question.hasFakeChild = true;
      question.children = [Studio.model.Question.getFake(1)];
    }
    question.nodeType = 'Studio.model.Question';
    var choices = question.choiceTpls;
    if (choices) {
      Ext.Array.sort(choices, function(a, b) {
        return a.position - b.position;
      });
    }
    return question;
  }
});