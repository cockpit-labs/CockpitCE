<template>
  <div :class="['question-select', {'read-only': readOnly}]">
    <div class="display-button" v-if="display === 'button'">
      <div class="choice" v-for="option in options" :key="option.id">
        <img
          v-if="photos[option.id]"
          :src="photos[option.id].src"
          :height="photos[option.id].height"
          @click="viewPhoto(option.id)"
        >
        <button
          :class="{'selected': isSelected(option.id)}"
          @click="selectOption(option.id)"
          :disabled="readOnly"
        >
          {{ option.label }}
        </button>
      </div>
    </div>

    <div class="display-list" v-else>
      <template v-if="readOnly">
        <div class="user-answer" v-if="localValue.length > 0">
          <div class="label" v-for="(label, i) in selectedOptionLabels" :key="i">
            <SIcon name="check-square" />{{ label }}
          </div>
        </div>
        <div class="no-data" v-else>{{ $t('question.noAnswer') }}</div>
      </template>

      <SSelect
        v-else
        v-model="localValue"
        :options="options"
        :multiselect="multiselect"
        :placeholder="$t('placeholder.selectAnswer')"
        @input="setChoices"
      />
    </div>

    <PhotoViewer ref="viewer" :photoSrc="selectedPhotoIndex !== null ? photos[selectedPhotoIndex].src : null" />
  </div>
</template>

<script>
import questionUtils from '@/mixins/questions'
import { http } from '@/plugins/http'
import PhotoViewer from '@/components/PhotoViewer'

export default {
  name: 'QuestionSelect',

  mixins: [questionUtils],

  components: {
    PhotoViewer
  },

  data () {
    return {
      localValue: this.cleanSelectedOptions(this.getChoices()),
      photos: [],
      selectedPhotoIndex: null
    }
  },

  computed: {
    options () {
      const options = this.question.choices
      return options || []
    },

    multiselect () {
      const { multiselect = false } = this.question.writeRenderer
      return multiselect
    },

    display () {
      const { display = 'list' } = this.question.writeRenderer
      return display
    },

    selectedOptionLabels () {
      return this.options
        .filter(option => this.localValue.includes(option.id))
        .map(option => option.label)
    }
  },

  methods: {
    cleanSelectedOptions (selectedOptions) {
      if (Array.isArray(selectedOptions)) {
        return selectedOptions.map(option => option.id)
      }
      return []
    },

    isSelected (optionId) {
      if (Array.isArray(this.localValue)) {
        return this.localValue.indexOf(optionId) !== -1
      } else {
        return this.localValue === optionId
      }
    },

    selectOption (optionId) {
      if (this.multiselect) {
        if (!Array.isArray(this.localValue)) {
          this.localValue = this.localValue ? [this.localValue] : []
        }
        const index = this.localValue.indexOf(optionId)
        if (index !== -1) {
          this.$delete(this.localValue, index)
        } else {
          this.localValue.push(optionId)
        }
      } else {
        if (this.localValue === optionId) {
          this.localValue = null
        } else {
          this.localValue = optionId
        }
      }

      this.setChoices(this.localValue)
    },

    reset () {
      this.localValue = null
      this.setChoices(this.localValue)
    },

    async getPhotos () {
      const downloads = this.options
        .filter(option => option.media)
        .map(async option => {
          const id = option.id
          const iri = option.media
          const mediaId = iri.substring(iri.lastIndexOf('/') + 1)
          const blob = await this.download(mediaId)
          const imageUrl = URL.createObjectURL(blob)
          const img = new Image()
          img.src = imageUrl
          img.height = 190
          return Promise.resolve({ id, img })
        })
      Promise.all(downloads).then(values => {
        values.forEach(v => this.$set(this.photos, v.id, v.img))
      })
    },

    async download (id) {
      try {
        const response = await http.get('media_tpls/' + id + '/content', {
          responseType: 'blob'
        })
        return Promise.resolve(response.data)
      } catch (error) {
        return Promise.reject(error)
      }
    },

    viewPhoto (index) {
      this.selectedPhotoIndex = index
      this.$refs.viewer.open()
    }
  },

  created () {
    this.getPhotos()
  },

  beforeDestroy () {
    this.photos.forEach(photo => URL.revokeObjectURL(photo.src))
  }
}
</script>

<style lang="scss" scoped>
.display-list {
  .s-select {
    max-width: 280px;
    margin: 0 auto;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
  }

}

.display-list::v-deep .s-select > .selected-option {
  background-color: var(--color-n-000);
  border: 1px solid var(--color-n-200);
  border-radius: 4px;
  font-weight: $fw-regular;
  color: var(--color-n-600);
  // white-space: nowrap;
  // overflow: hidden;
  // text-overflow: ellipsis;
}

.display-list::v-deep .options {
  border-radius: 4px;
  box-sizing: border-box;
}

.display-list::v-deep .options .selected-option {
  border: 1px solid transparent;
  border-radius: 4px 4px 0 0;
  font-weight: $fw-regular;
}

.display-list .user-answer {
  display: flex;
  flex-direction: column;
  width: fit-content;
  margin: 0 auto;
  max-width: 280px;
}

.display-button {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 16px;

  .choice {
    display: flex;
    flex-direction: column;
    align-items: center;

    img {
      cursor: zoom-in;
      max-height: 190px;
    }
  }

  button {
    @include font-stack;
    min-width: 100px;
    margin: 8px;
    appearance: none;
    padding: 13px 16px 12px 16px;
    background-color: var(--color-n-000);
    border: 1px solid var(--color-n-200);
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    font-size: 16px;
    color: var(--color-n-600);

    &.selected {
      background-color: var(--color-p-500);
      color: var(--color-n-000);
      box-shadow: none;
    }
  }
}

.read-only button {
  box-shadow: none;

  &.selected {
    background-color: var(--color-n-500);
    border-color: var(--color-n-500);
  }
}

.read-only .label {
  display: flex;
  align-items: center;
  padding: 8px 16px 8px 8px;
  margin-bottom: 4px;
  background-color: var(--color-n-000);
  border: 1px solid var(--color-n-200);
  border-radius: 4px;
  color: var(--color-n-600);

  .s-icon {
    margin-right: 16px;
    font-size: 24px;
  }
}

.no-data {
  text-align: center;
}
</style>
