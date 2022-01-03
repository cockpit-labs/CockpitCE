import { Model } from '@vuex-orm/core'
import Group from './Group'
import GroupFolderTemplate from './GroupFolderTemplate'

class FolderTemplate extends Model {
  static entity = 'folder_templates'

  static fields () {
    return {
      id: this.attr(null),
      label: this.attr(null),
      description: this.attr(null),
      availableFrom: this.attr(null),
      availableUntil: this.attr(null),
      questionnaires: this.attr(null),
      groups: this.belongsToMany(Group, GroupFolderTemplate, 'folderTemplateId', 'groupId'),
      periods: this.attr(null)
    }
  }
}

export default FolderTemplate
