// /**
//  * 
//  * Here we listened to the {@link #change} event on the slider and updated the background
//  * image of an {@link Ext.Img image component} based on what size the user selected. Of
//  * course, you can use any logic inside your event listener.
//  */
//  Ext.define('Studio.ux.field.Cron', {
//   extend: 'Ext.field.Field',
//   xtype: 'cronfield',
//   requires: ['Ext.slider.Slider'],
//   mixins: [
//       'Ext.mixin.ConfigProxy',
//       'Ext.field.BoxLabelable'
//   ],

//   /**
//    * @event change
//    * Fires when the value changes.
//    * @param {Ext.field.Slider} me 
//    * @param {Number[]} newValue The new value.
//    * @param {Number[]} oldValue The old value.
//    */

//   config: {
//       /**
//        * @private
//        */
//       cronlist: {
//           xtype: 'cron',
//           inheritUi: true
//       },

//       /**
//        * @cfg {Boolean} liveUpdate
//        * `true` to fire change events while the slider is dragging. `false` will
//        * only fire a change once the drag is complete.
//        */
//       liveUpdate: false,

//       /**
//        * @cfg tabIndex
//        * @inheritdoc
//        */
//       tabIndex: -1,

//       /**
//        * @cfg readOnly
//        * Will make this field read only, meaning it cannot be changed with used interaction.
//        * @accessor
//        */
//       readOnly: false,

//       /**
//        * @cfg value
//        * @inheritdoc Ext.slider.Slider#cfg-value
//        * @accessor
//        */
//       value: 0
//   },

//   /**
//    * @property classCls
//    * @inheritdoc
//    */
//   classCls: Ext.baseCSSPrefix + 'sliderfield',

//   proxyConfig: {
//       cronlist: [
//       ]
//   },

//   /**
//    * @cfg bodyAlign
//    * @inheritdoc
//    */
//   bodyAlign: 'stretch',

//   /**
//    * @property defaultBindProperty
//    * @inheritdoc
//    */
//   defaultBindProperty: 'value',

//   /**
//    * @cfg twoWayBindable
//    * @inheritdoc
//    */
//   twoWayBindable: {
//       values: 1,
//       value: 1
//   },

//   /**
//    * @cfg values
//    * @inheritdoc Ext.slider.Slider#cfg-values
//    */

//   constructor: function(config) {
//       config = config || {};

//       if (config.hasOwnProperty('values')) {
//           config.value = config.values;
//       }

//       this.callParent([config]);
//       this.updateMultipleState();
//   },

//   /**
//    * @private
//    */
//   initialize: function() {
//       this.callParent();

//       this.getSlider().on({
//           scope: this,

//           change: 'onSliderChange',
//           dragstart: 'onSliderDragStart',
//           drag: 'onSliderDrag',
//           dragend: 'onSliderDragEnd'
//       });
//   },

//   getBodyTemplate: function() {
//       return this.mixins.boxLabelable.getBodyTemplate.call(this);
//   },

//   applySlider: function(slider) {
//       if (slider && !slider.isInstance) {
//           slider = this.mergeProxiedConfigs('slider', slider);
//           slider.$initParent = this;
//           slider = Ext.create(slider);
//           delete slider.$initParent;
//       }

//       this.boxElement.appendChild(slider.el);

//       slider.ownerCmp = this;

//       return slider;
//   },

//   updateSlider: function(slider) {
//       slider.doInheritUi();
//   },

//   getValue: function() {
//       return this._value;
//   },

//   applyValue: function(value, oldValue) {
//       value = this.callParent([value, oldValue]) || 0;

//       // If we are currently dragging, don't allow the binding
//       // to push a value over the top of what the user is doing.
//       if (this.dragging && this.isSyncing('value')) {
//           value = undefined;
//       }
//       else if (Ext.isArray(value)) {
//           value = value.slice(0);

//           if (oldValue && Ext.Array.equals(value, oldValue)) {
//               value = undefined;
//           }
//       }
//       else {
//           value = [value];
//       }

//       return value;
//   },

//   updateValue: function(value, oldValue) {
//       if (!this.dragging) {
//           value = this.setSliderValue(value);
//       }

//       this.callParent([value, oldValue]);
//   },

//   setSliderValue: function(value) {
//       // Get the value back out after setting
//       return this.getSlider().setValue(value).getValue();
//   },

//   onSliderChange: function(slider, thumb, newValue, oldValue) {
//       this.setValue(slider.getValue());
//       this.fireEvent('dragchange', this, slider, thumb, newValue, oldValue);
//   },

//   onSliderDragStart: function(slider, thumb, startValue, e) {
//       this.dragging = true;
//       this.fireEvent('dragstart', this, slider, thumb, startValue, e);
//   },

