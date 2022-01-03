Ext.define('Studio.model.validators.MinMaxValidator', {
  extend: 'Ext.data.validator.Validator',
  alias: 'data.validator.minmax',
  type: 'minmax',
  config: {
    minField: null,
    message: null,
  },
  /**
   */
  validate: function(value, record) {
    var minField = this.getMinField();
    if (!minField) {
      console.error('Failed to initialize MinMaxValidator. No minField name given.');
      return true;
    }
    var min = record.get(minField);
    if (value < min) {
      return this.getMessage();
    }
    return true;
  }
});
