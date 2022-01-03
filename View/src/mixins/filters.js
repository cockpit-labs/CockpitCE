import Group from '@/models/Group'

export default {
  computed: {
    selectedGroupId () {
      return this.$store.state.groups.selectedGroupId
    },

    selectedGroup () {
      if (this.selectedGroupId) {
        return Group
          .query()
          .whereId(this.selectedGroupId)
          .with(['folderTemplates', 'children'])
          .first()
      }
      return null
    },

    selectedTplFolderId () {
      return this.$store.state.folders.selectedFolderTemplateId
    },

    selectedQuestionnaireId () {
      return this.$store.state.folders.selectedQuestionnaireId
    },

    dateStart () {
      return this.$store.state.periodSelectorValue.dateStart
        ? this.$store.state.periodSelectorValue.dateStart.toISO() : null
    },

    dateEnd () {
      return this.$store.state.periodSelectorValue.dateEnd
        ? this.$store.state.periodSelectorValue.dateEnd.endOf('day').toISO() : null
    },

    filtersAreFilled () {
      return (this.dateStart !== null) &&
        (this.dateEnd !== null) &&
        (this.selectedGroupId !== null) &&
        (this.selectedTplFolderId !== null)
    }
  }
}
