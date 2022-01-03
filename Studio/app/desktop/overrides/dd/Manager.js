//Ext.define('Override.dd.Manager', {
//  override: 'Ext.dd.Manager',
//  singletonOverride: function(item, options) {
//    // prevent error when list is not infinite
//    if (this.infinite) {
//      this.callParent(arguments);
//    }
//  }
//},
//function() {
//  this.singletonOverride();
//});
Ext.define('Override.dd.Manager', {},
function() {
  Ext.apply(Ext.dd.Manager, {
    // Cockpit specific
    // need to have more precise position in order to expand node or not in TreeDropzone
    // same as getPosition except it returns percentage
    /**
     * return current cursor position over target node
     * with respect to axis
     * Defaults to `y`
     * @param {String} axis `x` or `y` axis
     */
    getPercentPosition: function(info, targetNode, axis) {
        var cursorPosition = info.cursor.current,
            targetBox = targetNode.element.getBox(),
            posDiff, nodeSize, percentDiff;

        if (targetNode.getRecord) {
          var record =  targetNode.getRecord();
          if (record && record.get('fake')) {
            // always return a value < 50 for fake nodes
            return 49;
          }
        }
        axis = axis || 'y';
        posDiff = cursorPosition[axis] - targetBox[axis];
        nodeSize = targetNode.element[axis === 'y' ? 'getHeight' : 'getWidth']();
        percentDiff = (posDiff / nodeSize) * 100;

        return percentDiff;
    }
  });
});
