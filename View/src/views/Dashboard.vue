<template>
  <div class="content">
    <portal to="filters">
        <PageFilters ref="dashboardPageFilters" :selectQuestionnaire="true" @search="getStats" @loading-start="setLoaders" />
    </portal>

    <div class="charts">
      <div class="chart">
        <div class="title">{{ $t('dashboardPage.benchmarkChart') }}</div>
        <HorizontalBar :loading="loadingBenchmark" :dataset="benchmarkDataset" @click="showFoldersTarget" />
      </div>

      <div class="chart">
        <div class="title">{{ $t('dashboardPage.scoresByThemeChart') }}</div>
        <HorizontalBar :loading="loadingThemes" :dataset="themesDataset" :withAverage="false" />
      </div>

      <div class="chart">
        <div class="title">{{ $t('dashboardPage.performanceChart') }}</div>
        <LineChart :loading="loadingPerformance" :dataset="performanceDataset" @click="showFoldersByDate" />
      </div>

      <div class="chart" v-show="selectedQuestionnaireId === null || selectedQuestionnaireId === 0">
        <div class="title">{{ $t('dashboardPage.progressChart') }}</div>
        <HorizontalBar
          :loading="loadingProgress"
          :dataset="progressDataset"
          :withAverage="false"
          format="percent"
          @click="showFoldersTarget"
         />
      </div>
    </div>

    <ModalView ref="folderList" :contentLoading="loadingFolders">
      <template #title>{{ $t('dashboardPage.foldersModalTitle') }}</template>
      <SListCard @click="selectFolder(folder)" v-for="folder in folders" :key="folder.id">
        <template #title>{{ folder.label }}</template>
        <template #body>
          {{ $t('answersPage.folderCreationDetails', {createdAt: formatDate(folder.createdAt), createdBy: folder.createdBy}) }}
        </template>
        <template #action-label>{{ $t('answersPage.viewFolderQuestionnaires') }}</template>
      </SListCard>
    </ModalView>

    <QuestionnaireReadModal ref="questionnaire" :selectedFolder="selectedFolder" />
  </div>
</template>

<script>
import { http } from '@/plugins/http'
import PageFilters from '@/components/PageFilters'
import Group from '@/models/Group'
import qs from 'qs'
import HorizontalBar from '@/components/charts/HorizontalBar'
import LineChart from '@/components/charts/LineChart'
import collect from 'collect.js'
import { DateTime } from 'luxon'
import ModalView from '@/components/ModalView'
import FiltersUtils from '@/mixins/filters'
import QuestionnaireReadModal from '@/components/QuestionnaireReadModal'

