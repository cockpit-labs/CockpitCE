Ext.define('Studio.reader.Folder', {
  extend: 'Ext.data.reader.Json',
  alias: 'reader.folder',
  config: {
    transform: function(data) {
      for (var i=0; i < data.length; i++) {
        if (Ext.isEmpty(data[i].calendars)) {
          // set calendars null instead of empty array else it is not considered as empty for a required combobox field
          data[i].calendars = null;
        }
      }
      return data;
    }
  }
});