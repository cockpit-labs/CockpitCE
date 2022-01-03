import { Model } from '@vuex-orm/core'

class GroupFolderTemplate extends Model {
  static entity = 'group_folder_template'

  static primaryKey = ['groupId', 'folderTemplateId']

  static fields () {
    return {
      groupId: this.attr(null),
      folderTemplateId: this.attr(null)
    }
  }
}

export default GroupFolderTemplate
