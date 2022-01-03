Ext.define('Override.Dialog', {
  override: 'Ext.Dialog',
  // config: {
  //   // prevent maximize/restore animation since issue with duplicated elements (because of the animation proxy)
  //   maximizeAnimation: false,
  //   restoreAnimation: false,
  // }
  config: {
    // prevent error:  Ext.mixin.Container.attachNameRef(): Duplicate name: "id" on ext-viewport between ext-textfield-1 and ext-textfield-5
    // prevent maximize/restore animation proxy from having items/buttons
    maximizeProxy: {
      items: null,
      buttons: null
    }
  }
});