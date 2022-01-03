Ext.define('Override.grid.Grid', {
  override: 'Ext.grid.Grid',
  privates: {
    updateTitle: function(title) {
      var titleBar = this.getTitleBar();
      if (titleBar) {
        if (title) {
          if (titleBar.setTitle) {
            titleBar.setTitle(title);
          } else if (titleBar.setText) {
            titleBar.setText(title);
          }
          if (titleBar.isHidden()) {
            titleBar.show();
          }
        }
        else {
          titleBar.hide();
        }
      }
    },
    updateTitleBar: function(titleBar) {
      if (titleBar) {
        if (titleBar.getTitle) {
          this.callParent(arguments);
        } else if (titleBar.getText) {
          titleBar.setText(this.getTitle());
        }
      }
    }
  }
});
