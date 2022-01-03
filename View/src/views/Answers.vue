<template>
  <div class="content">
    <portal to="filters">
        <PageFilters ref="answersPageFilters" @search="getFoldersAnswered" @loading-start="loading = true" />
    </portal>

    <div class="answers">
      <div v-show="loading" class="loading">{{ $t('loading') }}</div>
      <div v-show="noData" class="loading">{{ $t('answersPage.noData') }}</div>

      <SListCard @click="selectFolder(folder)" v-for="folder in folders" :key="folder.id">
        <template #title>{{ folder.label }}</template>
        <template #body>
          {{ $t('answersPage.folderCreationDetails', {createdAt: formatDate(folder.createdAt), createdBy: folder.createdBy}) }}
        </template>
        <template #action-label>{{ $t('answersPage.viewFolderQuestionnaires') }}</template>
      </SListCard>
    </div>

    <QuestionnaireReadModal ref="questionnaire" :selectedFolder="selectedFolder" />
  </div>
</template>

<script>
import PageFilters from '@/components/PageFilters'
import QuestionnaireReadModal from '@/components/QuestionnaireReadModal'
import { http } from '@/plugins/http'
import qs from 'qs'
import { DateTime } from 'luxon'
import FiltersUtils from '@/mixins/filters'

export default {
  components: {
    PageFilters,
    QuestionnaireReadModal
  },

  mixins: [FiltersUtils],

  data () {
    return {
      loading: false,
      folders: [],
      selectedFolder: null
    }
  },

  computed: {
    noData () {
      return this.filtersAreFilled &&
        !this.loading &&
        this.folders.length === 0
    }
  },

  methods: {
    async getFoldersAnswered () {
      this.loading = true
      this.folders = []

      const response = await http.get('folders', {
        params: {
          state: 'VALIDATED',
          appliedTo: this.selectedGroupId,
          'folderTpl.id': this.selectedTplFolderId || undefined,
          createdAt: {
            after: this.dateStart || undefined,
            before: this.dateEnd || undefined
          }
        },
        paramsSerializer: params => qs.stringify(params, { arrayFormat: 'brackets' })
      })

      this.folders = response.data.sort((a, b) => a.createdAt < b.createdAt ? -1 : 1)
      this.loading = false
    },

    formatDate (datetime) {
      return DateTime.fromISO(datetime).toLocaleString(DateTime.DATETIME_SHORT)
    },

    selectFolder (folder) {
      this.selectedFolder = folder
      this.$refs.questionnaire.open()
    }
  },

  async mounted () {
    // portal-vue caveat
    await this.$nextTick()
    await this.$nextTick()

    if (this.filtersAreFilled) {
      this.getFoldersAnswered()
    }
  }
}
</script>

<style lang="scss" scoped>
.loading {
  text-align: center;
  padding-top: 80px;
}

.answers {
  padding-bottom: 80px;
}

.s-list-card {
  margin-left: auto;
  margin-right: auto;
  margin-bottom: 16px;
}
</style>
