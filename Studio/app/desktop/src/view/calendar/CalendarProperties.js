Ext.define('Studio.view.calendar.CalendarProperties', {
  extend: 'Studio.view.base.Properties',
  xtype: 'calendarproperties',
  userCls: 'calendarproperties',
  alias: 'widget.calendarproperties',
  requires: [
    'Ext.Label',
    'Ext.form.FieldSet',
    'Ext.field.Text',
    'Ext.field.Date',
    'Ext.Button',
    'Studio.ux.field.CronField',
    'Studio.ux.cron.Util'
  ],
  defaultType: 'textfield',
  fieldDefaults: {
  },
  updateRecord: function(record) {
    this.updateDateFields(record);
  },
  updateDateFields: function(record) {
    if (!record) {
      return;
    }
    var dateStart = this.down('datefield[name=dateStart]');
    var dateEnd = this.down('datefield[name=dateEnd]');
    dateStart.setMaxDate(dateEnd.getValue());
    dateEnd.setMinDate(dateStart.getValue());
    dateStart.validate();
    dateEnd.validate();
  },
  items: [
    {
      label: 'Label',
      name: 'label',
      bind: '{record.label}',
      width: 210,
      required: true
    },
    {
      xtype: 'fieldset',
      layout: 'hbox',
      defaultType: 'datefield',
      items: [
        {
          destroyPickerOnHide: true,
          name: 'dateStart',
          label: 'Date start',
          bind: {
            value: '{record.start}'
          },
          width: 150,
          required: true
        },
        {
          destroyPickerOnHide: true,
          name: 'dateEnd',
          label: 'Date end',
          bind: {
            value: '{record.end}'
          },
          width: 150,
          required: true
        }
      ]
    },
    {
      xtype: 'label',
      bind: {
        hidden: '{!record.periodStart && !record.periodStart}',
        html: 'Current or next period starts the <b>{record.periodStart:date}</b> and ends the <b>{record.periodStart:date}</b>'
      }
    },
    {
      xtype: 'fieldset',
      layout: 'hbox',
      defaultType: 'textfield',
      hidden: true,
      items: [
        {
          label: 'Cron start',
          width: 100,
          name: 'cronStart',
          bind: '{record.cronStart}',
          width: 210,
          required: true
        },
        {
          label: 'Cron end',
          width: 100,
          name: 'cronEnd',
          bind: '{record.cronEnd}',
          width: 210,
          required: true
        }
      ]
    },
    {
      xtype: 'fieldcontainer',
      layout: 'hbox',
      userCls: 'cronfieldset',
      // label: 'Each period starts on: ',
      bind: {
        label: 'Each period starts:<br />{conStartExpr}'
      },
      labelTextAlign: 'left',
      fieldDefaults: {
        labelTextAlign: 'center',
      },
      items:[{
        xtype: 'croninputfield',
        label: 'Week days',
        multiple: true,
        options: Studio.ux.cron.Util.ALL_WEEKDAYS,
        listeners: {
          change: function() {
          }
        },
        bind: '{record.cronStartWeekDays}'
      },{
        xtype: 'croninputfield',
        label: 'Days',
        multiple: true,
        options: Studio.ux.cron.Util.ALL_DAYS,
        listeners: {
          change: function() {
          }
        },
        bind: '{record.cronStartDays}'
      },{
        xtype: 'croninputfield',
        label: 'Months',
        multiple: true,
        options: Studio.ux.cron.Util.ALL_MONTHS,
        listeners: {
          change: function() {
          }
        },
        bind: '{record.cronStartMonths}'
      }]
    },
    {
      xtype: 'fieldcontainer',
      layout: 'hbox',
      userCls: 'cronfieldset',
      // label: 'Each period starts on: ',
      bind: {
        label: 'Each period ends:<br />{conEndExpr}'
      },
      labelTextAlign: 'left',
      fieldDefaults: {
        labelTextAlign: 'center',
      },
      items:[{
        xtype: 'croninputfield',
        label: 'Week days',
        multiple: true,
        options: Studio.ux.cron.Util.ALL_WEEKDAYS,
        listeners: {
          change: function() {

          }
        },
        bind: '{record.cronEndWeekDays}'
      },{
        xtype: 'croninputfield',
        label: 'Days',
        multiple: true,
        options: Studio.ux.cron.Util.ALL_DAYS,
        listeners: {
          change: function() {

          }
        },
        bind: '{record.cronEndDays}'
      },{
        xtype: 'croninputfield',
        label: 'Months',
        multiple: true,
        options: Studio.ux.cron.Util.ALL_MONTHS,
        listeners: {
          change: function() {

          }
        },
        bind: '{record.cronEndMonths}'
      }]
    }
  ]
});