export default {
  components: {
    PageFilters,
    HorizontalBar,
    LineChart,
    ModalView,
    QuestionnaireReadModal
  },

  mixins: [FiltersUtils],

  data () {
    return {
      benchmarkDataset: [],
      themesDataset: [],
      performanceDataset: {
        datasets: [{
          data: []
        }]
      },
      progressDataset: [],
      loadingBenchmark: false,
      loadingThemes: false,
      loadingPerformance: false,
      loadingProgress: false,
      loadingFolders: false,
      folders: [],
      selectedFolder: null
    }
  },

  computed: {
    templateGroupIds () {
      return this.$store.getters.selectedFolderTemplate?.groups.map(group => group.id)
    }
  },

  methods: {
    getAllChildrenId (groupId) {
      const targetWithChildren = Group.query().whereId(groupId).with('children').first()

      if (targetWithChildren.children.length > 0) {
        return targetWithChildren.children.flatMap(child => this.getAllChildrenId(child.id))
      }

      return [groupId]
    },

    async getStats () {
      this.loadingBenchmark = true
      this.loadingThemes = true
      this.loadingPerformance = true
      this.loadingProgress = true

      let directChildren = this.selectedGroup.children

      if (directChildren.length === 0) {
        directChildren = [this.selectedGroup]
      }

      const allChildrenIds = this.getAllChildrenId(this.selectedGroupId)

      let response = await http.get('folders/stats', {
        params: {
          state: 'VALIDATED',
          appliedTo: allChildrenIds,
          'folderTpl.id': this.selectedTplFolderId || undefined,
          updatedAt: {
            after: this.dateStart || undefined,
            before: this.dateEnd || undefined
          }
        },
        paramsSerializer: params => qs.stringify(params, { arrayFormat: 'brackets' })
      })

      const collection = collect(response.data)

      // Benchmark
      const dataset = directChildren.map(child => {
        const folders = collection.filter(folder => {
          return folder.parentGroups.split('/').includes(child.id)
        })

        let score = 0

        if (this.selectedQuestionnaireId) {
          const questionnaires = folders.flatMap(folder => {
            return folder.questionnaires.filter(q => q.questionnaireTplId === this.selectedQuestionnaireId)
          })
          score = questionnaires.avg('score')
        } else {
          score = folders.avg('score')
        }

        score = isNaN(score) ? 0 : score

        return { id: child.id, label: child.name, value: score }
      })

      this.benchmarkDataset = dataset
      this.loadingBenchmark = false

      // Themes
      const questionnaires = collection.flatMap(folder => {
        return this.selectedQuestionnaireId
          ? folder.questionnaires.filter(q => q.questionnaireTplId === this.selectedQuestionnaireId)
          : folder.questionnaires
      })
      const blocks = questionnaires.map(q => q.blocks).flatten(1)
      const blocksGrouped = blocks.groupBy('blockTplId').map((block, index) => {
        return { id: index, score: block.avg('score'), label: block.get(0).label }
      })

      const themesDataset = []
      blocksGrouped.each((block) => {
        themesDataset.push({ id: block.id, label: block.label, value: isNaN(block.score) ? 0 : block.score })
      })

      this.themesDataset = themesDataset
      this.loadingThemes = false

      // Performance
      const performanceDataset = []

      collection.map(folder => {
        if (this.selectedQuestionnaireId) {
          const q = folder.questionnaires.find(q => q.questionnaireTplId === this.selectedQuestionnaireId)
          return { date: DateTime.fromISO(folder.updatedAt).toISODate(), score: q?.score || 0 }
        }
        return { date: DateTime.fromISO(folder.updatedAt).toISODate(), score: folder.score }
      })
        .groupBy('date')
        .sortKeys()
        .each((date, key) => {
          performanceDataset.push({ x: key, y: date.avg('score') })
        })
        .all()

      this.performanceDataset = {
        datasets: [
          {
            label: 'Score',
            backgroundColor: cssvar('--color-p-100'),
            borderColor: cssvar('--color-p-500'),
            borderWidth: 5,
            data: performanceDataset
          }
        ]
      }
      this.loadingPerformance = false

      // Avancement
      response = await http.get('folder_tpls/' + this.selectedTplFolderId + '/expectation', {
        params: {
          fromdate: this.dateStart,
          todate: this.dateEnd
        }
      })

      const tplFolderWithExpectation = response.data

      const progressDataset = directChildren.map(child => {
        const folders = collection.filter(folder => {
          return folder.parentGroups.split('/').includes(child.id)
        })

        const foldersCount = folders.count()
        const concernedTargetsId = this.getAllChildrenId(child.id).filter(t => {
          return this.templateGroupIds.includes(t)
        })

        let value = foldersCount / (tplFolderWithExpectation.expectedFolders * concernedTargetsId.length)

        if (isNaN(value)) {
          value = 0
        }

        if (value > 1) {
          value = 1
        }

        return { id: child.id, label: child.name, value }
      })

      this.progressDataset = progressDataset
      this.loadingProgress = false
    },

    setLoaders () {
      this.loadingBenchmark = true
      this.loadingThemes = true
      this.loadingPerformance = true
      this.loadingProgress = true
    },

    showFoldersTarget (targetId) {
      this.getFoldersAnswered({ targetId, dateStart: this.dateStart, dateEnd: this.dateEnd })
      this.$refs.folderList.open()
    },

    showFoldersByDate (clickedGraphNode) {
      const dateStart = DateTime.fromISO(clickedGraphNode.x).toISO()
      const dateEnd = DateTime.fromISO(clickedGraphNode.x).endOf('day').toISO()

      this.getFoldersAnswered({ targetId: this.selectedTargetId, dateStart, dateEnd })
      this.$refs.folderList.open()
    },

    async getFoldersAnswered ({ targetId, dateStart, dateEnd }) {
      this.loadingFolders = true
      this.folders = []

      const response = await http.get('folders', {
        params: {
          state: 'VALIDATED',
          parentGroups: targetId,
          'folderTpl.id': this.selectedTplFolderId || undefined,
          updatedAt: {
            after: dateStart,
            before: dateEnd
          }
        },
        paramsSerializer: params => qs.stringify(params, { arrayFormat: 'brackets' })
      })

      this.folders = response.data
      this.loadingFolders = false
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
      this.getStats()
    }
  }
}

function cssvar (name) {
  return getComputedStyle(document.documentElement).getPropertyValue(name)
}
</script>

<style lang="scss" scoped>
.charts {
  max-width: 900px;
  margin: 0 auto;
}

.chart {
  margin-bottom: 88px;
  background-color: var(--color-n-000);
  padding: 16px 8px;
  border-radius: 12px;
  box-shadow: 0 3px 4px rgba($color: #000000, $alpha: 0.12);
}

.chart .title {
  text-align: center;
  font-size: 24px;
  margin-bottom: 40px;
}

.s-list-card {
  margin-bottom: 16px;
}

.desktop {
  .chart {
    margin-bottom: 88px;
    padding: 40px 56px 56px;
  }

  .chart .title {
    margin-bottom: 56px;
  }
}
</style>
