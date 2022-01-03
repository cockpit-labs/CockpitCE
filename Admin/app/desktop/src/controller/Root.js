/**
 * This global controller manages the login view and ensures that view is created when
 * the application is launched. Once login is complete we then create the main view.
 */
 Ext.define('Admin.controller.Root', {
  extend: 'Ext.app.Controller',

  requires: [
  ],
  onLaunch: function() {
      // this.session = new Ext.data.Session({
      //     autoDestroy: false
      // });
  }
});
