<template>
  <div class="content">
    <portal to="filters" :disabled="$mq === 'desktop'">
      <div class="welcome">{{ $t('homePage.hello') }} {{ $keycloak.tokenParsed.given_name }}</div>
    </portal>

    <!-- En cours -->
    <div class="drafts" v-if="draftFolders.length > 0">
      <div class="title">{{ $t('homePage.inProgress') }}</div>
      <SListCard
        v-for="draft in draftFolders"
        :key="draft.id"
        @click="goToQuestionnaire(draft)"
        :list="draft.questionnaires"
      >
        <template #title>{{draft.label}} • {{draft.group.name}}</template>
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
    </div>

    <!-- Aujourd'hui -->
    <div class="today" v-if="isFolderDataLoading === false && todayFolderTemplates.length > 0">
      <div class="title">{{ $t('homePage.today') }}</div>
      <div class="templates">
        <template v-for="template in todayFolderTemplates">
          <SListCard
            v-for="group in template.groups"
            :key="template.id +'/'+ group.id"
            :list="template.questionnaires"
            @click="instantiateFolder(group.id, template.id)"
          >
            <template #title>{{template.label}} • {{group.name}}</template>
            <template #body>{{template.description}}</template>
            <template #list>
              <div
                v-for="(questionnaire, i) in template.questionnaires"
                :key="questionnaire.id"
                class="questionnaire-link"
                @click="instantiateFolder(group.id, template.id, i + 1)"
              >
                <span>{{ questionnaire.label }}</span>
                <SIcon name="angle-right" />
              </div>
            </template>
            <template #footer>{{ $tc('questionnairesPage.remainingDays', remainingDays(template.availableUntil)) }}</template>
            <template #action-label>{{ $t('questionnairesPage.start') }}</template>
          </SListCard>
        </template>
      </div>
    </div>

    <div
      v-if="noData"
      class="no-template"
    >
      {{ $t('homePage.noQuestionnaireToday') }}
    </div>

    <div class="no-template" v-if="isFolderDataLoading">
      {{ $t('loading') }}
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import { DateTime } from 'luxon'

export default {
  name: 'home',

  data () {
    return {
      selectedValidation: null,
      interval: null
    }
  },

  computed: {
    ...mapGetters([
      'isFolderDataLoading',
      'draftFolders',
      'todayFolderTemplates'
    ]),

    noData () {
      return this.isFolderDataLoading === false &&
        this.todayFolderTemplates.length === 0 &&
        this.draftFolders.length === 0
    }
  },

  methods: {
    async instantiateFolder (groupId, folderTemplateId, goToQuestionnaire = 1) {
      const newFolder = await this.$store.dispatch('instantiateFolder', { groupId, folderTemplateId })
      console.log(newFolder)

      this.$router.push({
        name: 'questionnaire',
        params: { folderId: newFolder.id, questionnaireNumber: goToQuestionnaire }
      })
    },

    remainingDays (dateString) {
      const diff = DateTime.fromISO(dateString).diffNow().as('days')
      return diff < 1 ? 1 : Math.ceil(diff)
    },

    goToQuestionnaire (folder, questionnaireNumber = 1) {
      this.$router.push({
        name: 'questionnaire',
        params: { folderId: folder.id, questionnaireNumber }
      })
    }
  },

  mounted () {
    this.interval = setInterval(() => {
      this.$store.dispatch('getDraftFolders')
    }, 30000)
  },

  destroyed () {
    clearInterval(this.interval)
  }
}
</script>

<style lang="scss" scoped>
.welcome {
  text-align: center;
}

.title {
  font-size: 18px;
  font-weight: 800;
  color: var(--color-n-700);
  margin-bottom: 8px;
}

.drafts {
  margin-bottom: 40px;
}

.s-list-card {
  margin-bottom: 16px;
}

.no-template {
  text-align: center;
  padding-top: 80px;
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

.desktop {
  .content {
    min-width: 480px;
    justify-self: center;
  }

  .welcome {
    margin-bottom: 40px;
  }
}
</style>
