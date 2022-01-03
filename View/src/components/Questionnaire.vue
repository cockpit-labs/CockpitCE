<template>
  <div class="questionnaire">
    <div class="wrapper">
      <div class="questionnaire-description" v-if="questionnaire.description">
        {{ questionnaire.description }}
      </div>

      <div class="block" v-for="block in questionnaireSorted" :key="block.id">
        <div class="block-header">
          <div class="title">{{ block.label }}</div>
          <div class="description" >{{ block.description }}</div>
        </div>

        <BaseQuestion
          v-for="question in block.questions"
          :key="question.id"
          :question="question"
          :readOnly="readOnly"
          :id="question.id"
        />
      </div>
    </div>
  </div>
</template>

<script>
import BaseQuestion from '@/components/questions/BaseQuestion'
import Folder from '@/models/Folder'

export default {
  props: {
    questionnaire: {
      type: Object,
      required: true
    },
    readOnly: {
      type: Boolean,
      default: false
    }
  },

  components: {
    BaseQuestion
  },

  computed: {
    folder () {
      if (this.$route.params.folderId) {
        return Folder.query()
          .whereId(this.$route.params.folderId)
          .first()
      }
      return null
    },

    questionnaireSorted () {
      return this.questionnaire.blocks.map(block => {
        block.questions = [...block.questions].sort((a, b) => {
          return a.position - b.position
        })

        return block
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.wrapper {
  max-width: 700px;
  margin: 0 auto;
}

.questionnaire-description {
  margin-bottom: 72px;
  text-align: center;
}

.block {
  margin-bottom: 80px;
}

.block-header {
  margin-bottom: 32px;
  text-align: center;
}

.block-header .title {
  color: var(--color-n-600);
  text-transform: uppercase;
}

.block-header .description {
  margin-top: 8px;
  color: var(--color-n-500);
}
</style>
