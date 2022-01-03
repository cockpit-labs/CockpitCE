<template>
  <div class="page-questionnaire">
    <template v-if="folder && questionnaire">
      <QuestionnaireHeader v-if="folder.group">
        <template #action-left>
          <SButtonIcon v-show="Number(questionnaireNumber) > 1" icon="chevron-left" :label="$t('previousPage')" color="neutral" @click="goToPrevPage" />
        </template>
        {{ folder.group.name }} <SIcon name="caret-right" /> {{ questionnaire.label }}
        <template #action-right>
          <SButtonIcon icon="times" :label="$t('quit')" color="neutral" @click="confirmQuitQuestionnaire" />
        </template>
      </QuestionnaireHeader>

      <Questionnaire ref="questionnaire" :questionnaire="questionnaire" :readOnly="isReadOnly" />

      <div class="questionnaire-footer">
        <div class="left">
          <SButton v-if="!isReadOnly" ghost @click="saveFolder" :disabled="saving">
            <template v-if="saving">{{ $t('questionnaire.saving') }}</template>
            <template v-else>{{ $t('questionnaire.save') }}</template>
          </SButton>
          <div v-if="isDraft" class="delete" @click="$refs.deleteAlert.open()" :title="$t('questionnairesPage.alertDeleteQuestionnaireContent')">
            <SIcon name="trash" />
          </div>
        </div>

        <div class="right">
          <SButton v-if="nextQuestionnaire"  @click="goToNextQuestionnaire">{{ $t('next') }}</SButton>

          <SButton v-else @click="submitFolder" :disabled="submitting">
            <template v-if="submitting">{{ $t('questionnaire.submitting') }}</template>
            <template v-else>{{ $t('questionnaire.submit') }}</template>
          </SButton>
        </div>
      </div>

      <SAlert ref="deleteAlert" type="alert">
        <template #title>{{ $t('questionnairesPage.alertDeleteQuestionnaireTitle') }}</template>
        <template #content>
          {{ $t('questionnairesPage.alertDeleteQuestionnaireContent') }}
        </template>
        <template #actions>
          <SButton @click="$refs.deleteAlert.close()">{{ $t('no') }}</SButton>
          <SButton @click="deleteQuestionnaire">{{ $t('yes') }}</SButton>
        </template>
      </SAlert>

      <SAlert ref="requiredAlert" type="alert">
        <template #title>{{ $t('questionnairesPage.alertRequiredTitle') }}</template>
        <template #content>
          {{ $t('questionnairesPage.alertRequiredContent') }}
        </template>
        <template #actions>
          <SButton @click="$refs.requiredAlert.close()">{{ $t('close') }}</SButton>
        </template>
      </SAlert>

      <SAlert ref="confirmQuitAlert" type="alert">
        <template #title>{{ $t('questionnairesPage.alertQuitQuestionnaireTitle') }}</template>
        <template #content>
          {{ $t('questionnairesPage.alertQuitQuestionnaireContent') }}
        </template>
        <template #actions>
          <SButton @click="$refs.confirmQuitAlert.close()">{{ $t('cancel') }}</SButton>
          <SButton @click="quitQuestionnaire">{{ $t('quit') }}</SButton>
        </template>
      </SAlert>
    </template>
  </div>
</template>

<script>
import QuestionnaireHeader from '@/components/QuestionnaireHeader'
import Questionnaire from '@/components/Questionnaire'
import Folder from '@/models/Folder'

