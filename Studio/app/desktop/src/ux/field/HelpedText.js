Ext.define('Studio.ux.field.HelpedText', {
  extend: 'Ext.field.Text',
  xtype: 'helptextfield',
  requires: [
    'Studio.ux.field.trigger.Help'
  ],
  config: {
    helpTitle: 'Help',
    helpText: 'Help text...'
  },
  onHelpIconTap: function(input, e) {
    this.fireAction('helpicontap', [this, input, e], 'doHelpIconTap');
  },
  /**
   * @private
   */
  doHelpIconTap: function() {
    Ext.Msg.alert(this.getHelpTitle(), this.getHelpText());
  }
});
