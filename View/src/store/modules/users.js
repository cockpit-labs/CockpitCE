import { http } from '@/plugins/http'
import User from '@/models/User'

const state = {
  user: {
    username: null,
    fullname: null
  }
}

const getters = {
  loggedUser: state => {
    return state.user
  }
}

const mutations = {
  setUser (state, user) {
    state.user = user
  }
}

const actions = {
  async getUsers () {
    const users = await http.get('users')

    await User.create({ data: users.data })
  }
}

export default {
  state,
  getters,
  mutations,
  actions
}
