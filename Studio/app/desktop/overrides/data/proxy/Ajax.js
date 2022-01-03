Ext.define('Override.data.proxy.Ajax', {
  override: 'Ext.data.proxy.Ajax',
  /**
   *  WARNING : With ExJS 7x we actually don't need to return anything from this function 'sendRequest'
   *  JsonP and Ext.Direct do things differently but for Ajax proxy, we return nothing
   *  
   *  TODO : check for ExtJs future releases if we could use Promise
   */ 
  sendRequest: function(request) {
    var me = this;
    var controller = Ext.Viewport.getController();
    controller.refreshToken(
        function() {
          request.setRawRequest(Ext.Ajax.request(request.getCurrentConfig()));
          me.lastRequest = request;
        },
        controller
    );
  }
});