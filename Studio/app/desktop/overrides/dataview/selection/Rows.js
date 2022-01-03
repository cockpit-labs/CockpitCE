Ext.define('Override.dataview.selection.Rows', {
  override: 'Ext.dataview.selection.Rows',
  privates: {
    /* Fix setRangeEnd when grid is not infinite */

    /**
     * Used during drag/shift+downarrow range selection on change of row.
     * @param {Number} end The end row index of the row drag selection.
     * @private
     */
     setRangeEnd: function(end) {
      var me = this,
          dragRange = me.dragRange || (me.dragRange = [0, end]),
          oldEnd = dragRange[1],
          start = dragRange[0],
          view = me.view,
          renderInfo = view.renderInfo,
          tmp = dragRange[1] = end,
          removeRange = [],
          addRange = false,
          rowIdx, limit;

      // Ranges retain whatever start end end point, regardless of order
      // We just need the real start and end index to test candidates for inclusion.
      if (start > end) {
        end = start;
        start = tmp;
      }

      if (view.infinite) {
        rowIdx = Math.max(Math.min(dragRange[0], start, oldEnd, end),
                          renderInfo.indexTop);
  
        limit = Math.min(Math.max(dragRange[1], start, oldEnd, end),
                         renderInfo.indexBottom - 1);
      } else {
        rowIdx = Math.min(dragRange[0], start, oldEnd, end);
  
        limit = Math.max(dragRange[1], start, oldEnd, end);
      }

      // Loop through the union of previous range and newly set range
      for (; rowIdx <= limit; rowIdx++) {
        // If we are outside the current dragRange, deselect
        if (rowIdx < start || rowIdx > end) {
          view.onItemDeselect(rowIdx);
          removeRange[removeRange.length ? 1 : 0] = rowIdx;
        }
        else {
          view.onItemSelect(rowIdx, true);
          addRange = true;
        }
      }
      if (addRange) {
        me.addRange(true);
      }
      if (removeRange.length) {
        me.removeRecordRange(removeRange[0], removeRange[1]);
      }
      me.lastSelectedIndex = end;
    }
  }
});