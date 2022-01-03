Ext.define('Admin.view.main.MainController', {
  extend: 'Ext.app.ViewController',
  alias: 'controller.main',
  routes: {
    ':type(/:args)?': {
      action: 'handleNavigationRoute',
      conditions: {
        ':type': '(users|groups|roles)',
        ':args': '(.*)'
      }
    }
  },
  control: {
    'main': {
      'beforeactiveitemchange': 'onBeforeActivateItemChange'
    },
    'main component[viewId=logout]': {
      'activate': 'logout'
    }
  },
  logout: function() {
    Ext.defer(this.fireEvent, 500, this, ['logout']); // this.fireEvent('logout');
  },
  onBeforeActivateItemChange: function(main, tab) {
    var viewId = tab.viewId;
    if (viewId && viewId !== 'logout') {
      this.redirectTo(viewId);
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
    this.tryStoreLoad(view.getStore(), 0);
    return view;
  },
  tryStoreLoad: function(store, count) {
    if (count == 5) {
      return;
    }
    if (store == null) {
      console.warn("Have been trying to check view store 5 times. Cannot automatically load store");
      // happens sometimes when activate is done before grid.updateStore
      Ext.defer(this.tryStoreLoad, 200, this, [store, count + 1]);
    } else {
      var source = store;
      if (source.type == 'chained') {
        source = store.getSource();
      }
      if (!source.loadCount) {
        if (source.isTreeStore) {
          source.getRoot().expand();
        } else {
          source.load();
        }
      }
    }
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
        xtype: type
      }, args)
    );
  }
});
