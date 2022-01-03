<template>
  <div class="base-question-wrapper" v-if="triggerIsTrue">
    <div class="base-question">
      <div class="question-label">
        {{ question.label }}
        <div class="external-url" v-if="question.externalUrl">
          <a :href="question.externalUrl.url" target="_blank" rel="noopener noreferrer">{{ question.externalUrl.label }}</a> <SIcon name="external-link-alt" />
        </div>
      </div>
      <div class="question-required" v-if="isRequired" @click="requiredExpanded = !requiredExpanded" :title="$t('question.required')">
        <SIcon name="asterisk" fw />
        <span v-if="requiredExpanded">{{ $t('question.required') }}</span>
      </div>
      <div class="question-answer" v-if="question.writeRenderer.component !== 'none'">
        <component
          ref="questionComponent"
          :key="question.id"
          :is="getComponent(question.writeRenderer.component)"
          :question.sync="question"
          :readOnly="readOnly"
        />
      </div>
      <div class="question-options" v-if="hasOptions">
        <div class="option-buttons" v-if="!readOnly">
          <div class="option" v-if="question.hasComment">
              <SButtonText icon="comment" @click="showComment">{{ $t('question.comment') }}</SButtonText>
          </div>
          <div class="option" v-if="question.maxPhotos > 0">
              <SButtonText icon="camera" @click="showPhotos">{{ $t('question.photo') }}</SButtonText>
          </div>
        </div>
        <div class="option-components" v-if="optionCommentVisible || optionPhotosVisible">
          <OptionComment
            ref="optionCommentComponent"
            v-if="optionCommentVisible"
            v-model="question.comment"
            :placeholder="$t('placeholder.comment')"
            :readOnly="readOnly"
            @close="optionCommentVisible = false"

          />
          <OptionPhoto
            ref="optionPhotoComponent"
            v-if="optionPhotosVisible"
            v-model="question.photos"
            :max-photos="question.maxPhotos"
            :readOnly="readOnly"
            @close="optionPhotosVisible = false"
          />
        </div>
      </div>
    </div>

    <BaseQuestion
      v-for="childQuestion in question.children"
      :key="childQuestion.id"
      :question="childQuestion"
      :readOnly="readOnly"
    />
  </div>
</template>

<script>
import QuestionText from '@/components/questions/QuestionText'
import QuestionSelect from '@/components/questions/QuestionSelect'
import QuestionNumber from '@/components/questions/QuestionNumber'
import QuestionPhoto from '@/components/questions/QuestionPhoto'
import QuestionRange from '@/components/questions/QuestionRange'
import QuestionDatetime from '@/components/questions/QuestionDatetime'
import OptionComment from './OptionComment'
import OptionPhoto from './OptionPhoto'
import ExpressionLanguage from 'expression-language'
import Folder from '@/models/Folder'

