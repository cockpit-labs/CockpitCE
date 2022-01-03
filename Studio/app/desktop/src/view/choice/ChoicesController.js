Ext.define('Studio.view.choice.ChoicesController', {
  extend: 'Ext.app.ViewController',
  requires: [
  ],
  alias: 'controller.choices',
  control: {
    'choices button[action=help]': {
      tap: 'choicesHelp'
    },
    'choices button[action=create]': {
      tap: {
        fn: 'createChoice',
        // workaround for ChoiceEdit modal being sometimes under DocumentDialog modal
        buffer: 100
      }
    },
    'choices tool[action=edit]': {
      click: {
        fn: 'edit',
        // workaround for ChoiceEdit modal being sometimes under DocumentDialog modal
        buffer: 100
      }
    },
    'choices tool[action=up]': {
      click: 'up'
    },
    'choices tool[action=down]': {
      click: 'down'
    },
    'choices tool[action=delete]': {
      click: 'remove'
    },
    'choices': {
      select: {
        fn: 'select',
        // workaround for ChoiceEdit modal being sometimes under DocumentDialog modal
        buffer: 100
      }
    }
  },
  help: 'All questions can have any numbers of choices. They are important if you want to score user\'s answers.'
    + '<br /><br /> Questions of type None and Media won\'t use any choice you would have set.'
    + '<br /> Questions of type Text/Number/Date and Range will use the first choice for score calculation.'
    + '<br /> Questions of type Yes/No may have additional choice like \'Perhaps\'.'
    + '<br /><br /> The expression set for a choice should return a number.'
    + '<br /> You can use the keyword \'value\' if you need to return a score that depends on user\'s input.',
  createChoice: function() {
    var store = this.getView().getStore();
    var choice = Ext.create('Studio.model.Choice', {
      position: store.getCount() + 1
    });
    // force dirty for further question save
    choice.set('label', 'New choice');
    this.select(this.getView(), choice, null, true);
  },
  move: function(tool, event, cell, delta) {
    var view = this.getView();
    var record = cell.getRecord();
    var store = record.store;
    // when position won't be used any more, should do this :
    var index = store.indexOf(record);
    store.suspendEvents();
    store.removeAt(index);
    store.resumeEvents();
    store.insert(index + delta, record);
  },
  up: function(tool, event, cell) {
    this.move(tool, event, cell, -1);
  },
  down: function(tool, event, cell) {
    this.move(tool, event, cell, 1);
  },
  remove: function(tool, event, cell) {
    var view = this.getView();
    var record = cell.getRecord();
    var store = record.store;
    store.remove(record);
  },
  edit: function(tool, event, cell) {
    this.select(this.getView(), cell.getRecord());
  },
  select: function(grid, record, options, creation) {
    var modal = Ext.create({
      xtype: 'choiceedit',
      height: 440,
      width: 500,
      listeners: {
        save: function(dialog, choice) {
          var newValues = {};
          var label = choice.get('label');
          if (label != record.get('label')) {
            newValues['label'] = label;
          }
          var expr = choice.get('expression');
          if (expr != record.get('expression')) {
            newValues['valueFormula'] = choice.get('valueFormula');
          }
          var media = choice.get('media');
          if (media != record.get('media')) {
            newValues['media'] = media;
          }
          if (!Ext.Object.isEmpty(newValues)) {
            record.set(newValues);
          }
          if (creation) {
            grid.getStore().add(choice);
          } else {
            choice.destroy();
          }
          modal.close();
        },
        cancel: function(dialog, choice) {
          if (creation) {
            choice.destroy();
          }
          modal.close();
        },
        scope:this
      }
    });
    var copy = record;//.copy(null);
    if (!creation) {
      copy = copy.copy(null);
    }
    var media = copy.get('media');
    if (media) {
      var documentDialog = grid.up('documentdialog');
      documentDialog.setMasked({
        xtype: 'loadmask',
        message: 'Getting media information...'
      });
      Ext.Ajax.request({
        url: media,
        success: function(response) {
          documentDialog.setMasked({
            xtype: 'loadmask',
            message: 'Downloading media...'
          });
          try {
            var media = Ext.JSON.decode(response.responseText);
            if (media) {
              if (media.mediaUrl) {
                Ext.Ajax.request({
                  binary: true,
                  url: media.mediaUrl,
                  success: function(response) {
                    var blob = new Blob([response.responseBytes], {type: (media.mimeType || 'image/png')});
                    modal.getViewModel().set({
                      record: copy,
                      media: URL.createObjectURL(blob)
                    });
                    modal.show().focus(true);
                  },
                  failure: function(response) {
                    console.error(response);
                    Ext.Msg.alert('Error', 'An error occured while getting media data.');
                  },
                  callback: function() {
                    documentDialog.setMasked(false);
                  },
                  scope: this
                });
              }
            }
          } catch (e) {
            console.error(e);
            Ext.Msg.alert('Error', 'An error occured while loading response media. See logs for more details.');
          }
        },
        callback: function() {
          documentDialog.setMasked(false);
        },
        scope: this
      });
    } else {
      modal.getViewModel().set({
        record: copy
      });
      modal.show().focus(true);
    }
  },
  /* */
  choicesHelp: function() {
    Ext.Msg.alert('Choices help', this.help);
  }
});
