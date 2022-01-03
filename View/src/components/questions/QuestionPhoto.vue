<template>
  <div class="answer">
    <template v-if="!readOnly">
      <input
        type="file"
        :id="'file-' + question.id"
        class="input-file"
        accept="image/*"
        capture
        ref="file"
        :disabled="maxPhotoReached || uploading"
        @change="handleFile"
      />
      <label :class="['label', uploading]" :for="'file-' + question.id">
        <template v-if="uploading">{{ $t('question.uploading') }}</template>
        <template v-else-if="maxPhotoReached">{{ $t('question.maxPhotosReached') }}</template>
        <template v-else>{{ $t('question.addPhoto') }}</template>
      </label>
    </template>
    <div class="gallery">
      <img
        v-for="(photo, i) in collection"
        :key="photo.id"
        :src="photo.img.src"
        :height="photo.img.height"
        @click="viewPhoto(i)"
      />

      <div class="no-data" v-if="readOnly && collection.length === 0">{{ $t('question.noPhoto') }}</div>
    </div>
    <PhotoViewer ref="viewer" :photoSrc="selectedPhotoIndex !== null ? collection[selectedPhotoIndex].img.src : null">
      <template #footer v-if="!readOnly">
        <SButton icon="pen" icon-position="left" @click="launchEditor()">{{ $t('question.annotatePhoto') }}</SButton>
        <SButton icon="trash" icon-position="left" @click="deletePhoto(selectedPhotoIndex)">{{ $t('question.deletePhoto') }}</SButton>
      </template>
    </PhotoViewer>
  </div>
</template>

<script>
import photos from '@/mixins/photos'
import PhotoViewer from '@/components/PhotoViewer'
import { isEmpty } from 'lodash'

export default {
  name: 'QuestionPhoto',

  mixins: [photos],

  components: {
    PhotoViewer
  },

  props: {
    question: Object,
    readOnly: Boolean
  },

  data () {
    return {
      max: this.question?.writeRenderer?.max || 3
    }
  },

  computed: {
    localValue () {
      if (isEmpty(this.question.answers)) {
        return []
      }
      return this.question.answers
    }
  },

  watch: {
    collection: function (newPhotos = []) {
      this.question.answers = newPhotos.map(v => {
        return { id: v.id, media: v.mediaId }
      })

      this.$emit('update:question', this.question)
    }
  }
}
</script>

<style lang="scss">
@import '@/plugins/stnl-drawing/stnl-drawing';

#stnlDrawing.opened {
  z-index: 1000;
}
</style>

<style lang="scss" scoped>
.answer {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.input-file {
  display: none;

  &:disabled + .label {
    pointer-events: none;
    background-color: var(--color-p-100);
  }
}

.label {
  display: block;
  width: fit-content;
  max-width: 200px;
  margin-bottom: 8px;
  padding: 13px 16px 12px 16px;
  background-color: var(--color-p-500);
  color: var(--color-n-000);
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
  font-size: 16px;
  transition: all 150ms ease-in;

  &:hover {
    background-color: var(--color-p-700);
  }

  &:active {
    box-shadow: none;
    transform: translate3d(0, 1px, 0);
  }

  &.uploading {
    pointer-events: none;
  }

  // &.selected {
  //   background-color: var(--color-p-500);
  //   color: var(--color-n-000);
  //   box-shadow: none;
  // }
}

.gallery {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;

  img {
    padding: 4px;
    border-radius: 4px;
  }
}
</style>
