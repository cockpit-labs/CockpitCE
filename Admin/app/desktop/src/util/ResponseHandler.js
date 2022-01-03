Ext.define('Admin.util.ResponseHandler', {
  extend: 'Ext.Base',
  singleton: true,
  handleResponse: function(operation, defaultTitle, defaultMessage, callback, scope) {
    if (operation) {
      var error = operation.error;
      if (error) {
        var response = error.response;
        if (response.responseType == 'json') {
          var jsonReponse =  response.responseJson;
          if (jsonReponse) {
            Ext.Msg.alert(jsonReponse.title ? jsonReponse.title : defaultTitle, jsonReponse.detail ? jsonReponse.detail : defaultMessage, callback, scope);
            return;
          } else if (response.statusText) {
            Ext.Msg.alert(defaultTitle, defaultMessage + Ext.String.format(' ({0} {1})', response.status, response.statusText), callback, scope);
            return;
          }
        }
      }
    }
    Ext.callback(callback, scope);
  }
});
