Ext.define('Studio.view.choice.ChoiceEditController', {
  extend: 'Studio.view.base.ViewController',
  alias: 'controller.choiceedit',
  control: {
    'field[itemId=choicevalue]': {
      change: 'choiceExpressionChange'
    },
    'filefield': {
      change: 'fileChange'
    },
    'choiceedit': {
      destroy: 'revokeObjectUrlForChoice'
    }
  },
  cancelProperties: function() {
    var record = this.getViewModel().get('record');
    var view = this.getView();
    view.fireEvent('cancel', view, record);
  },
  saveProperties: function() {
    var view = this.getView();
    var record = this.getViewModel().get('record');
    // first save media if new one
    var media = record.get('media');
    if (media && media.indexOf('blob:') == 0) {
      var session = Studio.util.State.get('session');
      var token = session ? session.token : null;
    } else {
    }
    view.fireEvent('save', view, record);
  },
  choiceExpressionChange: function(field, value, oldValue) {
    if (oldValue != null) {
      this.getViewModel().get('record').set('valueFormula', {
        expression: value
      });
    }
  },
  fileChange: function(input, newValue, oldValue) {
    var record = this.getViewModel().get('record');
    var files = input.getFiles();
    if (files && files.length > 0) {
      this.getView().setMasked({
        xtype: 'loadmask',
        message: 'Uploading file...'
      });
      var session = Studio.util.State.get('session');
      var token = session ? session.token : null;
      var formData = new FormData();
      var file = files[0];
      formData.append('file', file, file.name);
      Ext.Ajax.request({
         url: '/api/media_tpls',
         rawData: formData,
         success: function(response){
           try {
             // revoke potential ObjectUrl
             this.revokeObjectUrlForChoice();
             this.getView().setMasked({
               xtype: 'loadmask',
               message: 'Uploading file...'
             });
             var media = Ext.JSON.decode(response.responseText);
             if (media) {
               if (media.id) {
                 record.set('media', media.id); 
               }
               if (media.mediaUrl) {
                 Ext.Ajax.request({
                   binary: true,
                   url: media.mediaUrl,
                   success: function(response) {
                     var blob = new Blob([response.responseBytes], {type: (media.mimeType || 'image/png')});
                     this.getViewModel().set('media', URL.createObjectURL(blob));
                   },
                   failure: function(response) {
                     console.error(response);
                     Ext.Msg.alert('Error', 'An error occured while getting media data.');
                   },
                   callback: function() {
                     this.getView().setMasked(false);
                   },
                   scope: this
                 });
               }
             }
           } catch (e) {
             console.error(e);
             this.getView().setMasked(false);
             Ext.Msg.alert('Error', 'An error occured while loading response media. See logs for more details.');
           }
         },
         failure: function(response) {
           console.error(response);
           this.getView().setMasked(false);
           Ext.Msg.alert('Error', 'An error occured while saving media. See logs for more details.');
         },
         scope: this
      });
    } else {
      this.revokeObjectUrlForChoice();
      /**
       * Chrome remove previously selected file when opening for new file but clicking cancel
       * https://bugs.chromium.org/p/chromium/issues/detail?id=2508
       */
      record.set('media', null);
    }
  },
  revokeObjectUrlForChoice: function() {
    var vm = this.getViewModel();
    var media = vm.get('media');
    if (media && media.indexOf('blob:') == 0) {
      URL.revokeObjectURL(media);
    }
    vm.set('media', null);
  }
});