//   onSliderDrag: function(slider, thumb, value, e) {
//       var me = this;

//       if (me.getLiveUpdate()) {
//           me.setValue(slider.getValue());
//       }

//       me.fireEvent('drag', me, slider, thumb, value, e);
//   },

//   onSliderDragEnd: function(slider, thumb, startValue, e) {
//       this.dragging = false;
//       this.fireEvent('dragend', this, slider, thumb, startValue, e);
//   },

//   /**
//    * Convenience method. Calls {@link #setValue}.
//    * @param {Object} value 
//    */
//   setValues: function(value) {
//       this.setValue(value);
//       this.updateMultipleState();
//   },

//   /**
//    * Convenience method. Calls {@link #getValue}
//    * @return {Object} 
//    */
//   getValues: function() {
//       return this.getValue();
//   },

//   reset: function() {
//       var config = this.config,
//           initialValue = (this.config.hasOwnProperty('values')) ? config.values : config.value;

//       this.setValue(initialValue);
//   },

//   updateReadOnly: function(newValue) {
//       this.getSlider().setReadOnly(newValue);
//   },

//   updateMultipleState: function() {
//       var value = this.getValue();

//       if (value && value.length > 1) {
//           this.addCls(Ext.baseCSSPrefix + 'slider-multiple');
//       }
//   },

//   updateDisabled: function(disabled, oldDisabled) {
//       this.callParent([disabled, oldDisabled]);

//       this.getSlider().setDisabled(disabled);
//   },

//   doDestroy: function() {
//       this.getSlider().destroy();
//       this.callParent();
//   },

//   getRefItems: function(deep) {
//       var refItems = [],
//           slider = this.getSlider();

//       if (slider) {
//           refItems.push(slider);

//           if (deep && slider.getRefItems) {
//               refItems.push.apply(refItems, slider.getRefItems(deep));
//           }
//       }

//       return refItems;
//   },

//   rawToValue: Ext.emptyFn
// });

/**
 * Cron field container used for Calendars
 */
