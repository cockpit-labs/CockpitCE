Ext.define('Studio.view.folder.Folders', {
  extend: 'Studio.view.base.View',
  xtype: 'folders',
  userCls: 'c-folders',
  alias: 'widget.folders',
  requires: [
    'Studio.view.folder.FoldersController',
    'Studio.view.folder.FoldersModel',
    'Studio.view.folder.FolderProperties'
  ],
  controller: 'folders',
  viewModel: {
    type: 'folders'
  },
  config: {
    listTitle: 'Folders',
    listIconCls: 'x-fi md-icon-folder-special',
    emptyMessage: 'Select a folder or create a new one'
  },
  defaults: {
  },
  layout: {
    type: 'hbox'
  },
  items: [{ // before panel, superclass adds a list at the left side
    title: 'Details',
    flex: 1,
    reference: 'properties',
    iconCls: 'x-fa fa-file-alt',
    xtype: 'folderproperties',
    hidden: true,
    bind: {
      title: '{record.label}',
      hidden: '{!record}',
      record: '{record}'
    }
  }]
});