export default {
  name: 'BaseQuestion',

  components: {
    QuestionText,
    QuestionSelect,
    QuestionNumber,
    QuestionPhoto,
    QuestionRange,
    QuestionDatetime,
    OptionComment,
    OptionPhoto
  },

  props: {
    question: {
      type: Object,
      required: true
    },
    readOnly: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      optionCommentVisible: Boolean(this.question.hasComment && this.question.comment),
      optionPhotosVisible: Boolean(this.question.photos.length > 0) &&
        this.question.writeRenderer.component !== 'photo',
      requiredExpanded: false
    }
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

    isRequired () {
      return this.question.mandatory
    },

    hasOptions () {
      if (this.readOnly) {
        return (this.question.hasComment === true ||
        this.question.maxPhotos > 0) && (this.optionCommentVisible || this.optionPhotosVisible)
      }
      return this.question.hasComment === true ||
        this.question.maxPhotos > 0
    },

    parent () {
      if (this.$parent && this.$parent.$options.name === 'BaseQuestion') {
        return this.$parent
      }
      return null
    },

    triggerIsTrue () {
      if (this.parent === null) {
        return true
      }

      const trigger = this.question.trigger || {}
      if (typeof trigger.expression === 'string') {
        const expressionLanguage = new ExpressionLanguage()

        const expressionParams = {
          value: this.parent.question.answers[0]?.value || null,
          rawValue: this.parent.question.answers[0]?.rawValue || null,
          values: this.parent.question.answers.map(a => a.value),
          rawValues: this.parent.question.answers.map(a => a.rawValues),
          count: this.parent.question.answers.length
        }

        try {
          const result = expressionLanguage.evaluate(trigger.expression, expressionParams)
          return typeof result === 'boolean' ? result : false
        } catch (error) {
          console.log(trigger.expression, error)
          return false
        }
      }

      return false
    }
  },

  methods: {
    getComponent (componentDbName) {
      switch (componentDbName) {
        case 'select':
          return 'QuestionSelect'
        case 'text':
          return 'QuestionText'
        case 'photo':
          return 'QuestionPhoto'
        case 'number':
          return 'QuestionNumber'
        case 'range':
          return 'QuestionRange'
        case 'dateTime':
          return 'QuestionDatetime'
        default:
          throw Error('unknown component')
      }
    },

    showComment () {
      this.optionCommentVisible = true
    },

    showPhotos () {
      this.optionPhotosVisible = true

      this.$nextTick(() => {
        if (!this.$refs.optionPhotoComponent?.collection.maxPhotoReached) {
          this.$refs.optionPhotoComponent.$refs.file.click()
        }
      })
    },

    resetQuestion () {
      if (this.$refs.questionComponent) {
        this.$refs.questionComponent.reset()
      }

      if (this.optionPhotosVisible) {
        this.$refs.optionPhotoComponent.reset()
      }

      if (this.optionCommentVisible) {
        this.$refs.optionCommentComponent.reset()
      }
    }
  },

  watch: {
    triggerIsTrue (newValue, oldValue) {
      if (oldValue === true && newValue === false) {
        this.resetQuestion()
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.base-question {
  @include font-stack;
  position: relative;
  width: 100%;
  max-width: 380px;
  margin: 0 auto 32px auto;
  display: grid;
  grid-template-columns: auto;
  grid-template-rows: auto;
  grid-template-areas: "label"
                       "answer"
                       "options";
  align-items: center;
  background-color: var(--color-n-000);
  border-radius: 8px;
  box-shadow: 0 3px 4px rgba($color: #000000, $alpha: 0.08);
  box-sizing: border-box;
}

.question-label {
  grid-area: label;
  padding: 16px 16px 24px 16px;
  align-self: flex-start;

  .external-url {
    margin-top: 16px;
    color: var(--color-p-500);

    a {
      color: var(--color-p-500);
    }
  }
}

.question-required {
  position: absolute;
  top: -9px;
  right: -4px;
  padding: 6px 10px;
  background-color: var(--color-s-1);
  border-radius: 20px;
  box-shadow: 0 2px 6px 1px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  color: var(--color-s-1-dark);
  cursor: default;

  &:hover {
    background-color: var(--color-s-1-light);
  }

  span {
    line-height: 1;
    margin-left: 2px;
  }
}

.question-answer {
  grid-area: answer;
  padding: 0 16px 24px 16px;
}

.question-options {
  grid-area: options;
  padding: 8px 16px;
  border-top: 1px solid var(--color-n-100);
}

.question-notes {
  padding: 8px 16px;
  border-top: 1px solid var(--color-n-100);
}

.option-buttons {
  display: flex;
}

.option {
  display: flex;
  flex: 1;
  align-items: center;
  justify-content: center;
}

.option-components {
  margin-top: 8px;

  > div {
    margin-bottom: 8px;
  }
}

.tablet, .desktop {
  .base-question {
    max-width: 1100px;
  }

  .question-label {
    padding: 24px;
  }

  .question-answer {
    padding: 8px 24px 32px;
  }

  .question-options {
    padding: 12px 24px;
  }

  .question-notes {
    padding: 8px 24px;
  }

  .option {
    margin: 0 4px;
  }
}
</style>
