<template>
  <div class="page-filters">
    <div class="form">
      <component :is="targetSelector" :loading="isFolderDataLoading" />
      <SSelect
        v-model="localSelectedTplFolderId"
        :loading="isFolderDataLoading"
        :options="templateFolders"
        :placeholder="$t('placeholder.selectQuestionnaire')"
        :disabled="templateFolders.length === 0"
      />
      <SSelect
        v-show="selectQuestionnaire && folderQuestionnaires"
        v-model="localSelectedQuestionnaireId"
        :options="folderQuestionnaires"
        :placeholder="$t('placeholder.selectQuestionnaire')"
      />
      <PeriodSelector
        v-show="selectedTplFolderPeriods"
        v-model="periodSelectorValue"
        :loading="loadingPeriods"
        :periods="selectedTplFolderPeriods"
      />
    </div>
  </div>
</template>

<script>
import TargetSelector from '@/components/TargetSelector'
import TargetSelectorTile from '@/components/TargetSelectorTile'
import Group from '@/models/Group'
import FolderTemplate from '@/models/FolderTemplate'
import PeriodSelector from '@/components/PeriodSelector'
import { DateTime } from 'luxon'

export default {
  components: {
    TargetSelector,
    TargetSelectorTile,
    PeriodSelector
  },

  props: ['selectQuestionnaire'],

  data () {
    return {
      periodSelectorValue: this.$store.state.periodSelectorValue,
      loadingPeriods: false,
      localSelectedTplFolderId: null,
      localSelectedQuestionnaireId: null
    }
  },

  computed: {
    isFolderDataLoading () {
      return this.$store.getters.isFolderDataLoading
    },

    targetSelector () {
      return this.$store.state.targetListWithTiles ? TargetSelectorTile : TargetSelector
    },

    selectedGroupId () {
      return this.$store.state.groups.selectedGroupId
    },

    selectedGroup () {
      if (this.selectedGroupId) {
        return Group
          .query()
          .whereId(this.selectedGroupId)
          .with('folderTemplates')
          .first()
      }
      return null
    },

    selectedTplFolderId () {
      return this.$store.state.folders.selectedFolderTemplateId
    },

    selectedTplFolder () {
      return this.$store.getters.selectedFolderTemplate
    },

    selectedTplFolderPeriods () {
      return this.selectedTplFolder ? this.selectedTplFolder.periods : null
    },

    templateFolders () {
      return FolderTemplate.all()
    },

    folderQuestionnaires () {
      if (this.selectedTplFolder?.questionnaires.length > 1) {
        return [{ id: 0, label: this.$t('placeholder.allQuestionnaires') }, ...this.selectedTplFolder.questionnaires]
      }
      return null
    }
  },

  methods: {
    async getTplFolderPeriods () {
      if (this.selectedTplFolder.periods === null) {
        this.loadingPeriods = true
        this.$emit('loading-start')
        await this.$store.dispatch('getTplFolderPeriods')

        if (this.selectedTplFolder.periods.length > 0) {
          const { start, end } = [...this.selectedTplFolder.periods].pop()

          this.periodSelectorValue.selectedPeriod = this.selectedTplFolder.periods.length - 1
          this.periodSelectorValue.dateStart = DateTime.fromISO(start)
          this.periodSelectorValue.dateEnd = DateTime.fromISO(end)
        }
        this.loadingPeriods = false
        this.$emit('loading-end')
      }
      this.search()
    },

    search () {
      if (this.periodSelectorValue.dateStart instanceof DateTime &&
      this.periodSelectorValue.dateEnd instanceof DateTime &&
      this.selectedGroupId &&
      this.selectedTplFolderId) {
        this.$emit('search', {
          groupId: this.selectedTargetId,
          folderTplId: this.selectedTplFolderId,
          dateStart: this.periodSelectorValue.dateStart,
          dateEnd: this.periodSelectorValue.dateEnd
        })
      }
    }
  },

  watch: {
    periodSelectorValue: function (newValue) {
      this.$store.commit('setPeriodSelectorValue', newValue)
      this.search()
    },

    localSelectedTplFolderId: function (newValue) {
      if (newValue !== null) {
        this.$store.commit('setSelectedFolderTemplateId', newValue[0])
        this.getTplFolderPeriods()
      }
    },

    localSelectedQuestionnaireId: function (newValue) {
      if (newValue === null) {
        this.$store.commit('setSelectedQuestionnaireId', null)
      } else {
        this.$store.commit('setSelectedQuestionnaireId', newValue[0])
        this.search()
      }
    },

    folderQuestionnaires: function (newValue) {
      if (newValue === null) {
        this.localSelectedQuestionnaireId = null
      } else {
        this.localSelectedQuestionnaireId = [0]
      }
    },

    selectedGroupId: function (newValue) {
      this.search()
    }
  },

  created () {
    const tplIds = this.templateFolders.map(t => t.id)
    if (tplIds.includes(this.selectedTplFolderId)) {
      this.localSelectedTplFolderId = [this.selectedTplFolderId]
      this.getTplFolderPeriods()
    } else {
      this.$store.commit('setSelectedFolderTemplateId', null)
      this.$store.commit('setSelectedQuestionnaireId', null)
    }
  }
}
</script>

<style lang="scss" scoped>
.filters {
  padding: 24px 16px;
}

.form {
  display: grid;
  grid-template-columns: 1fr;
  grid-auto-rows: min-content;
  gap: 8px;
}

.period-selector {
  position: fixed;
  bottom: 24px;
  left: 22px;
  right: 22px;
  z-index: 1;
}

.search-btn {
  margin: 32px auto 0;
}

.desktop {
  .form {
    gap: 16px;
  }

  .period-selector {
    position: unset;
    z-index: auto;
  }
}
</style>
