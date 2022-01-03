Ext.define('Studio.view.main.MainController', {
  extend: 'Ext.app.ViewController',
  alias: 'controller.main',
  routes: {
    ':type(/:args)?': {
      action: 'handleNavigationRoute',
      conditions: {
        ':type': '(calendars|folders)',
        ':args': '(.*)'
      }
    }
  },
  control: {
    'main': {
      'beforeactiveitemchange': 'onBeforeActivateItemChange'
    }
  },
  onBeforeActivateItemChange: function(main, tab) {
    var viewId = tab.viewId;
    if (viewId) {
      if (viewId === 'logout') {
        this.fireEvent('logout');
      } else {
        this.redirectTo(viewId); 
      }
    }
  },
  /**
   * @param {String} ref Component reference, MUST be valid.
   * @protected
   */
  activate: function(ref) {
    var view = ref.isComponent? ref : this.lookup(ref),
        child = view,
        parent;
    while (parent = child.getParent()) {
        parent.setActiveItem(child);
        child = parent;
    }
    return view;
  },

  getContainerForViewId: function() {
    return this.getView();
  },

  ensureView: function(id, config, route) {
    var container = this.getContainerForViewId(id);
    var item = container.child('component[viewId=' + id + ']');
    var reset = !!item;

    if (!item) {
      item = container.add(Ext.apply({ viewId: id }, config));
    }

    if (Ext.isDefined(item.config.route)) {
      item.setRoute(route);
    }

    // Reset the component (form?) only if previously instantiated (i.e. with outdated data).
    if (reset && Ext.isFunction(item.reset)) {
      item.reset();
    }

    return item;
  },
  handleNavigationRoute: function(type, args) {
    this.activate(
      this.ensureView(type, {
        title: 'test',
        xtype: type
      }, args)
    );
  }
});
