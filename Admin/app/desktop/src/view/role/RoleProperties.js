Ext.define('Admin.view.role.RoleProperties', {
  extend: 'Admin.view.base.Properties',
  xtype: 'roleproperties',
  alias: 'widget.roleproperties',
  requires: [
    'Ext.field.Toggle',
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
      label: 'Role name',
      required: true,
      name: 'name',
      // force validation at init
      // not needed if disabled is binded
      validateOnInit: 'all',
      value: '',
      //
      bind: {
        // disabled: '{wouldForceValidation}'
        value: '{record.name}'
      },
      width: 350
    },{
      xtype: 'textfield',
      label: 'Description',
      width: 350,
      name: 'description',
      bind: '{record.description}'
    },{
      xtype: 'togglefield',
      label: 'System role?',
      name: 'system',
      disabled: true,
      bind: {
        hidden: '{record.phantom}',
        value: '{record.system}'
      }
    }]
  }
});