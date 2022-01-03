Ext.define('Studio.writer.Calendar', {
  extend: 'Ext.data.writer.Json',
  alias: 'writer.calendar',
  config: {
    writeRecordId: false
  },
  writeRecords: function(request, data) {
    for (var i=0; i < data.length; i++) {
      var record = request.getRecords()[i];
      var calendar = data[i];
      var cronStart = record.get('cronStart').split(' ');
      var cronEnd = record.get('cronEnd').split(' ');
      var cronStartChanged = calendar.cronStartDays || calendar.cronStartMonths || calendar.cronStartWeekDays;
      var cronEndChanged = calendar.cronEndDays || calendar.cronEndMonths || calendar.cronEndWeekDays;
      if (cronStart.length == 5) {
        if (calendar.cronStartDays) {
          cronStart[2] = calendar.cronStartDays.join(',');
          delete calendar.cronStartDays;
        }
        if (calendar.cronStartMonths) {
          cronStart[3] = calendar.cronStartMonths.join(',');
          delete calendar.cronStartMonths;
        }
        if (calendar.cronStartWeekDays) {
          cronStart[4] = calendar.cronStartWeekDays.join(',');
          delete calendar.cronStartWeekDays;
        }
        if (cronStartChanged) {
          calendar.cronStart = cronStart.join(' ');
        }
      }
      if (cronEnd.length == 5) {
        if (calendar.cronEndDays) {
          cronEnd[2] = calendar.cronEndDays.join(',');
          delete calendar.cronEndDays;
        }
        if (calendar.cronEndMonths) {
          cronEnd[3] = calendar.cronEndMonths.join(',');
          delete calendar.cronEndMonths;
        }
        if (calendar.cronEndWeekDays) {
          cronEnd[4] = calendar.cronEndWeekDays.join(',');
          delete calendar.cronEndWeekDays;
        }
        if (cronEndChanged) {
          calendar.cronEnd = cronEnd.join(' ');
        }
      }
    }
    return this.callParent(arguments);
  }
});