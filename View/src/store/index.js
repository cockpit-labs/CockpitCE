import Vue from 'vue'
import Vuex from 'vuex'
import groups from './modules/groups'
import folders from './modules/folders'
import users from './modules/users'
import VuexORM from '@vuex-orm/core'
import Folder from '@/models/Folder'
import User from '@/models/User'
import Group from '@/models/Group'
import FolderTemplate from '@/models/FolderTemplate'
import GroupFolderTemplate from '@/models/GroupFolderTemplate'

Vue.use(Vuex)

const database = new VuexORM.Database()
database.register(Folder)
database.register(User)
database.register(Group)
database.register(FolderTemplate)
database.register(GroupFolderTemplate)

export default new Vuex.Store({
  state: {
    targetListWithTiles: false,
    showTargetData: false,
    filterDateStart: null,
    filterDateEnd: null,
    periodSelectorValue: {
      selectedPeriod: null,
      dateStart: null,
      dateEnd: null
    },
    folderDataLoading: false
  },

  getters: {
    isFolderDataLoading: state => {
      return state.folderDataLoading
    }
  },

  mutations: {
    setFilterDateStart (state, date) {
      state.filterDateStart = date
    },
    setFilterDateEnd (state, date) {
      state.filterDateEnd = date
    },
    resetDateFilters (state) {
      state.filterDateStart = null
      state.filterDateEnd = null
    },
    setPeriodSelectorValue (state, object) {
      state.periodSelectorValue = object
    },
    setFolderDataLoading (state, isLoading) {
      state.folderDataLoading = isLoading
    }
  },

  modules: {
    groups,
    folders,
    users
  },

  plugins: [VuexORM.install(database)]
})
