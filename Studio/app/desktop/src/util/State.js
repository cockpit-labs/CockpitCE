Ext.define('Studio.util.State', {
  extend: 'Ext.Base',
  requires: [
    'Ext.util.LocalStorage'
  ],
  singleton: true,
  store: new Ext.util.LocalStorage({
    id: 'cockpit-studio'
  }),
  get: function(key, defaultValue) {
    var value = this.store.getItem(key);
    return value === undefined ? defaultValue : Ext.decode(value);
  },
  set: function(key, value) {
    if (value == null) {
      this.store.removeItem(value);
    } else {
      this.store.setItem(key, Ext.encode(value));
    }
  },
  clear: function(key) {
    this.set(key, null);
  }
});
