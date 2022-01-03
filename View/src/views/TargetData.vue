<template>
  <div class="content">
    <portal to="filters">
      <component :is="targetSelector" :loading="isFolderDataLoading" />
    </portal>

    <div class="data" v-if="attributes && Object.keys(attributes).length > 0">
      <div class="identity">
        <div class="photo" v-if="attributes.photo">
          <img :src="attributes.photo">
        </div>

        <div class="group">
          <div class="sub" v-if="attributes.address">
            <div class="label">Adresse</div>
            <div class="value">{{ attributes.address }}</div>
            <div class="plan" v-if="mapUrl">
              <a :href="mapUrl">
                <SButtonText icon="external-link-alt">Voir dans Plan</SButtonText>
              </a>
            </div>
          </div>
          <div class="sub" v-if="attributes.telephone">
            <div class="label">Téléphone</div>
            <div class="value">{{ attributes.telephone }}</div>
          </div>
          <div class="sub" v-if="attributes.director">
            <div class="label">Directeur</div>
            <div class="value">{{ attributes.director }}</div>
          </div>
        </div>
      </div>

      <div class="group" v-if="dataKeysAttributes.length > 0">
        <div class="title">Données clés</div>
        <div class="sub" v-for="(attr, i) in dataKeysAttributes" :key="i">
          <div class="label">{{ attr.label }}</div>
          <div class="value">{{ attr.value }}</div>
        </div>
      </div>

    </div>

    <div v-else class="no-data">Aucune information</div>
  </div>
</template>

<script>
import TargetSelector from '@/components/TargetSelector'
import TargetSelectorTile from '@/components/TargetSelectorTile'
import Group from '@/models/Group'

export default {
  components: {
    TargetSelector,
    TargetSelectorTile
  },

  data () {
    return {
      reservedAttributeIds: ['address', 'lat', 'long', 'director', 'telephone', 'photo']
    }
  },

  computed: {
    isFolderDataLoading () {
      return this.$store.getters.isFolderDataLoading
    },

    targetSelector () {
      return this.$store.state.targetListWithTiles ? TargetSelectorTile : TargetSelector
    },

    selectedGroupId () {
      return this.$store.state.groups.selectedGroupId
    },

    selectedGroup () {
      if (this.selectedGroupId) {
        return Group
          .query()
          .whereId(this.selectedGroupId)
          .first()
      }
      return null
    },

    dataKeysAttributes () {
      return this.selectedGroup?.attributes?.filter(attr => !this.reservedAttributeIds.includes(attr.label))
        .sort((a, b) => a.position - b.position)
    },

    attributes () {
      return this.selectedGroup?.attributes?.filter(attr => this.reservedAttributeIds.includes(attr.label))
        .reduce((result, attr) => {
          result[attr.label] = attr.value
          return result
        }, {})
    },

    mapUrl () {
      if (this.attributes.lat && this.attributes.lat) {
        return `http://maps.apple.com/?ll=${this.attributes.lat},${this.attributes.long}&q=${this.attributes.address}`
      }
      return null
    }
  }
}
</script>

<style lang="scss" scoped>
.identity {
  .tablet &, .desktop & {
    display: flex;
    gap: 16px;

    .photo {
      order: 1;
    }
  }
}
.group {
  margin-bottom: 40px;
}

.group .title {
  font-size: 16px;
  border-bottom: 1px solid var(--color-n-500);
  margin-bottom: 16px;
  letter-spacing: 1px;
  text-transform: uppercase;
}

.sub {
  margin-bottom: 16px;
}

.sub .label {
  font-size: 16px;
  font-variant-caps: all-petite-caps;
  color: var(--color-n-400);
}

.photo {
  margin-bottom: 32px;
  text-align: center;

  img {
    width: 100%;
    max-width: 500px;
  }
}

.no-data {
  text-align: center;
  padding-top: 80px;
}
</style>
