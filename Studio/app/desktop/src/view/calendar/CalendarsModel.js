Ext.define('Studio.view.calendar.CalendarsModel', {
  extend: 'Studio.view.base.ViewModel',
  alias: 'viewmodel.calendars',
  stores: {
    list: {
      source: 'Calendars'
    }
  },
  formulas: {
    conStartExpr: {
      bind: {
        cronStartDays: '{record.cronStartDays}',
        cronStartMonths: '{record.cronStartMonths}',
        cronStartWeekDays: '{record.cronStartWeekDays}'
      },
      get: function(data) {
        return data.cronStartDays && data.cronStartMonths && data.cronStartWeekDays ? 
          cronstrue.toString('0 0 ' + data.cronStartDays + ' ' + data.cronStartMonths + ' ' + data.cronStartWeekDays, {verbose: true})
          // cronstrue.toString('0 0 1-3,4-5 1-3,4-5 1-3,4-5', {verbose: true})
          : '';
      }
    },
    conEndExpr: {
      bind: {
        cronEndDays: '{record.cronEndDays}',
        cronEndMonths: '{record.cronEndMonths}',
        cronEndWeekDays: '{record.cronEndWeekDays}'
      },
      get: function(data) {
        return data.cronEndDays && data.cronEndMonths && data.cronEndWeekDays ? 
          cronstrue.toString('0 0 ' + data.cronEndDays + ' ' + data.cronEndMonths + ' ' + data.cronEndWeekDays, {verbose: true})
          // cronstrue.toString('0 0 1-3,4-5 1-3,4-5 1-3,4-5', {verbose: true})
          : '';
      }
    }
  }
});
