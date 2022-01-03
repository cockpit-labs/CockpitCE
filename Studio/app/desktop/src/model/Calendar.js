Ext.define('Studio.model.Calendar', {
  extend: 'Ext.data.Model',
  requires: [
    'Studio.reader.Calendar',
    'Studio.writer.Calendar',
    'Ext.data.validator.Presence'
  ],
  fields: [
    {
      name: 'label',
      type: 'string',
      convert: null,
      defaultValue: '',
      validators:[{
        type: 'presence',
        message: 'Label is mandatory'
      }]
    },
    {
      name: 'start',
      type: 'date',
      dateFormat: 'c',
      dateReadFormat: 'c',
      dateWriteFormat: 'c',
      validators:[{
        type: 'presence',
        message: 'Date start is mandatory'
      }]
    },
    {
      name: 'end',
      type: 'date',
      dateFormat: 'c',
      dateReadFormat: 'c',
      dateWriteFormat: 'c',
      validators:[{
        type: 'presence',
        message: 'Date end is mandatory'
      }]
    },
    {
      name: 'cronStart',
      type: 'string',
      convert: null,
      defaultValue: '* * * * *',
      validators:[{
        type: 'presence',
        message: 'Cron start is mandatory'
      }]
    },
    {
      name: 'cronEnd',
      type: 'string',
      convert: null,
      defaultValue: '* * * * *',
      validators:[{
        type: 'presence',
        message: 'Cron end is mandatory'
      }]
    },
    {
      name: 'cronStartDays',
      type: 'array',
      convert: null,
      defaultValue: ['*'],
      // persist: false
    },
    {
      name: 'cronEndDays',
      type: 'array',
      convert: null,
      defaultValue: ['*'],
      // persist: false
    },
    {
      // calculated field
      name: 'iri',
      convert: function (value, record) {
        return '/api/calendars/' + record.get('id');
      },
      depends: 'id',
      persist: false
    },
    {
      name: 'periodStart',
      type: 'date',
      dateFormat: 'c',
      dateReadFormat: 'c',
      persist: false
    },
    {
      name: 'periodEnd',
      type: 'date',
      dateFormat: 'c',
      dateReadFormat: 'c',
      persist: false
    }
  ],
  proxy: {
    type: 'rest',
    url: '/api/calendars',
    writer: {
      type: 'calendar'
    },
    reader: {
      type: 'calendar'
    }
  }
});
