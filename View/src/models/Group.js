import { Model } from '@vuex-orm/core'
import FolderTemplate from './FolderTemplate'
import GroupFolderTemplate from './GroupFolderTemplate'

class Group extends Model {
  static entity = 'groups'

  static fields () {
    return {
      id: this.attr(null),
      name: this.attr(null),
      parentId: this.attr(null),
      parent: this.belongsTo(Group, 'parentId'),
      children: this.hasMany(Group, 'parentId'),
      attributes: this.attr(null),
      icon: this.attr(null),
      targetsAncestor: this.boolean(false),
      root: this.boolean(false),
      folderTemplates: this.belongsToMany(FolderTemplate, GroupFolderTemplate, 'groupId', 'folderTemplateId')
    }
  }
}

export default Group
