<template>
  <div class="target-selector" :disabled="only1Target">
    <div class="selected-target" @click.stop="openList" v-show="!expanded">
      <span class="label" v-if="selectedTarget">{{ selectedTarget.name }}</span>
      <span v-else>{{ $t('placeholder.selectTarget') }}</span>
      <SIcon v-if="loading" name="circle-notch" spin />
      <SIcon name="chevron-down" v-else-if="!only1Target" />
    </div>

    <div ref="targetList" :class="['target-list', {open: modalOpened}]" v-show="expanded || modalOpened">
      <div class="loading" v-if="loading">{{ $t('loading') }}</div>
      <div class="level-up" v-if="currentNodeParent" @click="setCurrentNode(currentNodeParent)">
        <div class="label">
          <SIcon name="caret-left" />
          <span>{{ currentNodeParent.name }}</span>
        </div>
      </div>
      <div
        class="target current-node"
        v-if="currentNode"
        :class="{selected: currentNode.id === selectedTargetId}"
        @click="setSelectedTarget(currentNode, true)"
      >
        <div class="label">{{ currentNode.name }}</div>
      </div>

      <div
      class="target"
      v-for="target in currentNodeChildren"
      :key="target.id"
      :class="{selected: target.id === selectedTargetId}"
      @click="goTo(target)"
      >
        <div class="label">
          <span>{{ target.name }}</span>
          <SIcon name="caret-right" v-if="target.children.length > 0" />
        </div>
      </div>
    </div>

    <div class="overlay" v-show="modalOpened" @click="closeList"></div>

    <GlobalEvents
      v-if="modalOpened"
      @keyup.esc="closeList"
    />
  </div>
</template>

<script>
import Group from '@/models/Group'

export default {
  name: 'TargetSelector',

  props: {
    expanded: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      currentNodeTargetId: null,
      modalOpened: false
    }
  },

  computed: {
    selectedTargetId () {
      return this.$store.state.groups.selectedGroupId
    },

    selectedTarget () {
      if (this.selectedTargetId) {
        return Group.query().whereId(this.selectedTargetId).with('children').with('parent').first()
      } else {
        return null
      }
    },

    currentNode () {
      if (this.currentNodeTargetId === null && this.selectedTarget && !this.only1Target) {
        // Afficher le noeud de la target sélectionnée
        if (this.selectedTarget.children.length > 0) {
          return Group.query().whereId(this.selectedTarget.id).with('children.children').with('parent').first()
        } else {
          return Group.query().whereId(this.selectedTarget.parent.id).with('children.children').with('parent').first()
        }
      } else if (this.currentNodeTargetId) {
        // Afficher le noeud demandé
        return Group.query().whereId(this.currentNodeTargetId).with('children.children').with('parent').first()
      } else {
        // Afficher le noeud racine
        return Group.query().where('root', true).with('children.children').first()
      }
    },

    currentNodeChildren () {
      if (this.currentNode) {
        return this.currentNode.children
      } else {
        return null
      }
    },

    currentNodeParent () {
      if (this.currentNode && this.currentNode.parent) {
        return this.currentNode.parent
      } else {
        return null
      }
    },

    only1Target () {
      return this.selectedTarget?.parent === null && this.selectedTarget?.children.length === 0
    }
  },

  methods: {
    openList () {
      this.modalOpened = true
    },

    closeList () {
      this.modalOpened = false
    },

    goTo (target) {
      if (target.children.length > 0) {
        this.setSelectedTarget(target)
        this.setCurrentNode(target)
      } else {
        this.setSelectedTarget(target, true)
      }
    },
    setCurrentNode (target) {
      this.currentNodeTargetId = target.id
      this.$refs.targetList.scrollTop = 0
      // this.$store.commit('setCurrentNodeTargetId', target.id)
    },
    setSelectedTarget (target, closeList = false) {
      if (closeList) this.closeList()
      this.$store.commit('setSelectedGroupId', target.id)
      this.$emit('selectTarget', target.id)
    },
    reset () {
      this.$store.commit('setSelectedGroupId', null)
      this.currentNodeTargetId = null
    }
  }
}
</script>

<style lang="scss" scoped>
.target-selector {
  @include font-stack;
  position: relative;
  display: flex;
  flex-direction: column;
  font-size: 16px;

  &[disabled] {
    pointer-events: none;
  }
}

.level-up {
  padding: 16px;
  margin-bottom: 16px;
  color: var(--color-n-500);
  font-weight: $fw-bold;
  cursor: default;
  transition: all 150ms ease-in;

  @media (hover: hover) and (pointer: fine) {
    &:hover {
      color: var(--color-p-700);
    }

    &:hover .s-icon {
      margin-left: 4px;
      margin-right: 20px;
    }
  }

  &:active {
    transform: scale3d(0.99, 0.99, 1);
  }

  .s-icon {
    font-size: 22px;
    margin-right: 16px;
    margin-left: 8px;
    transition: all 150ms ease-in;
  }

  .label {
    display: flex;
    align-items: center;
  }
}

.target-list {
  padding: 16px;
  border-radius: 12px;
  background-color: var(--color-n-000);
  box-shadow: 0 3px 4px rgba($color: #000000, $alpha: 0.12);
  overflow-y: auto;

  &.open {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: calc(100vw - 32px);
    max-height: calc(100vh - 128px);
    box-sizing: border-box;
    z-index: 110;
  }
}

.overlay {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: 100vw;
  background-color: rgba(0,0,0,0.6);
  z-index: 100;
}

.target {
  font-weight: $fw-semibold;
  padding: 16px;
  margin: 2px 0;
  border-radius: 8px;
  color: var(--color-p-500);
  cursor: default;
  transition: transform 150ms ease-in;

  @media (hover: hover) and (pointer: fine) {
    &:hover {
      color: var(--color-p-900);
      background-color: var(--color-p-100);
    }

    &:hover .s-icon {
      margin-right: 4px;
    }
  }

  &:active {
    transform: scale3d(0.99, 0.99, 1);
  }

  &.current-node {
    color: var(--color-p-900);
    font-weight: $fw-extrabold;
  }

  &.selected {
    background-color: var(--color-p-800);
    color: var(--color-p-050);
  }

  .label {
    display: flex;
    align-items: center;
  }

  .s-icon {
    margin-left: auto;
    margin-right: 8px;
    font-size: 22px;
    transition: margin-right 150ms ease-in;
  }
}

.selected-target {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  border-radius: 8px;
  color: var(--color-n-800);
  font-weight: $fw-semibold;
  background-color: var(--color-n-000);
  cursor: default;
  white-space: nowrap;
  text-overflow: ellipsis;
  box-shadow: 0 3px 4px rgba($color: #000000, $alpha: 0.12);
  transition: all 150ms ease-in;

  &:hover {
    color: var(--color-p-900);
    background-color: var(--color-p-100);
  }

  .s-icon {
    font-size: 16px;
    margin-left: 16px;
  }
}

.loading {
  position: absolute;
  background-color: rgba(255,255,255,0.8);
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(2px);
  border-radius: 12px;
}

.desktop {
  .target-list {
    max-height: 600px;
    max-width: 500px;
  }
}
</style>