export default {
  components: {
    QuestionnaireHeader,
    Questionnaire
  },

  props: ['folderId', 'questionnaireNumber'],

  data () {
    return {
      saving: false,
      submitting: false
    }
  },

  computed: {
    folder () {
      if (this.folderId) {
        return Folder.query()
          .whereId(this.folderId)
          .with('group')
          .first()
      }
      return null
    },

    questionnaire () {
      if (this.folder && this.folder.questionnaires) {
        return this.folder.questionnaires[this.questionnaireNumber - 1]
      }
      return null
    },

    nextQuestionnaire () {
      const questionnaire = this.folder.questionnaires[this.questionnaireNumber]
      if (questionnaire) {
        return true
      }
      return false
    },

    prevQuestionnaire () {
      const questionnaire = this.folder.questionnaires[this.questionnaireNumber - 2]
      if (questionnaire) {
        return true
      }
      return false
    },

    isReadOnly () {
      return this.folder?.state !== 'DRAFT' &&
        this.folder?.state !== 'REVIEWED'
    },

    isDraft () {
      return this.folder?.state === 'DRAFT'
    },

    showActionButtons () {
      if (this.folder?.state === 'SUBMITTED' && this.folder.createdBy === this.$keycloak.userName) {
        return false
      }
      return true
    }
  },

  methods: {
    async saveFolder () {
      this.saving = true
      await this.$store.dispatch('saveFolder', this.folder)
      console.log('saved')
      this.saving = false
    },

    async submitFolder () {
      let questionNotAnswered = null
      let questionnaire = null

      if (!this.isReadOnly) {
        questionnaire = this.folder.questionnaires.find(questionnaire => {
          return questionnaire.blocks.find(block => {
            const question = block.questions.find(question => {
              return question.mandatory && question.answers.length === 0
            })
            if (question) {
              questionNotAnswered = question
              return true
            }
            return false
          })
        })
      }

      if (questionNotAnswered) {
        const questionnaireIndex = this.folder.questionnaires.findIndex(q => q.id === questionnaire?.id)

        const expectedQuestionnaireIndex = questionnaireIndex + 1
        if (Number(this.questionnaireNumber) !== expectedQuestionnaireIndex) {
          await this.$router.push({ name: 'questionnaire', params: { folderId: this.folderId, questionnaireNumber: expectedQuestionnaireIndex } })
        }

        const questionEl = document.getElementById(questionNotAnswered.id)
        if (questionEl) {
          questionEl.scrollIntoView({ behavior: 'smooth', block: 'center' })
          const questionComponent = this.$refs.questionnaire.$children.find(child => child.$vnode.key === questionEl.id)
          questionComponent.requiredExpanded = true
        }

        this.$refs.requiredAlert.open()
      } else {
        this.submitting = true
        try {
          await this.$store.dispatch('saveFolder', this.folder)
          await this.$store.dispatch('actionOnFolder', { action: 'validate', folderId: this.folder.id })
        } finally {
          this.submitting = false
        }
        this.$router.push({ name: 'home' })
      }
    },

    goToNextQuestionnaire () {
      const questionnaireNumber = Number(this.questionnaireNumber) + 1
      this.$router.push({ name: 'questionnaire', params: { folderId: this.folderId, questionnaireNumber } })
    },

    goToPrevQuestionnaire () {
      const questionnaireNumber = Number(this.questionnaireNumber) - 1
      this.$router.push({ name: 'questionnaire', params: { folderId: this.folderId, questionnaireNumber } })
    },

    goToPrevPage () {
      if (Number(this.questionnaireNumber) === 1) {
        this.$router.push({ name: 'home' })
      } else {
        this.goToPrevQuestionnaire()
      }
    },

    confirmQuitQuestionnaire () {
      if (!this.isReadOnly) {
        this.$refs.confirmQuitAlert.open()
      } else {
        this.$router.push({ name: 'home' })
      }
    },

    quitQuestionnaire () {
      this.$refs.confirmQuitAlert.close()
      this.$router.push({ name: 'home' })
    },

    async deleteQuestionnaire () {
      this.$refs.deleteAlert.close()
      await this.$store.dispatch('deleteFolder', this.folder.id)
      this.$router.push({ name: 'home' })
    }
  }
}
</script>

<style lang="scss" scoped>
.page-questionnaire {
  height: 100%;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
}

.questionnaire {
  flex: 1;
  margin-top: 32px;
  padding: 16px 16px 0;
  overflow-y: auto;
}

.questionnaire-header .title .s-icon {
  margin: 0 4px;
}

.questionnaire-footer {
  position: relative;
  width: 100vw;
  background-color: var(--color-n-000);
  padding: 16px 24px 32px 24px;
  box-shadow: 0 -2px 4px rgba($color: #000000, $alpha: 0.08);
  display: flex;
  box-sizing: border-box;

  .left {
    display: flex;
    gap: 8px;
  }

  .right {
    display: flex;
    margin-left: auto;
  }

  .right .s-button-text {
    margin-right: 4px;
  }
}

.delete {
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-n-400);
  padding: 0 16px;
  border-radius: 4px;
  transition: background-color 150ms linear, color 150ms linear;

  &:hover {
    color: var(--color-s-5-dark);
    background-color: var(--color-s-5-light);
  }
}

.desktop {
  .questionnaire {
    padding-top: 64px;
  }

  .right .s-button-text {
    margin-right: 32px;
  }
}
</style>
