<template>
  <div v-if="full" class="user-badge">
    {{ fullname }}
  </div>
  <div v-else class="user-badge initials">
    {{ initials }}
  </div>
</template>

<script>
export default {
  name: 'UserBadge',

  props: {
    user: {
      type: Object,
      required: true
    },

    full: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    initials () {
      return `${this.user.firstname.charAt(0)}${this.user.lastname.charAt(0)}`
    },

    fullname () {
      return `${this.user.firstname} ${this.user.lastname}`
    }
  }
}
</script>

<style lang="scss" scoped>
.user-badge {
  display: flex;
  width: max-content;
  align-items: center;
  justify-content: center;
  padding: 4px 16px;
  border-radius: 20px;
  color: var(--color-n-700);
  background-color: var(--color-n-100);
  text-transform: capitalize;
  font-size: 14px;

  &.initials {
    width: 28px;
    height: 28px;
    padding: 0;
    text-transform: uppercase;
  }
}
</style>
