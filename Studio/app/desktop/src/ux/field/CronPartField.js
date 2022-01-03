// /**
//  * Cron field container used for Calendars
//  */
// Ext.define('Studio.ux.field.CronPartField', {
//   extend: 'Ext.field.Container',
//   requires: [
//     'Ext.field.Select',
//     'Ext.field.RadioGroup'
//   ],
//   xtype: 'cronpartfield',
//   cls: 'cronpart-field',
//   config: {
//     partType: 'week',
//     labels: {
//       'day': 'Days',
//       'month': 'Months',
//       'week': 'Weekday'
//     },
//     helpers: {
//       'day': [{
//         label: 'Every Day',
//         value: '*'
//       // },{
//       //   label: 'Every 5 Days',
//       //   value: '*/5'
//       // },{
//       //   label: 'Every 10 Days',
//       //   value: '*/10'
//       // },{
//       //   label: 'Every Half Month',
//       //   value: '*/15'
//       }],
//       'month': [{
//         label: 'Every Month',
//         value: '*'
//       // },{
//       //   label: 'Every 3 Months',
//       //   value: '*/3'
//       // },{
//       //   label: 'Every 6 Months',
//       //   value: '*/6'
//       }],
//       'week': [{
//         label: 'Every Weekday',
//         value: '*'
//       // },{
//       //   label: 'Monday => Friday',
//       //   value: '1-5'
//       // },{
//       //   label: 'Weekend Days',
//       //   value: '0,6'
//       }]
//     },
//     listItems: {
//       'day': [{
//         text: 'Every Day', value: '*'
//       },{
//         text: 1, value: 1
//       },{
//         text: 2, value: 2
//       },{
//         text: 3, value: 3
//       },{
//         text: 4, value: 4
//       },{
//         text: 5, value: 5
//       },{
//         text: 6, value: 6
//       },{
//         text: 7, value: 7
//       },{
//         text: 8, value: 8
//       },{
//         text: 9, value: 9
//       },{
//         text: 10, value: 10
//       },{
//         text: 11, value: 11
//       },{
//         text: 12, value: 12
//       },{
//         text: 13, value: 13
//       },{
//         text: 14, value: 14
//       },{
//         text: 15, value: 15
//       },{
//         text: 16, value: 16
//       },{
//         text: 17, value: 17
//       },{
//         text: 18, value: 18
//       },{
//         text: 19, value: 19
//       },{
//         text: 20, value: 20
//       },{
//         text: 21, value: 21
//       },{
//         text: 22, value: 22
//       },{
//         text: 23, value: 23
//       },{
//         text: 24, value: 24
//       },{
//         text: 25, value: 25
//       },{
//         text: 26, value: 26
//       },{
//         text: 27, value: 27
//       },{
//         text: 28, value: 28
//       },{
//         text: 29, value: 29
//       },{
//         text: 30, value: 30
//       },{
//         text: 31, value: 31
//       },{
//         text: 'Last day of month', value: 'L'
//       }],
//       'month': [{
//         text: 'Every month', value: '*'
//       },{
//         text: 'Jan', value: 1
//       },{
//         text: 'Feb', value: 2
//       },{
//         text: 'Mar', value: 3
//       },{
//         text: 'Apr', value: 4
//       },{
//         text: 'May', value: 5
//       },{
//         text: 'Jun', value: 6
//       },{
//         text: 'Jul', value: 7
//       },{
//         text: 'Aug', value: 8
//       },{
//         text: 'Sep', value: 9
//       },{
//         text: 'Oct', value: 10
//       },{
//         text: 'Nov', value: 11
//       },{
//         text: 'Dec', value: 12
//       }],
//       'week': [{
//         text: 'Every day of week', value: '*'
//       },{
//         text: 'Sun',
//         value: 0
//       },{
//         text: 'Mon',
//         value: 1
//       },{
//         text: 'Tue',
//         value: 2
//       },{
//         text: 'Wed',
//         value: 3
//       },{
//         text: 'Thu',
//         value: 4
//       },{
//         text: 'Fri',
//         value: 5
//       },{
//         text: 'Sat',
//         value: 6
//       }]
//     }
//   },
//   initialize: function() {

//     var me = this;
//     var name = me.getName() + '-customfield';
//     var partType = me.getPartType();
//     var helpers = me.getHelpers()[partType];
//     helpers = null;
//     me.setLayout((helpers && helpers.length > 1) ? 'hbox' : 'vbox');
//     me.setLabel(me.getLabels()[partType]);
//     this.add([
//       {
//         xtype: 'radiogroup',
//         vertical: true,
//         defaults: {
//           xtype: 'radiofield',
//           labelAlign: 'right',
//           labelWidth: 140,
//           required: false,
//           name: name
//         },
//         items: helpers,
//         listeners: {
//           change: function(group, newValue) {
//             this.up('cronpartfield').down('list').toggleCls('kind-of-disabling', (newValue != undefined));
//           }
//         }
//       },
//       {
//         xtype: 'container',
//         items: {
//           xtype: 'list',
//           cls: 'cronpart-list',
//           selectable: {
//             mode: 'multi'
//           },
//           store: me.getListItems()[partType],
//           height: 142,
//           width: 140,
//           // items: {
//           //   docked: 'left',
//           //   xtype: 'radiofield',
//           //   cls: 'radioselect',
//           //   value: 'choose',
//           //   name: name
//           // },
//           listeners: {
//             select: function(list, records) {
//               if (records.length > 0) {
//                 this.down('list').down('radiofield').setChecked(true);
//               }
//             },
//             deselect: function(list, records) {
//               if (list.getSelectable().getSelectionCount() == 0) {
//                 this.down('radiofield').setChecked(true);
//               }
//             },
//             scope: me
//           }
//         }
//       }
//     ]);
//     this.callParent(arguments);
//   }
//   // initConfig: function (instanceConfig) {
//   //   var conf = instanceConfig || {};
//   //   if (!conf.dateFieldCfg) {
//   //     conf.dateFieldCfg = {};
//   //   }
//   //   if (!conf.timeFieldCfg) {
//   //     conf.timeFieldCfg = {};
//   //   }
//   //   var id = Ext.id();
//   //   if (!conf.dateFieldCfg['name']) {
//   //     conf.dateFieldCfg['name'] = conf['name'] ? 'datefield-' + conf['name'] : 'datefield-' + id;
//   //   }
//   //   if (!conf.timeFieldCfg['name']) {
//   //     conf.timeFieldCfg['name'] = conf['name'] ? 'timefield-' + conf['name'] : 'timefield-' + id;
//   //   }
//   //   Ext.apply(instanceConfig, {
//   //       items: [
//   //           Ext.apply({
//   //               xtype: 'datefield',
//   //               name: conf.dateFieldCfg['name'],
//   //               required: Ext.isDefined(instanceConfig.required) ? instanceConfig.required : false,
//   //               submitValue: false,
//   //               flex: 1
//   //           }, conf.dateFieldCfg),
//   //           Ext.apply({
//   //               xtype: 'timefield',
//   //               name: conf.timeFieldCfg['name'],
//   //               margin: '0 0 0 10',
//   //               allowBlank: Ext.isDefined(instanceConfig.required) ? instanceConfig.required : false,
//   //               submitValue: false,
//   //               flex: 1
//   //           }, conf.timeFieldCfg)
//   //       ]
//   //   });
//   //     this.callParent(arguments);
//   // }
// });
