Ext.define('Studio.view.base.ViewModel', {
  extend: 'Ext.app.ViewModel',
  alias: 'viewmodel.base',
  data: {
    record: null,
    validCnt: 0
  },
  formulas: {
    hasError: {
      bind: {
        record: '{record}',
        validCnt: '{validCnt}'
      },
      get: function (data) {
        var record = data.record;
        return record ? !record.isValid() : false;
      }
    }
  }
});
