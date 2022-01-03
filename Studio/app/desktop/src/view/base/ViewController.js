Ext.define('Studio.view.base.ViewController', {
  extend: 'Ext.app.ViewController',
  alias: 'controller.base',
  control: {
    'field': {
      change: 'onFieldValueChange'
    }
  },
  onFieldValueChange: function(field) {
    var vm = this.getViewModel();
    vm.set('validCnt', vm.get('validCnt') + 1);
  }
});
