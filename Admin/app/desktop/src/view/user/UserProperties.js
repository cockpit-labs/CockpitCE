Ext.define('Admin.view.user.UserProperties', {
  extend: 'Admin.view.base.Properties',
  xtype: 'userproperties',
  alias: 'widget.userproperties',
  requires: [
    'Ext.field.Toggle',
    'Ext.field.Email',
    'Ext.field.Date',
    'Ext.form.FieldSet'
  ],
  items: {
    xtype: 'fieldset',
    layout: {
      type: 'vbox',
      align: 'center'
    },
    fieldDefaults: {
      labelAlign: 'left',
      errorTarget: 'side'
    },
    items: [{
      xtype: 'textfield',
      label: 'ID',
      name: 'id',
      disabled: true,
      width: 350,
      bind: {
        hidden: '{record.phantom}',
        value: '{record.id}'
      }
    },{
      xtype: 'textfield',
      itemId: 'firstField',
      label: 'Username',
      required: true,
      name: 'username',
      // force validation at init
      // not needed if disabled is binded
      // validateOnInit: 'all',
      // value: '',
      //
      bind: {
        disabled: '{!record.phantom}',
        value: '{record.username}'
      },
      width: 350
    },{
      xtype: 'emailfield',
      label: 'Email',
      required: true,
      width: 350,
      name: 'email',
      bind: '{record.email}',
      validators: 'email'
    },{
      xtype: 'textfield',
      label: 'First name',
      name: 'firstname',
      bind: '{record.firstname}',
      width: 350
    },{
      xtype: 'textfield',
      label: 'Last name',
      name: 'lastname',
      bind: '{record.lastname}',
      width: 350
    },{
      xtype: 'togglefield',
      label: 'User enabled?',
      name: 'enabled',
      bind: {
        value: '{record.enabled}',
        disabled: '{expired}'
      }
    },{
      xtype: 'togglefield',
      label: 'Email verified?',
      name: 'emailVerified',
      disabled: true,
      bind: '{record.emailVerified}'
    },{
      xtype: 'datefield',
      destroyPickerOnHide: true,
      name: 'dateStart',
      label: 'Expiration date',
      bind: {
        value: '{record.expirationDate}',
        disabled: '{!superuser}'
      },
      width: 350
    }]
  }
});