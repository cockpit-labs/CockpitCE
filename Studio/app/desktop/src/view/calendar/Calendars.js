Ext.define('Studio.view.calendar.Calendars', {
  extend: 'Studio.view.base.View',
  xtype: 'calendars',
  userCls: 'c-calendars',
  alias: 'widget.calendars',
  requires: [
    'Studio.view.calendar.CalendarsController',
    'Studio.view.calendar.CalendarsModel'
  ],
  controller: 'calendars',
  viewModel: {
    type: 'calendars'
  },
  config: {
    listTitle: 'Calendars',
    listIconCls: 'x-fa fa-calendar-alt',
    emptyMessage: 'Select a calendar or create a new one'
  },
  items: {
    title: 'Details',
    flex: 1,
    reference: 'properties',
    iconCls: 'x-fa fa-file-alt',
    xtype: 'calendarproperties',
    bind: {
      hidden: '{!record}',
      record: '{record}'
    }
  }
});