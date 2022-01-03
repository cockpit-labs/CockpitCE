import { Model } from '@vuex-orm/core'

class User extends Model {
  static entity = 'users'

  static fields () {
    return {
      id: this.attr(null),
      username: this.attr(null),
      firstname: this.attr(null),
      lastname: this.attr(null)
    }
  }

  get fullname () {
    return `${this.firstname} ${this.lastname}`
  }
}

export default User
