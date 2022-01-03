import { Model } from '@vuex-orm/core'
import Group from './Group'
import User from './User'
import { DateTime } from 'luxon'

class Folder extends Model {
  static entity = 'folders'

  static fields () {
    return {
      id: this.attr(null),
      label: this.attr(null),
      description: this.attr(null),
      state: this.attr(null),
      availableFrom: this.attr(null),
      availableUntil: this.attr(null),
      questionnaires: this.attr(null),
      groupId: this.attr(null),
      folderTemplateId: this.attr(null),
      updatedAt: this.attr(null),
      createdBy: this.attr(null),
      user: this.belongsTo(User, 'createdBy', 'username'),
      group: this.belongsTo(Group, 'groupId')
    }
  }

  get formatedUpdatedDate () {
    return DateTime.fromISO(this.updatedAt).toLocaleString(DateTime.DATE_SHORT)
  }
}

export default Folder
