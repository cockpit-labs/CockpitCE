Ext.define('Override.data.request.Ajax', {
  override: 'Ext.data.request.Ajax',
  setupHeaders: function(xhr, options, data, params) {
    if (options.method === 'PATCH') {
      options.headers = Ext.apply({}, options.headers || {}, {
        'Content-Type': 'application/merge-patch+json'
      });
    } else if (data instanceof FormData) {
      // means we need to upload
      // temporarly remove data to make parent ignore setting Content-Type header
      return this.callParent([xhr, options, null, null]);
    }
    return this.callParent(arguments);
  }
});