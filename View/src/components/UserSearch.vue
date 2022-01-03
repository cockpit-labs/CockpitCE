<template>
  <div class="user-search"
    @keydown.down.prevent="highlightNextOption"
    @keydown.up.prevent="highlightPrevOption"
    @keydown.enter="selectHighlightedUser"
  >
    <div class="selected-users" v-show="selectedUsers.length > 0">
      <div
        v-for="user in selectedUsers"
        :key="user.id"
        class="selected-user"
        @click="removeUser(user)"
      >
        <div class="name">{{ `${user.firstname} ${user.lastname}` }}</div>
        <div class="remove-btn"><SIcon name="times" fw /></div>
      </div>
    </div>

    <div class="search-field" v-show="(max > 1 && selectedUsers.length < max ) || selectedUsers.length === 0">
      <div class="selected-option" @click.stop="open">
        <span class="label">{{ $t('placeholder.selectUser') }}</span>
        <SIcon name="chevron-down" />
      </div>
      <div class="options" v-if="opened" v-click-outside="close">
        <div class="selected-option">
          <VueFuse
            ref="fuse"
            class="input-fuse"
            :keys="['firstname', 'lastname']"
            :list="userList"
            eventName="resultsUpdated"
            :placeholder="$t('placeholder.searchPlaceholder')" />
          <SIcon name="times" @click.native="close" />
        </div>
        <div class="list">
          <div
            v-for="user in usersFound"
            :key="user.id"
            :class="['option', {selected: user.id === highlightedOptionId}]"
            @click="selectUser(user)"
          >
            {{ `${user.firstname} ${user.lastname}` }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import User from '@/models/User'
import clickOutside from '@/plugins/cockpit-ui/directives/click-outside'

export default {
  name: 'UserSearch',

  directives: {
    clickOutside
  },

  props: {
    value: {
      type: Array
    },

    max: {
      type: Number,
      default: 1
    }
  },

  data () {
    return {
      opened: false,
      usersFound: [],
      highlightedOptionIndex: -1
    }
  },

  computed: {
    userList () {
      return User.query().all()
    },

    selectedUsers () {
      // return this.value ?? []
      const [first] = this.value
      if (first?.id) {
        return this.value
      } else if (typeof first === 'string') {
        return this.value.map(user => {
          const userId = user.substring(user.lastIndexOf('/') + 1)
          return User.find(userId)
        })
      }
      return []
    },

    highlightedOptionId () {
      if (this.highlightedOptionIndex >= 0) {
        return this.usersFound[this.highlightedOptionIndex]?.id
      }
      return null
    }
  },

  methods: {
    open () {
      this.opened = true
      this.$nextTick(() => {
        this.$refs.fuse.$el.focus()
      })
    },

    close () {
      this.opened = false
      this.highlightedOptionIndex = -1
    },

    selectUser (user) {
      const selectedUsers = this.selectedUsers

      if (selectedUsers.every(u => u.id !== user.id)) {
        selectedUsers.push(user)
        this.$emit('input', selectedUsers)
      }
      this.close()
    },

    removeUser (user) {
      const index = this.selectedUsers.findIndex(u => u.id === user.id)
      this.$delete(this.selectedUsers, index)
    },

    selectHighlightedUser () {
      if (this.highlightedOptionIndex >= 0) {
        const user = this.usersFound[this.highlightedOptionIndex]
        this.selectUser(user)
      }
    },

    highlightNextOption () {
      if (this.highlightedOptionIndex >= 0 && this.highlightedOptionIndex < this.usersFound.length) {
        this.highlightedOptionIndex++
      } else {
        this.highlightedOptionIndex = 0
      }
    },

    highlightPrevOption () {
      if (this.highlightedOptionIndex > 0) {
        this.highlightedOptionIndex--
      }
    }
  },

  created () {
    if (User.query().count() === 0) {
      this.$store.dispatch('getUsers')
    }

    this.$on('resultsUpdated', results => {
      this.usersFound = results
      this.highlightedOptionIndex = -1
    })
  }
}
</script>

<style lang="scss" scoped>
.search-field {
  @include font-stack;
  position: relative;
  max-width: 400px;
  border-radius: 8px;
  box-shadow: 0 3px 4px rgba($color: #000000, $alpha: 0.12);
}

.selected-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border-radius: 8px;
  color: var(--color-n-800);
  font-weight: $fw-semibold;
  background-color: var(--color-n-000);
  cursor: default;
  transition: all 150ms ease-in;

  .input-fuse {
    @include font-stack;
    width: 100%;
    appearance: none;
    margin: 0;
    border-radius: none;
    padding: 0;
    background-color: transparent;
    border: none;
    border-radius: none;
    box-shadow: none;
    font-size: 16px;
    color: var(--color-n-600);
    box-sizing: border-box;
    outline: none;
  }

  &:hover, &:focus-within {
    color: var(--color-p-900);
    background-color: var(--color-p-100);
  }

  .s-icon {
    font-size: 16px;
    margin-left: 16px;
  }
}

.options {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  border-radius: 8px;
  background-color: var(--color-n-000);
  box-shadow: -2px 2px 28px 2px rgba(0, 0, 0, 0.2);
  z-index: 1;
}

.options .selected-option {
  border-radius: 8px 8px 0 0;
}

.list {
  padding: 8px 16px 16px;
  max-height: 160px;
  overflow-y: auto;
}

.option {
  font-weight: $fw-regular;
  padding: 8px;
  margin: 2px 0;
  border-radius: 4px;
  color: var(--color-p-500);
  cursor: default;
  transition: transform 150ms ease-in;

  &:hover {
    color: var(--color-p-900);
    background-color: var(--color-p-100);
  }

  &:active {
    transform: scale3d(0.99, 0.99, 1);
  }

  &.selected {
    background-color: var(--color-p-800);
    color: var(--color-p-050);
  }
}

.selected-users {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-bottom: 8px;
}

.selected-user {
  display: flex;
  background-color: var(--color-n-200);
  color: var(--color-n-700);
  font-size: 14px;
  border-radius: 20px;

  .name {
    padding: 4px 12px;
  }
}

.remove-btn {
  padding: 4px 8px 4px 4px;
  border-left: 1px solid var(--color-n-300);
  border-top-right-radius: 20px;
  border-bottom-right-radius: 20px;

  &:hover {
    color: var(--color-n-900);
    background-color: var(--color-n-400);
  }
}
</style>
