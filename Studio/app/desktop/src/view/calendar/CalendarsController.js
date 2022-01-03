Ext.define('Studio.view.calendar.CalendarsController', {
  extend: 'Studio.view.base.CommonViewController',
  alias: 'controller.calendars',
  control: {
    'calendars': {
      activate: 'onActivate',
      deactivate: 'onDeactivate'
    }
  },
  onFieldValueChange: function(field) {
    this.callParent(arguments);
    if (field.isXType('datefield')) {
      field.up('calendarproperties').updateDateFields();
    }
  }
});
