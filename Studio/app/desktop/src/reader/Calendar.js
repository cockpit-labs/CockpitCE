Ext.define('Studio.reader.Calendar', {
  extend: 'Ext.data.reader.Json',
  alias: 'reader.calendar',
  config: {
    transform: function(data) {
      for (var i=0; i < data.length; i++) {
        Ext.apply(data[i], this.getCrons(data[i], 'cronStart'), this.getCrons(data[i], 'cronEnd'));
      }
      return data;
    }
  },
  getCrons: function(data, value) {
    var crons = {};
    try {
      var values = data[value].split(' ');
      if (values.length == 5) {
        // min
        var minutes = values[0];
        // h
        var hours = values[1];
        // days
        var days = values[2];
        var daysList = '';
        if (days === '') {
          days = '*';
        }
        crons[value + 'Days'] = this.getValues(days);
        // console.log(days);
        if (days.indexOf(',') != -1) {

        }
        // months
        var months = values[3];
        // months = this.getValues(months);
        crons[value + 'Months'] = this.getValues(months);
        // console.log(months);
        // weekdays
        var weekdays = values[4];
        // weekdays = this.getValues(value + 'Weekdays', weekdays);
        crons[value + 'WeekDays'] = this.getValues(weekdays);
        // console.log(weekdays);

      }
    } catch (e) {
      crons[value + 'Days'] = '*';
      crons[value + 'Months'] = '*';
      crons[value + 'WeekDays'] = '*';
    }
    return crons;
  },
  isCronValid: function(value) {
    if (value === '*' || value === 'L') {
      return true;
    }
    var hasHypens = value.indexOf('-') != -1;
    var hasComa = value.indexOf(',') != -1;
    var hasSlash = value.indexOf('/') != -1;
    if ((hasHypens && hasComa) || (hasHypens && hasSlash) || (hasComa && hasSlash)){
      return false;
    }
    return true;
  },
  getValues: function(value) {
    var values = [];
    var hasHypens = value.indexOf('-') != -1;
    var hasComa = value.indexOf(',') != -1;
    var hasSlash = value.indexOf('/') != -1;
    if (hasSlash){
      console.error('Unhandled cron expression : ', value);
      values.push('*');
      return values;
    }
    if (!hasHypens && !hasComa) {
      hasComa = true;
    }
    if (hasComa) {
      var splittedValues = value.split(',');
      for (let part of splittedValues) {
        if (!part) {
          continue;
        }
        // if (part != 'L' && part != '*') {
        //   try {
        //     part = parseInt(part);
        //   } catch (e) {
        //   }
        // }
        this.fillHypenedValues(part, values);
        // var hyphenValues = part.split('-');
        // for (let hyphen of hyphenValues) {
        //   if (!hyphen) {
        //     continue;
        //   }
        //   values.push(part);
        // }

        // values.push(part);
      }
    } else if (hasHypens) {
      this.fillHypenedValues(value, values);
      // var splittedValues = value.split('-');
      // if (splittedValues.length != 2) {
      //   console.error('Unhandled end of cron expression : ', value);
      //   if (splittedValues.length > 0) {
      //     values.push(splittedValues[0]);
      //   }
      // } else {
      //   try {
      //     var from = parseInt(splittedValues[0]);
      //     var to = parseInt(splittedValues[1]);
      //     for (var i=from; i <= to; i++) {
      //       values.push('' + i);
      //     }
      //   } catch (e) {}
      // }
    } else if (value == '*' || value == 'L') {
      values.push(value);
    } else {
      console.error('Unhandled end of cron expression : ', value);
    }
    return values;
  },
  fillHypenedValues: function(value, result) {
    if (!result) {
      result = [];
    }
    var splittedValues = value.split('-');
    if (splittedValues.length > 2) {
      console.error('Unhandled end of cron expression : ', value);
    }
    if (splittedValues.length == 1) {
      result.push(splittedValues[0]);
    } else {
      try {
        var from = parseInt(splittedValues[0]);
        var to = parseInt(splittedValues[1]);
        for (var i=from; i <= to; i++) {
          result.push('' + i);
        }
      } catch (e) {}
    }
  }
});