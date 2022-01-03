Ext.define('Override.grid.TreeDropZone', {
  override: 'Ext.grid.TreeDropZone',
  /**
   * @param {*} draggedData 
   * @param {*} targetRecord 
   * @param {*} highlight 
   * @param {*} info 
   * @returns `false` if the drop cannot be achieved else return `true`
   */
  customCheckIfTargetIsValid: function(record, draggedData, targetRecord, highlight, info) {
    var targetCmp = Ext.dd.Manager.getTargetComp(info);
    var positionCls = this.dropMarkerCls + '-' + highlight;

    // means a fake record is moved (see util/Utils.js for fake blocks and questions generation)
    if (record.get('fake') === true) {
        // console.log('fake === true');
        return false;
    }
    // source & target node type
    var sourceType = record.get('nodeType');
    var targetType = targetRecord.get('nodeType');
    if (!sourceType) {
        console.error('Source node has a null nodeType');
    } else if (!targetType) {
        console.error('Target node has a null nodeType');
    }
    var isSourceBlock = (sourceType == 'Studio.model.BlockNode');
    var isTargetBlock = (targetType == 'Studio.model.BlockNode');
    if (isTargetBlock) { // target is a block
        if (highlight === 'after' && targetRecord.get('fake') === true) {
            // console.log('highlight === after && targetRecord.get(fake');
            return false;
        }
        if (isSourceBlock) {
            if (targetCmp) {
                // if (highlight === 'before' && !targetCmp.hasCls(positionCls))
                if (targetCmp.hasCls(positionCls) || highlight === 'after') {
                    return false;
                } else {
    
                }
                // if (targetCmp.hasCls(positionCls) && isSourceBlock && highlight === 'after') {
                //     // No way to move block inside block
                //     // console.log(' No way to move block inside block');
                //     return false;
                // }
            }
            if (targetRecord.previousSibling && targetRecord.previousSibling === record) {
                // console.log('prevent from moving a block just after itself');
                // prevent from moving a block just after itself and make tree dirty for nothing
                // cannot be done with question since when dropping over a question makes this one expand and allows to drop a linked question
                return false;
            }
        } else {
            // a question cannot be moved before a block
            if (targetCmp && !targetCmp.hasCls(positionCls) && highlight === 'before') {
                // console.log('a question cannot be moved before a block');
                return false;
            }
        }
    } else { // target is a question
        if (isSourceBlock) {
            // No way to move block inside question
            // console.log('No way to move block inside question');
            return false;
        }
        // question should not be moved after itself and make tree dirty for nothing
        if (targetRecord.previousSibling && targetRecord.previousSibling === record && highlight === 'before') {
            // console.log('prevent from moving a question just after itself');
            return false;
        }
    }
    // if ((targetRecord.get('nodeType') == 'Studio.model.Question') && (record.get('nodeType') == 'Studio.model.BlockNode')) {
    //   return false;
    // }
    // if (targetCmp && targetCmp.hasCls(positionCls)) {
    //   if ((record.get('nodeType') == 'Studio.model.BlockNode') && (targetRecord.get('nodeType') == 'Studio.model.BlockNode')) {
    //     console.log('No way to move block inside block');
    //     return false;
    //   }
    // } else {
      
    // }
    
    // console.log(highlight,info);
//          if (record.get('nodeType') == 'Studio.model.BlockNode' && targetRecord.get('nodeType') == 'Studio.model.BlockNode') {
//            console.log('No way to move block inside block');
//            return false;
//          }
//          console.log(info.cursor.current.x, highlight, info);

//          if (targetCmp && !targetCmp.hasCls(positionCls) && record.get('nodeType') == 'Studio.model.BlockNode' && ) {
//          	return false;
//          }
    // end Cockpit specifics
  },
  /**
   * Return `false` if the target node is child of source dragged data 
   * else return `true`
   * 
   * @param {[Ext.data.Model]} draggedData 
   * @param {Ext.data.Model} targetRecord
   * @param {String} highlight Drop position
   */
  isTargetValid: function(draggedData, targetRecord, highlight, info) {
      var ln = draggedData.length,
          count = 0,
          i, record;

      for (i = 0; i < draggedData.length; i++) {
          record = draggedData[i];

          // Avoid parent node to be dragged into child node
          if (record.contains(targetRecord)) {
              return false;
          }
//if (record.get('label') == "ceci est ma question oui / non.") {
//  return false;
//}
//if (targetRecord.get('label') == "ceci est ma question oui / non.") {
//  return false;
//}
          // Cockpit cusom check
          if (this.customCheckIfTargetIsValid(record, draggedData, targetRecord, highlight, info) === false) {
            return false;
          }
          
          if ((record.parentNode === targetRecord) &&
              (highlight !== 'before' || targetRecord.isRoot())) {
              ++count;
          }
      }

      return count < ln ? true : false;
  },

  onDragMove: function(info) {
      var me = this,
          ddManager = Ext.dd.Manager,
          targetCmp = ddManager.getTargetComp(info),
          addDropMarker = true,
          highlight, isValidTargetNode,
          ddRecords, isSameGroup, isValidTarget,
          targetRecord, positionCls;
      // 
          // highlight = ddManager.getPosition(info, targetCmp);
      var percentDiff = ddManager.getPercentPosition(info, targetCmp);
      highlight = percentDiff < 50 ? 'before' : 'after';

      positionCls = me.dropMarkerCls + '-' + highlight;

      if (!targetCmp || targetCmp.hasCls(positionCls)) {
          return;
      }

      if (targetCmp.getRecord) {
          targetRecord = targetCmp.getRecord();
      }

      isSameGroup = Ext.Array.intersect(me.getGroups(), info.source.getGroups()).length;

      if (!targetRecord || !isSameGroup) {
          if (me.ddEl) {
              me.removeDropMarker();
          }

          return;
      }

      ddRecords = info.data.dragData.records;
      isValidTarget = ddRecords.indexOf(targetRecord) === -1;
      isValidTargetNode = me.isTargetValid(ddRecords, targetRecord, highlight, info);

      // ASSERT: same node and parent to be dropped in child node
      if (!isValidTarget || !isValidTargetNode) {
        // // CC
        // if (isValidTargetNode === 0) {
        //   console.log('is 0 == yes');
        //   me.cancelExpand();
        // } else {
        //   if (me.ddEl) {
        //     me.removeDropMarker();
        //   }
        //   me.cancelExpand();
        //   return;
        // }
          
        //   // CC
          
         if (me.ddEl) {
           me.removeDropMarker();
         }
         me.cancelExpand();
         return;
      }

      if (me.ddEl) {
          me.removeDropMarker();
      }

      me.cancelExpand();
      me.ddEl = targetCmp;

      if (!targetRecord.isLeaf()) {
          addDropMarker = false;
          ddManager.toggleProxyCls(info, me.validDropCls, true);

          if ((!targetRecord.isExpanded() && highlight === 'after') ||
              (!targetRecord.isRoot() && highlight === 'before')) {
            //   addDropMarker = true;
            // force drop marker
            me.addDropMarker(targetCmp, [me.dropIndicator, positionCls]);
          }

            // Cockpit specifics
            // allows node expansion when drag over node
 //         if (highlight === 'after' && me.allowExpandOnHover) {
          if ( me.allowExpandOnHover && percentDiff > 30 && percentDiff < 70) {
                me.timerId = Ext.defer(me.expandNode, me.expandDelay, me, [targetRecord]);
            // if (targetCmp && !targetCmp.hasCls(positionCls)) {
            //     if (this.test != ('!targetCmp.hasCls(' + positionCls + ')')) {
            //         this.test = '!targetCmp.hasCls(' + positionCls + ')';
            //         console.log('!targetCmp.hasCls(' + positionCls + ')');
            //     }
//            var allowExpand = true;
//            for (i = 0; i < ddRecords.length; i++) {
//              record = ddRecords[i];
//              if (record.get('nodeType') == 'Studio.model.BlockNode') {
//                allowExpand = false;
//                break;
//              }
//            }
//            if (allowExpand) {
//              me.timerId = Ext.defer(me.expandNode, me.expandDelay, me, [targetRecord]);
//            }
            // console.log('will expand node ' + targetRecord.get('label'));
            //   me.timerId = Ext.defer(me.expandNode, me.expandDelay, me, [targetRecord]);

            // } else {
            //     if (this.test != ('targetCmp.hasCls(' + positionCls + ')')) {
            //         this.test = 'targetCmp.hasCls(' + positionCls + ')';
            //         console.log('targetCmp.hasCls(' + positionCls + ')');
            //     }
            // }
            // end Cockpit specifics
          }
      }

      if (addDropMarker) {
          me.addDropMarker(targetCmp, [me.dropIndicator, positionCls]);
      }
  },

  onNodeDrop: function(dragInfo) {
      var me = this,
          targetNode = dragInfo.targetNode,
          draggedData = dragInfo.draggedData,
          records = draggedData.records,
          len = records.length,
          targetRecord = dragInfo.targetRecord,
          position = dragInfo.position,
          isRoot = targetRecord.isRoot(),
          parentNode = targetRecord.parentNode || me.view.getRootNode(),
          action = 'appendChild',
          args = [null],
          i, nextSibling, selectable, selModel;

      if (me.copy) {
          for (i = 0; i < len; i++) {
              records[i] = records[i].copy(undefined, true, true);
          }
      }

      if (position === 'before') {
          if (!isRoot) {
              action = 'insertBefore';
              args = [null, targetRecord];
          }
      }
      else if (position === 'after') {
          nextSibling = targetRecord.nextSibling;

        //   if (isRoot || !targetRecord.isLeaf()) {
        //       parentNode = targetRecord;
        //   }
        // CC
          if (isRoot || !targetRecord.isLeaf()) {
                targetRecord.expand(false, function() {parentNode = targetRecord});
            //   if (targetRecord.isExpanded()) {
                // parentNode = targetRecord;
            //   } else if (nextSibling) {
            //     args = [null, nextSibling];
            //     action = 'insertBefore';
            //   }
          }
        // end CC

          else if (nextSibling) {
              if (targetRecord.isLeaf()) {
                  args = [null, nextSibling];
                  action = 'insertBefore';
              }
              else {
                //   if (targetRecord.isExpanded()) {
                    parentNode = targetRecord;
                //   } else {
                //     action = 'insertBefore';
                //   }

              }
              // CC
            //   else {
            //       parentNode = targetRecord;
            //   }
          }
      }

      me.arrangeNode(parentNode, records, args, action);

      // Select the dropped nodes
      selectable = me.view.getSelectable();
      selModel = selectable.getSelection().getSelectionModel();
      selModel.select(records);

      me.view.fireEvent('drop', targetNode, draggedData, targetRecord, position);
      delete me.dragInfo;
  },

  onDrop: function(info) {
      var me = this,
          view = me.view,
          targetNode = me.ddEl,
          draggedData, targetRecord, position;

      // Cancel any pending expand operation
      me.cancelExpand();

      if (!targetNode) {
          return;
      }

      targetRecord = targetNode.getRecord();

      if (!targetRecord.isNode) {
          return;
      }

      draggedData = info.data.dragData;
      position = targetNode.hasCls(me.dropMarkerCls + '-before') ? 'before' : 'after';
      me.prepareRecordBeforeDrop(draggedData, targetRecord, position);

      // Prevent drop if dragged record is empty
      if (!draggedData.records.length) {
          return;
      }

      if (view.fireEvent('beforedrop',
                         targetNode, draggedData, targetRecord, position) !== false) {

          me.dragInfo = {
              draggedData: draggedData,
              targetRecord: targetRecord,
              position: position,
              targetNode: targetNode
          };

          if (!targetRecord.isExpanded() && position === 'after') {
            //   targetRecord.expand(undefined, me.confirmDrop, me);
            me.confirmDrop();
          }
          else if (targetRecord.isLoading()) {
              targetRecord.on({
                  expand: 'confirmDrop',
                  single: true,
                  scope: me
              });
          }
          else {
              me.confirmDrop();
          }
      }

      me.removeDropMarker();
  }
});