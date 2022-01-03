<template>
  <div class="target-selector-tile" :disabled="only1Target">
    <div class="selected-target" @click.stop="openList">
      <div class="label">
        <SIcon v-if="selectedTarget && selectedTarget.icon" :name="selectedTarget.icon" class="target-icon" fw />
        <span v-if="selectedTarget">{{ selectedTarget.name }}</span>
        <span v-else>{{ $t('placeholder.selectTarget') }}</span>
      </div>
      <SIcon v-if="loading" name="circle-notch" class="caret" spin />
      <SIcon name="chevron-down" class="caret" v-else-if="!only1Target" />
    </div>

    <div ref="targetList" :class="['target-list', {open: modalOpened}]" v-show="modalOpened">
      <div class="loading" v-if="loading">{{ $t('loading') }}</div>

      <div class="header">
        <div class="level-up" v-if="currentNodeParent" @click="setCurrentNode(currentNodeParent)">
          <SIcon name="caret-left" fw />
        </div>

        <div
          class="current-node"
          v-if="currentNode"
          :class="{selected: currentNode.id === selectedTargetId}"
          @click="setSelectedTarget(currentNode, true)"
        >
          <div class="label">{{ currentNode.name }}</div>
        </div>
      </div>

      <div class="targets">
        <div
          class="target"
          v-for="target in currentNodeChildren"
          :key="target.id"
          :class="{selected: target.id === selectedTargetId}"
          @click="goTo(target)"
        >
          <SIcon v-if="target.icon" :name="target.icon" class="target-icon" />
          <div class="label">
            <span>{{ target.name }}</span>
            <SIcon name="caret-right" class="caret" v-if="target.children.length > 0" />
          </div>
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
  name: 'TargetSelectorTile',

  props: {
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
      return this.$store.state.targets.selectedGroupId
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
        return Group.query().where('parentId', null).with('children.children').first()
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
      return Group.query().count() === 1
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

.header {
  display: flex;
  margin-bottom: 24px;
}

.level-up {
  display: flex;
  align-items: center;
  padding: 16px 15px 16px 14px;
  margin-right: 16px;
  color: var(--color-n-600);
  font-size: 24px;
  line-height: 1;
  border: 1px solid var(--color-n-600);
  border-radius: 8px;
  cursor: default;
  transition: all 150ms ease-in;

  @media (hover: hover) and (pointer: fine) {
    border-color: transparent;

    &:hover {
      color: var(--color-n-900);
      background-color: var(--color-n-200);
      border-color: var(--color-n-200);
    }
  }

  &:active {
    transform: scale3d(0.99, 0.99, 1);
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

.targets {
  display: grid;
  grid-template-columns: 1fr 1fr;
  // flex-wrap: wrap;
  gap: 8px;

  .tablet &, .desktop & {
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  }
}

.target {
  display: grid;
  grid-template-columns: 1fr;
  grid-template-rows: 1fr;
  height: 110px;
  font-weight: $fw-semibold;
  padding: 16px;
  border-radius: 8px;
  border: 1px solid var(--color-p-500);
  color: var(--color-p-500);
  cursor: default;
  transition: transform 150ms ease-in;

  @media (hover: hover) and (pointer: fine) {
    &:hover {
      color: var(--color-p-900);
      background-color: var(--color-p-100);
      border-color: var(--color-p-100);
    }

    &:hover .s-icon {
      margin-right: 4px;
    }
  }

  &:active {
    transform: scale3d(0.99, 0.99, 1);
  }

  &.selected {
    background-color: var(--color-p-800);
    color: var(--color-n-000);
  }

  .target-icon {
    grid-row: 1 / -1;
    grid-column: 1;
    font-size: 32px;
    // color: var(--color-p-100);
  }

  .label {
    grid-row: 1;
    grid-column: 1;
    align-self: flex-end;
    display: flex;
    align-items: center;

    > span {
      flex: auto;
      padding-right: 8px;
      font-size: 14px;

      .tablet &, .desktop & {
        font-size: 16px;
      }
    }
  }

  .caret {
    font-size: 22px;
    transition: margin-right 150ms ease-in;

    @media (hover: hover) and (pointer: fine) {
      margin-right: 8px;
    }
  }
}

.current-node {
  display: flex;
  align-items: center;
  color: var(--color-p-900);
  font-weight: $fw-extrabold;
  padding: 16px;
  border-radius: 8px;
  // border: 1px solid var(--color-p-900);
  cursor: default;
  transition: transform 150ms ease-in;

  @media (hover: hover) and (pointer: fine) {
    &:hover {
      color: var(--color-p-900);
      background-color: var(--color-p-100);
      border-color: var(--color-p-100);
    }
  }

  &:active {
    transform: scale3d(0.99, 0.99, 1);
  }

  &.selected {
    background-color: var(--color-p-800);
    color: var(--color-p-050);
  }
}

.selected-target {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 24px 8px 18px;
  border-radius: 8px;
  color: var(--color-p-500);
  font-weight: $fw-semibold;
  background-color: var(--color-n-000);
  cursor: default;
  white-space: nowrap;
  text-overflow: ellipsis;
  box-shadow: 0 3px 4px rgba($color: #000000, $alpha: 0.12);
  // transition: color 150ms ease-in;

  &:hover {
    color: var(--color-p-900);
    background-color: var(--color-p-100);

    .label .target-icon {
      background-color: var(--color-p-100);
    }
  }

  .caret {
    font-size: 16px;
    margin-left: 16px;
    color: var(--color-n-800);
  }

  .label {
    display: flex;
    align-items: center;
    // color: var(--color-p-500);

    > span {
      padding: 9px 0;
    }

    .target-icon {
      width: 24px;
      height: 24px;
      padding: 8px;
      margin-right: 8px;
      background-color: var(--color-p-050);
      border-radius: 20px;
    }
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
    max-height: 700px;
    max-width: 800px;
  }
}
</style>
