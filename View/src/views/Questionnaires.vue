<template>
  <div class="content">
    <portal to="filters">
      <component :is="targetSelector" :loading="isFolderDataLoading" />
    </portal>

    <div class="folders" ref="folders">
      <template v-if="isFolderDataLoading === false && selectedGroupId">
        <SListCard
          v-for="draft in draftFolders"
          :key="draft.id"
          @click="goToQuestionnaire(draft)"
          :list="draft.questionnaires"
        >
          <template #title>{{draft.label}}</template>
          <template #body>{{draft.description}}</template>
          <template #list>
            <div
              v-for="(questionnaire, i) in draft.questionnaires"
              :key="questionnaire.id"
              class="questionnaire-link"
              @click="goToQuestionnaire(draft, i + 1)"
            >
              <span>{{ questionnaire.label }}</span>
              <SIcon name="angle-right" />
            </div>
          </template>
          <template #footer>{{ $tc('questionnairesPage.remainingDays', remainingDays(draft.availableUntil)) }}</template>
          <template #action-label>{{ $t('questionnairesPage.continue') }}</template>
        </SListCard>

        <SListCard
          v-for="folder in availableFolderTemplates"
          :key="folder.id"
          :list="folder.questionnaires"
          @click="instantiateFolder(selectedGroup.id, folder.id)"
        >
          <template #title>{{folder.label}}</template>
          <template #body>{{folder.description}}</template>
          <template #list>
            <div
              v-for="(questionnaire, i) in folder.questionnaires"
              :key="questionnaire.id"
              class="questionnaire-link"
              @click="instantiateFolder(selectedGroup.id, folder.id, i + 1)"
            >
              <span>{{ questionnaire.label }}</span>
              <SIcon name="angle-right" />
            </div>
          </template>
          <template #footer>{{ $tc('questionnairesPage.remainingDays', remainingDays(folder.availableUntil)) }}</template>
          <template #action-label>{{ $t('questionnairesPage.start') }}</template>
        </SListCard>
      </template>
    </div>

    <div
      v-if="isFolderDataLoading === false && availableFolderTemplates.length === 0 && draftFolders.length === 0"
      class="no-data"
    >
      {{ $t('questionnairesPage.noData') }}
    </div>

    <div class="no-data" v-if="isFolderDataLoading">
      {{ $t('loading') }}
    </div>

  </div>
</template>

<script>
import { DateTime } from 'luxon'
import TargetSelector from '@/components/TargetSelector'
import TargetSelectorTile from '@/components/TargetSelectorTile'
import Group from '@/models/Group'
import CardMini from '@/components/CardMini'

export default {
  name: 'questionnaires',

  components: {
    TargetSelector,
    TargetSelectorTile,
    CardMini
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

    availableFolderTemplates () {
      if (this.selectedGroup) {
        return this.selectedGroup.folderTemplates
      }
      return []
    },

    draftFolders () {
      if (this.selectedGroupId) {
        return this.$store.getters.draftFolders.filter(folder => folder.groupId === this.selectedGroupId)
      }
      return []
    },

    targetSelectorExpanded () {
      return this.selectedGroupId === null || (this.$mq !== 'mobile' && this.$mq !== 'tablet')
    }
  },

  methods: {
    remainingDays (dateString) {
      const diff = DateTime.fromISO(dateString).diffNow().as('days')
      return diff < 1 ? 1 : Math.ceil(diff)
    },

    goToQuestionnaire (draftFolder, questionnaireNumber = 1) {
      this.$router.push({
        name: 'questionnaire',
        params: { folderId: draftFolder.id, questionnaireNumber }
      })
    },

    async instantiateFolder (groupId, folderTemplateId, goToQuestionnaire = 1) {
      const newFolder = await this.$store.dispatch('instantiateFolder', { groupId, folderTemplateId })
      console.log(newFolder)

      this.$router.push({
        name: 'questionnaire',
        params: { folderId: newFolder.id, questionnaireNumber: goToQuestionnaire }
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.s-list-card, .card-mini {
  margin-left: auto;
  margin-right: auto;
  margin-bottom: 16px;
}

.questionnaire-link {
  display: flex;
  align-items: center;
  color: var(--color-p-500);
  font-size: 16px;
  font-weight: $fw-semibold;
  padding: 8px 16px;
  text-decoration: none;
  border-radius: 8px;

  &:hover {
    color: var(--color-p-900);
    background-color: var(--color-p-100);
  }

  .s-icon {
    margin-left: auto;
    margin-right: 8px;
    transition: margin-right 150ms ease-in;
  }

  &:hover .s-icon {
    margin-right: 4px;
  }
}

.no-data {
  text-align: center;
  padding-top: 80px;
}
</style>
