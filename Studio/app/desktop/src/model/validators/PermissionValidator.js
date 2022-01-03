Ext.define('Studio.model.validators.PermissionsValidator', {
  extend: 'Ext.data.validator.Validator',
  alias: 'data.validator.permissions',
  type: 'permissions',
  config: {
    message: null
  },
  /**
   * @param: value should be an array
   */
  validate: function(value) {
    // considering null/undefined value as valid
    if (Ext.isDefined(value)) {
      for (var i=0; i < value.length; i++) {
        var val = value[i];
        if (val.get) {
          if (!val.get('userRole') || !val.get('targetRole') || !val.get('right')) {
            return this.getMessage();
          }
        } else if (!val['userRole'] || !val['targetRole'] || !val['right']) {
          return this.getMessage();
        }
      }
    }
    return true;
  }
});
