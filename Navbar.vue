<script>
import monoLogo from "@/assets/images/logo-monochrome.png";
import LangSwitcher from "@/components/settings/LangSwitcher.vue";
import Profile from "@/components/navigation/Profile.vue";

export default {
  name: "Navbar",

  props: {
    menuItems: {
      type: Array,
      required: true,
      default: () => []
    }
  },

  components: {
    LangSwitcher,
    Profile
  },

  data: () => ({
    monoLogo
  }),

  watch: {
    width: {
      immediate: true,
      deep: true,
      handler() {
        if (this.$vuetify.display.mdAndUp) {
          this.$emit("hideSidebar");
        }
      }
    }
  },

  computed: {
    width() {
      return this.$vuetify.display.width;
    },
    isActive() {
      return (item) => this.$route.fullPath.startsWith(item.to);
    },
    isGroupActive() {
      return (item) => item.children.some(child => this.isActive(child));
    }
  },
};
</script>

<template>
  <v-card
    class="navbar-wrapper"
    color="white"
    flat
    rounded="0"
  >
    <v-toolbar class="hidden-xs-and-down" color="#FDFDFD">
      <template v-slot:prepend>
        <div
          class="cursor-pointer"
          @click="$router.push('/')"
        >
          <v-img
            :src="monoLogo"
            width="32"
            height="32"
            alt="logo"
          />
        </div>
      </template>
      <v-list nav class="hidden-md-and-down d-md-flex ga-4">
        <div
          v-for="item in menuItems"
          :key="item.to"
        >
          <v-menu
            v-if="item?.children?.length"
            :value="item.text"
          >
            <template v-slot:activator="{ props }">
              <v-list-item
                v-bind="props"
                :class="{ 'v-list-group__header--active': isGroupActive(item) }"
                :ripple="false"
                variant="plain"
                exact
              >
                <v-icon :icon="item.icon" start />
                {{ $t(item.text) }}
              </v-list-item>
            </template>
            <v-list nav class="d-flex flex-column ga-2 mt-1">
              <v-list-item
                v-for="(child, index) in item.children"
                :key="index"
                :to="child.to"
                :ripple="false"
                :class="{ 'v-list-item--active': isActive(child) }"
                variant="plain"
                exact
              >
                <v-icon :icon="child.icon" start />
                {{ $t(child.text) }}
              </v-list-item>
            </v-list>
          </v-menu>
          <v-list-item
            v-else
            :to="item.to"
            :ripple="false"
            :class="{ 'v-list-item--active': isActive(item) }"
            variant="plain"
            exact
          >
            <v-icon :icon="item.icon" start />
            {{ $t(item.text) }}
          </v-list-item>
        </div>
      </v-list>
      <template v-slot:append>
        <div class="d-flex ga-4 justify-center align-center">
          <LangSwitcher />

          <span class="d-none d-md-inline">
            <Profile />
          </span>

          <span class="hidden-md-and-up">
            <v-app-bar-nav-icon @click="$emit('toggleSidebar')" />
          </span>
        </div>
      </template>
    </v-toolbar>
  </v-card>
</template>

<style scoped lang="scss">
.navbar-wrapper {
  padding: 0 20px;
  height: 64px;
  box-shadow: 0 16px 24px -3px rgba(0, 0, 0, 0.06),
    0 2px 6px 2px rgba(0, 0, 0, 0.04), 0 0 1px rgba(0, 0, 0, 0.04) !important;
}

.v-list-item {
  border-radius: 0;
  box-shadow: none;
  height: 48px;
}

.v-list-item--active {
  border-bottom: 2px solid #2A30B3;
  opacity: 1;
}

.v-list-group__header--active {
  border-bottom: 2px solid #2A30B3;
  opacity: 1;
}
</style>
