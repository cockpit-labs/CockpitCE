/**
 * @class Admin.ux.TreeProxy
 * @version 1.0
 * A tree proxy that appends id for all nodes but root
 **/
 Ext.define('Admin.ux.TreeProxy', {
  extend: 'Ext.data.proxy.Rest',
  alias: 'proxy.treerest',
  buildUrl: function(request) {
    this.setAppendId(request.getOperation().getId() != 'root');
    return this.callParent(arguments);
  }
});