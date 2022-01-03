import { http } from '@/plugins/http'
import FolderTemplate from '@/models/FolderTemplate'
import Folder from '@/models/Folder'
import { DateTime } from 'luxon'
import qs from 'qs'

const state = {
  selectedFolderTemplateId: null,
  selectedQuestionnaireId: null
}

const getters = {
  selectedFolderTemplate: state => {
    return FolderTemplate.find(state.selectedFolderTemplateId)
  },

  todayFolderTemplates: (state, getters) => {
    const templates = FolderTemplate.query()
      .where('availableFrom', date => {
        const diff = DateTime.fromISO(date).diffNow().as('milliseconds')
        return diff <= 0
      })
      .where('availableUntil', date => {
        const diff = DateTime.fromISO(date).diffNow().as('milliseconds')
        return diff >= 0
      })
      .with('groups')
      .orderBy('availableUntil')
      .get()

    return templates.filter(t => {
      return (Array.isArray(t.groups) && t.groups.length) &&
      (!getters.draftFolders.some(draft => draft.folderTemplateId === t.id))
    })
  },

  draftFolders: () => {
    return Folder.query()
      .where('state', 'DRAFT')
      .with('group')
      .orderBy('updatedAt', 'desc')
      .get()
  }
}

const mutations = {
  setSelectedFolderTemplateId (state, folderId) {
    state.selectedFolderTemplateId = folderId
  },

  setSelectedQuestionnaireId (state, questionnaireId) {
    state.selectedQuestionnaireId = questionnaireId
  }
}

const actions = {
  async getTemplates () {
    const templateFolders = await http.get('folder_tpls')

    await FolderTemplate.create({
      data: templateFolders.data.map(f => {
        return {
          id: f.id,
          label: f.label,
          description: f.description,
          availableFrom: f.periodStart,
          availableUntil: f.periodEnd,
          questionnaires: f.questionnaireTpls
        }
      })
    })
  },

  async getDraftFolders () {
    const folders = await http.get('folders', {
      params: {
        state: ['DRAFT']
      },
      paramsSerializer: params => qs.stringify(params, { arrayFormat: 'brackets' })
    })

    await Folder.create({
      data: folders.data.map(f => {
        return {
          id: f.id,
          label: f.label,
          description: f.description,
          state: f.state,
          availableFrom: f.periodStart,
          availableUntil: f.periodEnd,
          questionnaires: f.questionnaires,
          groupId: f.appliedTo,
          folderTemplateId: f.folderTplId,
          updatedAt: f.updatedAt,
          createdBy: f.createdBy
        }
      })
    })
  },

  async instantiateFolder (context, params) {
    const { groupId, folderTemplateId } = params
    const folderInstantiated = await http.post('folders/create', {
      appliedTo: groupId,
      folderTpl: folderTemplateId
    })

    const dataInserted = await Folder.insert({
      data: {
        id: folderInstantiated.data.id,
        label: folderInstantiated.data.label,
        description: folderInstantiated.data.description,
        state: folderInstantiated.data.state,
        availableFrom: folderInstantiated.data.periodStart,
        availableUntil: folderInstantiated.data.periodEnd,
        questionnaires: folderInstantiated.data.questionnaires,
        groupId: folderInstantiated.data.appliedTo,
        folderTemplateId: folderInstantiated.data.folderTplId,
        updatedAt: folderInstantiated.data.updatedAt,
        createdBy: folderInstantiated.data.createdBy
      }
    })

    return Promise.resolve(dataInserted.folders[0])
  },

  async saveFolder (context, folder) {
    const response = await http.patch('folders/' + folder.id, folder,
      { headers: { 'Content-Type': 'application/merge-patch+json' } })

    if (response.data) {
      await Folder.update({
        id: folder.id,
        questionnaires: response.data.questionnaires,
        updatedAt: response.data.updatedAt
        // state: response.data.state
      })
    }
    return Promise.resolve()
  },

  async actionOnFolder (context, { action, folderId }) {
    const response = await http.patch(
      `folders/${folderId}/${action}`,
      {},
      { headers: { 'Content-Type': 'application/merge-patch+json' } }
    )
    console.log(response)

    await Folder.update({
      id: folderId,
      state: response.data.state,
      actions: response.data.transitions
    })

    return Promise.resolve()
  },

  async getTplFolderPeriods ({ state }) {
    const response = await http.get(
      'folder_tpls/' + state.selectedFolderTemplateId + '/periods', {
        params: {
          fromdate: DateTime.utc().minus({ year: 1 }).startOf('day').toISO(),
          todate: DateTime.utc().endOf('day').toISO()
        }
      }
    )

    if (response.data.periods) {
      await FolderTemplate.update({
        id: state.selectedFolderTemplateId,
        periods: response.data.periods
      })
    }
  },

  async deleteFolder (context, folderId) {
    await http.delete('folders/' + folderId)
    Folder.delete(folderId)
    return Promise.resolve()
  }
}

export default {
  state,
  getters,
  mutations,
  actions
}
