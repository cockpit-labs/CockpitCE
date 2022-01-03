import { http } from '@/plugins/http'
import Group from '@/models/Group'
import GroupFolderTemplate from '@/models/GroupFolderTemplate'
import { intersection } from 'lodash'

const state = {
  selectedGroupId: null
}

const mutations = {
  setSelectedGroupId (state, groupId) {
    state.selectedGroupId = groupId
  },
  resetSelectedGroupId (state) {
    state.selectedGroupId = null
  }
}

const actions = {
  async getGroups (context) {
    const groups = await http.get('groups')

    await Group.create({ data: groups.data.map(group => transform(group)) })
    await GroupFolderTemplate.create({ data: groups.data.flatMap(group => getFolderTemplateIds(group)) })

    const groupsWithTargets = groups.data.filter(group => group.targets.length > 0)

    const groupsAncestors = groupsWithTargets.map(group => group.idPath.split('/'))

    const uniqueAncestorsIds = [...new Set(groupsAncestors.flat())]

    Group.update({
      where: (group) => {
        return uniqueAncestorsIds.includes(group.id)
      },
      data: { targetsAncestor: true }
    })

    const commonAncestors = intersection(...groupsAncestors)

    const rootId = commonAncestors[commonAncestors.length - 1]

    Group.update({
      where: rootId,
      data: { root: true, parentId: null }
    })

    if (!uniqueAncestorsIds.includes(context.state.selectedGroupId)) {
      context.commit('setSelectedGroupId', null)
    }

    if (context.state.selectedGroupId === null) {
      context.commit('setSelectedGroupId', rootId)
    }
  }
}

export default {
  state,
  mutations,
  actions
}

function transform (group) {
  return {
    id: group.id,
    name: group.label,
    parentId: group.parent?.substring(group.parent.lastIndexOf('/') + 1),
    attributes: group.attributes,
    icon: group.attributes.find(t => t.label === 'icon')?.value
  }
}

function getFolderTemplateIds (group) {
  let folderTemplateIds = null
  const createTargets = group.targets.filter(target => target.right === '/api/rights/CREATE')

  if (createTargets.length > 0) {
    folderTemplateIds = createTargets.map(target => {
      return {
        groupId: group.id,
        folderTemplateId: target.folderTpl.id
      }
    })
  }

  return folderTemplateIds
}
