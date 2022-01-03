Ext.define('Studio.ux.field.trigger.Help', {
    extend: 'Ext.field.trigger.Trigger',
    xtype: 'helptrigger',
    alias: 'trigger.help',
    classCls: Ext.baseCSSPrefix + 'helptrigger',
    weight: -1000,
    handler: 'onHelpIconTap',
    scope: 'this'
});