<template>
  <div :class="['option-photo', {'read-only': readOnly}]">
    <div class="photo-gallery">
      <template v-if="!readOnly">
        <input
          type="file"
          class="input-file"
          accept="image/*"
          capture
          ref="file"
          :disabled="maxPhotoReached"
          @change="handleFile"
        />
        <SButtonIcon icon="camera" color="neutral" :label="$t('question.addPhoto')" @click="$refs.file.click()" />
      </template>
      <div class="gallery">
        <img
          v-for="(photo, i) in collection"
          :key="photo.id"
          :src="photo.img.src"
          :height="photo.img.height"
          @click="viewPhoto(i)"
        />
      </div>
    </div>
    <div v-if="!readOnly" class="close" @click="close">
      <SIcon name="times" />
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

export default {
  name: 'OptionPhoto',

  mixins: [photos],

  components: {
    PhotoViewer
  },

  props: {
    value: Array,
    maxPhotos: [Number, String],
    readOnly: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      localValue: this.value,
      max: Number(this.maxPhotos)
    }
  },

  methods: {
    close () {
      this.$emit('close')
    }
  }
}
</script>

<style lang="scss" scoped>
.option-photo {
  display: grid;
  grid-template-columns: 1fr 46px;

  &:hover .photo-gallery,
  &:focus-within .photo-gallery {
   border-color: var(--color-n-200);
  }

  &:hover .close,
  &:focus-within .close {
    background-color: var(--color-n-200);
    border: 1px solid var(--color-n-200);
    color: var(--color-n-500);

    &:hover {
      background-color: var(--color-s-5-light);
      border-color: var(--color-s-5-light);
      color: var(--color-s-5-dark);
    }
  }
}

.photo-gallery {
  position: relative;
  display: flex;
  align-items: center;
  min-height: 90px;
  padding: 8px;
  margin-right: -21px;
  border-radius: 12px;
  background-color: var(--color-n-050);
  border: 1px solid var(--color-n-050);
  transition: all 150ms ease-in-out;

  .read-only & {
    margin-right: 0;
    grid-column: 1 / 3;

    &:hover {
      border-color: var(--color-n-050);
    }
  }
}

.close {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  background-color: var(--color-n-050);
  border: 1px solid var(--color-n-050);
  color: var(--color-n-400);
  border-radius: 0 12px 12px 0;
  transition: all 150ms ease-in-out;

  .s-icon {
    margin-right: 8px;
  }
}

.input-file {
  display: none;

  &:disabled + .s-button-icon {
    pointer-events: none;
    background-color: var(--color-n-100);
  }
}

.gallery {
  flex: 1;
  display: flex;
  flex-wrap: wrap;
  padding-left: 8px;

  img {
    padding: 4px;
    border-radius: 8px;
  }

  .read-only & {
    padding-left: 0;
  }
}
</style>
