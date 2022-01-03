Ext.override(Ext.data.Store, {
  /**
   * since getNewRecords checks if phantoms are valid,
   * we need a filterNewOnly methods to check if there is a valid calendar for folder creation
   */
  getPhantomRecords: function() {
    // Store filterNewOnly should exist
    return this.filterDataSource(this.filterNewOnly);
  }
});