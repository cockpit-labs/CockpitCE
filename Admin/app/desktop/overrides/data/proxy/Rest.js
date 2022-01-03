Ext.define('Override.data.proxy.Rest', {
  override: 'Ext.data.proxy.Rest',
  config: {
    actionMethods: {
      create: 'POST',
      read: 'GET',
      update: 'PATCH',
      destroy: 'DELETE'
    }
  }
});