Ext.define('Studio.ux.field.Cron', {
  extend: 'Ext.field.Container',
  requires: [
  ],
  xtype: 'cronfield',
  cls: 'cron-field',
  defaults: {
    labelTextAlign: 'center'
  },
  everyDayText: 'Every day', // *
  lastDayOfMonthText: 'Last day of month', // L
  everyMonthText: 'Every month', // *
  everyWeekDayText: 'Every week day', // *

  defaultType: 'list',
  twoWayBindable: {
    values: 1,
    value: 1
},
  delegate: '[isList]',
  // buildDays: function() {
  //   var length = 33;
  //   var me = this;
  //   return Array.apply(null, Array(length)).map(function (x, i) { return i == 0 ? {text: me.everyDayText, value: '*'} : (i < length - 1 ? {text: i, value: i} : {text: me.lastDayOfMonthText, value: 'L'}); });
  // },
  // buildMonths:  function() {
  //   var me = this;
  //   return [...Array(13).keys()].map(
  //     function(x, i) {
  //       return i == 0 ? {text: me.everyMonthText, value: '*'} : {text: new Date(0, i - 1).toLocaleString('en', { month: 'long' }), value: i};
  //     }
  //   );
  // },
  // buildWeekDays: function() {
  //   var me = this;
  //   var currentDate = new Date();
  //   var day = new Date(currentDate.setDate(currentDate.getDate() - currentDate.getDay() - 1));
  //   return [...Array(7).keys()].map(
  //     function(x, i) {
  //       day.setDate(day.getDate() + 1);
  //       return i == 50 ? {text: 'all', value: '*'} : {text: day.toLocaleString('en',{weekday:'long'}), value: i};
  //     }
  //   );
  // },

/**
 * Returns an object containing the values of all checked checkboxes within the group.
 * Each key-value pair in the object corresponds to a checkbox
 * {@link Ext.field.Checkbox#name name}. If there is only one checked checkbox
 * with a particular name, the value of that pair will be the String
 * {@link Ext.field.Checkbox#value value} of that checkbox. If there are
 * multiple checked checkboxes with that name, the value of that pair will be an Array
 * of the selected inputValues.
 *
 * The object format returned from this method can also be passed directly to the
 * {@link #setValue} method.
 */
getValue: function() {
  // var items = this.getGroupItems(),
  //     ln = items.length,
  //     values = {},
  //     item, name, value, bucket, b;

  // for (b = 0; b < ln; b++) {
  //     item = items[b];
  //     name = item.getName();
  //     value = item.getValue();

  //     if (value && item.getChecked()) {
  //         if (values.hasOwnProperty(name)) {
  //             bucket = values[name];

  //             if (!Ext.isArray(bucket)) {
  //                 bucket = values[name] = [bucket];
  //             }

  //             bucket.push(value);
  //         }
  //         else {
  //             values[name] = value;
  //         }
  //     }
  // }

  // return values;
  // return this.down('list[name="cron0"]').getSelectable().getSelectedRecords().extractValues('value');
},
  setValue: function(value) {
    if (value == null) {

    } else 
    if (value[0] == 'HAHA') {
      this.callParent(arguments);
    } else 
    if (Ext.isArray(value)) {
      var list = this.down('list[name="cron0"]');
      var store = list.getStore();
      var selections = [];
      for (var i = 0; i < value.length; i++) {
        var rec = store.findRecord('value', value[0]);
        if (rec) {
          selections.push(rec);
        }
      }
      list.getSelectable().select(selections, true, true);
    }
    console.log('setValue', value);
    // try {
    //   var values = value.split(' ');
    //   if (values.length == 5) {
    //     // min
    //     var minutes = values[0];
    //     // h
    //     var hours = values[1];
    //     // days
    //     var days = values[2];
    //     var daysList = '';
    //     if (days === '') {
    //       days = '*';
    //     }
    //     if (days.indexOf('-') != -1) {

    //     }
    //     if (days.indexOf('-') != -1) {

    //     }
    //     // months
    //     var months = values[3];
    //     // weekdays
    //     var weekdays = values[4];
    //   }
    // } catch (e) {

    // }
  },
  initConfig: function (instanceConfig) {
    var conf = instanceConfig || {};
    var me = this;
    // console.log();
    // console.log();
    // console.log(this.buildWeekDays());

    var items = [];
    for (var i = 0; i < 1; i++) {
      items.push(
        {
          xtype: 'list',
          name: 'cron' + i,
          items: {
            docked: 'top',
            xtype: 'label',
            html: i == 0 ? Studio.ux.cron.Util.DAYS : (i == 1 ? Studio.ux.cron.Util.MONTHS : Studio.ux.cron.Util.WEEKDAYS),
          },
          striped: true,
          cls: 'cronpart-list',
          selectable: {
            mode: 'multi'
          },
          // bind: {
          //   selection: 
          // },
          store: i == 0 ? Studio.ux.cron.Util.ALL_DAYS : (i == 1 ? Studio.ux.cron.Util.ALL_MONTHS : Studio.ux.cron.Util.ALL_WEEKDAYS),
          height: 190,
          width: 140,
          bind: {
            selection: '{toto' + Ext.id() + '}'
          },
          listeners: {
            select: function(list, records) {
              // if (records.length > 0) {
              //   this.down('list').down('radiofield').setChecked(true);
              // }
              me.setValue(['HAHA']);
              // me.value = 'HAH';
            },
            deselect: function(list, records) {
              // if (list.getSelectable().getSelectionCount() == 0) {
              //   this.down('radiofield').setChecked(true);
              // }
            },
            scope: me
          }
        });
    }
    Ext.apply(instanceConfig, {
      items: items
      //[
        // {
        //   xtype: 'cronpartfield',
        //   partType: 'day',
        //   name: conf.name + 'day'
        // },
        // {
        //   xtype: 'cronpartfield',
        //   partType: 'month',
        //   name: conf.name + 'month'
        // },
        // {
        //   xtype: 'cronpartfield',
        //   partType: 'week',
        //   name: conf.name + 'week'
        // }
      //]
    });
    this.callParent(arguments);
  },
//   setValue: function(value) {
//     var me = this,
//         items, ln, item, name, b, cbValue, cbName;

//     // Ignore if value is equals to last updated value
//     if (me.isEqual(value, me.lastValue)) {
//         return me;
//     }

//     items = me.getGroupItems();
//     ln = items.length;
//     me.suspendCheckChange = 1;

//     for (b = 0; b < ln; b++) {
//         item = items[b];
//         name = item.getName();
//         cbValue = false;

//         if (value) {
//             cbName = value[name];

//             if (Ext.isArray(cbName)) {
//                 cbValue = Ext.Array.contains(cbName, item.getValue());
//             }
//             else {
//                 cbValue = cbName;
//             }
//         }

//         item.setChecked(cbValue);
//     }

//     me.suspendCheckChange = 0;
//     me.onGroupChange();

//     return me;
// },

  // getTemplate: function() {
  //   var tpl = this.callParent();
  //   var labelEl = tpl[0];
  //   labelEl.children.push({
  //     reference: 'labelTextHelper',
  //     cls: Ext.baseCSSPrefix + 'label-text-el',
  //     tag: 'span'
  //   });
  //   return tpl;
  // }
});
