Ext.define('Override.grid.cell.Tree', {
  override: 'Ext.grid.cell.Tree',
  /* FIX wrong parameter for events nodecollapse and nodeexpand
  /**
   * Collapse this tree node.
   */
  collapse: function() {
      var me = this,
          record = me.getRecord();

      me.getGrid()
          .fireEventedAction('nodecollapse', [/*me.parent*/ me.row, record, 'collapse'], 'doToggle', this);
  },

  /**
   * Expand this tree node.
   */
  expand: function() {
      var me = this,
          record = me.getRecord(),
          tree = me.getGrid(),
          siblings, i, len, sibling;

      tree.fireEventedAction('nodeexpand', [/*me.parent*/ me.row, record, 'expand'], 'doToggle', me);

      // Collapse any other expanded sibling if tree is singleExpand
      if (record.isExpanded && !record.isRoot() && tree.getSingleExpand()) {
          siblings = record.parentNode.childNodes;

          for (i = 0, len = siblings.length; i < len; ++i) {
              sibling = siblings[i];

              if (sibling !== record) {
                  sibling.collapse();
              }
          }
      }
  }
});