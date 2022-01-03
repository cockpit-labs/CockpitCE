/**
 * @class Studio.ux.cron.Util
 * @version 1.0
 *   
 **/
 Ext.define('Studio.ux.cron.Util', {
    singleton: true,
    requires: [
    ],
    everyDayText: 'Any day', // *
    lastDayOfMonthText: 'Last day of month', // L
    everyMonthText: 'Any month', // *
    everyWeekDayText: 'Any week day', // *
    DAYS: 'Days',
    MONTHS: 'Months',
    WEEKDAYS: 'Weekday',
    // cleanExpression: function(expr) {
    //   var list = expr.split(' ');
    //   for (var i = 2; i < list.length; i++) {
    //     var item = list[i];
    //     var newItem = '';
    //     // we only parse strings with coma and numbers
    //     // TODO use regex with , and digits
    //     if (item.indexOf(',') != -1 && item.indexOf('L') == -1 && item.indexOf('-') == -1 && item.indexOf('*') == -1 && item.indexOf('/') == -1) {
    //       var comas = item.split(',');
    //       if (comas.length > 1) {
    //         try {
    //           var firstValue = parseInt(comas[0]);
    //           var previousValue = firstValue;
    //           var lastValue = null;
    //           for (var j = 1; j < comas.length; j++) {
    //             var coma = comas[j];
    //             if (coma == previousValue + 1) {
    //               previousValue = coma;
    //             } else {

    //             }
    //           }

    //           newItem = previousValue + '-';
    //         } catch (e) {}
    //       }
    //     }
    //     list.push(newItem ? newItem : item);
    //   }
    //   return list.join(' ');
    // }
}, function() {
  var currentDate = new Date();
  var day = new Date(currentDate.setDate(currentDate.getDate() - currentDate.getDay() - 1));
  var length = 33;
  Ext.apply(this, {
    ALL_DAYS: Array.apply(null, Array(length)).map(function (x, i) { return i == 0 ? {text: this.everyDayText, value: '*'} : (i < length - 1 ? {text: i, value: '' + i} : {text: this.lastDayOfMonthText, value: 'L'}); }, this),
    ALL_MONTHS: [...Array(13).keys()].map(
      function(x, i) {
        return i == 0 ? {text: this.everyMonthText, value: '*'} : {text: new Date(0, i - 1).toLocaleString('en', { month: 'long' }), value: '' + i};
      },
      this
    ),
    ALL_WEEKDAYS: [...Array(8).keys()].map(
      function(x, i) {
        if (i == 0) {
          return {text: this.everyWeekDayText, value: '*'};
        } else {
          day.setDate(day.getDate() + 1);
          return {text: day.toLocaleString('en', { weekday: 'long'}), value: '' + (i - 1)};
        }
      },
      this
    )
  });
});
