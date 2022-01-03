<template>
  <ModalView ref="questionnaire">
    <template #title v-if="questionnaire">{{ questionnaire.label }}</template>
    <template #subtitle v-if="selectedFolder">
      {{ $t('answersPage.folderCreationDetails', {createdAt: formatDate(selectedFolder.createdAt), createdBy: selectedFolder.createdBy}) }}
    </template>

    <Questionnaire v-if="questionnaire" :questionnaire="questionnaire" :readOnly="true" />

    <template #footer>
      <div class="questionnaire-footer">
        <div class="pagination" v-if="selectedFolder && selectedFolder.questionnaires.length > 1">
          <SButton v-if="prevQuestionnaire" @click="goToPrevQuestionnaire">{{ $t('previous') }}</SButton>
          <SButton v-if="nextQuestionnaire"  @click="goToNextQuestionnaire">{{ $t('next') }}</SButton>
        </div>
        <div class="tools">
          <SButtonText icon="file-pdf" @click="downloadPdf">{{ $t('downloadPdf') }}</SButtonText>
          <SButtonText icon="paper-plane" @click="openSendByEmailModal">{{ $t('answersPage.sendByEmail') }}</SButtonText>
        </div>
      </div>
    </template>

    <ModalView ref="emailModal">
      <template #title>{{ $t('answersPage.sendByEmail') }}</template>
      <div class="recipients">
        <div class="label">{{ $t('answersPage.recipientsReport') }}</div>
        <UserSearch v-model="recipients" :max="5" />
      </div>
      <template #footer>
        <div v-if="sendingEmail" class="sending">{{ $t('answersPage.sendingEmail') }}</div>
        <SButton v-else @click="sendByEmail">{{ $t('answersPage.sendEmail') }}</SButton>
      </template>
    </ModalView>
  </ModalView>
</template>

<script>
import ModalView from '@/components/ModalView'
import Questionnaire from '@/components/Questionnaire'
import UserSearch from '@/components/UserSearch'
import { DateTime } from 'luxon'
import { http } from '@/plugins/http'

export default {
  components: {
    ModalView,
    Questionnaire,
    UserSearch
  },

  props: {
    selectedFolder: {
      type: Object
    }
  },

  data () {
    return {
      questionnaireNumber: 0,
      recipients: [],
      sendingEmail: false
    }
  },

  computed: {
    questionnaire () {
      if (this.selectedFolder && this.selectedFolder.questionnaires) {
        return this.selectedFolder.questionnaires[this.questionnaireNumber]
      }
      return null
    },

    nextQuestionnaire () {
      return this.selectedFolder.questionnaires[this.questionnaireNumber + 1] || false
    },

    prevQuestionnaire () {
      return this.selectedFolder.questionnaires[this.questionnaireNumber - 1] || false
    }
  },

  methods: {
    open () {
      this.$refs.questionnaire.open()
    },

    formatDate (datetime) {
      return DateTime.fromISO(datetime).toLocaleString(DateTime.DATETIME_SHORT)
    },

    goToPrevQuestionnaire () {
      this.questionnaireNumber = this.questionnaireNumber - 1
      this.$refs.questionnaire.scrollMainTop()
    },

    goToNextQuestionnaire () {
      this.questionnaireNumber = this.questionnaireNumber + 1
      this.$refs.questionnaire.scrollMainTop()
    },

    async downloadPdf () {
      const pdfWindow = window.open('', '_blank')
      pdfWindow.document.write(this.$t('pdfLoading'))

      try {
        const dataUrl = await this.getDataUrl()
        pdfWindow.location.href = dataUrl
      } catch (error) {
        pdfWindow.document.body.innerHTML = this.$t('alert.errorOccurred')
      }

      // const a = document.createElement('a')
      // a.style = 'display: none'
      // document.body.appendChild(a)
      // a.href = dataUrl
      // a.setAttribute('download', this.questionnaire.id)
      // a.click()
      // a.remove()
      // URL.revokeObjectURL(dataUrl)
    },

    async getDataUrl () {
      try {
        const response = await http.get('questionnaires/' + this.questionnaire.id + '/pdf', {
          responseType: 'blob'
        })
        const dataUrl = URL.createObjectURL(response.data)
        return Promise.resolve(dataUrl)
      } catch (error) {
        return Promise.reject(error)
      }
    },

    openSendByEmailModal () {
      this.$refs.emailModal.open()
    },

    async sendByEmail () {
      try {
        this.sendingEmail = true
        await http.get('questionnaires/' + this.questionnaire.id + '/sendpdf', {
          params: {
            recipients: this.recipients.map(r => r.id)
          }
        })
        this.$refs.emailModal.close()
        return Promise.resolve()
      } catch (error) {
        return Promise.reject(error)
      } finally {
        this.sendingEmail = false
        this.recipients = []
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.questionnaire-footer {
  display: flex;
  width: 100%;
  align-items: center;

  .tools {
    flex: 1;
    display: flex;
    gap: 8px;
  }

  .pagination {
    display: flex;

    > .s-button {
      margin: 0 8px;
    }
  }
}

.recipients {
  min-height: 300px;

  .label {
    margin-bottom: 16px;
  }
}
</style>
