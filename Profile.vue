<script lang="ts">
import Vue from "vue";
import ChangePasswordDialog from "@/components/dialogs/ChangePasswordDialog.vue";
import { ROLE_NAMES } from "@/modules/catalogs/roles/resources/consts";
import { ResourceObjectInterface } from "@/ts/interfaces";
import { clearResidentCards } from "@/utils";

export default Vue.extend({
  name: "Profile",

  components: { ChangePasswordDialog },

  data: () => ({
    showPasswordDialog: false as boolean,
    roleNames: ROLE_NAMES as ResourceObjectInterface,
    isSetFromLocalStorage: false as boolean
  }),

  mounted() {
    this.changeGlobalFontSize();

    this.isSetFromLocalStorage = true;
  },

  computed: {
    user(): any {
      return this.$store.getters["user/info"];
    },
    isFontIncreased: {
      get(): boolean {
        return this.$store.getters["fonts/getFontSizeIncreased"];
      },
      set(): void {
        this.$store.dispatch("fonts/toggleFontSize");
      }
    }
  },

  methods: {
    async logout(): Promise<void> {
      try {
        clearResidentCards();

        await this.$store.dispatch("authentication/logout");
      } catch (e) {
        await this.$store.dispatch("alerts/showError", e?.message);
      }
    },
    changePasswordDialogState(): void {
      this.showPasswordDialog = !this.showPasswordDialog;
    },
    changeGlobalFontSize(): void {
      if (!this.isFontIncreased && !this.isSetFromLocalStorage) {
        return;
      }

      const allElements = Array.from(document.getElementsByTagName('*')) as Array<HTMLElement>;

      for (const element of allElements) {
        const styles = window.getComputedStyle(element);
        const fontSize = styles.fontSize;

        let isNested = false;
        let parentElement: HTMLElement | null = element.parentElement;

        while (parentElement !== null && parentElement !== document.body) {
          const parentStyles = window.getComputedStyle(parentElement);

          if (parentStyles.fontSize) {
            isNested = true;
            break;
          }

          parentElement = parentElement.parentElement;
        }

        if (fontSize && !isNested) {
          const currentSize = parseFloat(fontSize);
          const sizeChange = this.$store.getters["fonts/getFontSizeIncreased"] ? 2 : -2;

          element.style.fontSize = `${currentSize + sizeChange}px`;
        }
      }

      document.documentElement.dataset.fontSizeIncreased = this.isFontIncreased.toString();
    }
  }
});
</script>

<template>
  <div>
    <v-menu
      v-if="user"
      nudge-right="-178"
      nudge-top="-14"
      offset-y
      :close-on-content-click="false"
    >
      <template v-slot:activator="{ on, attrs }">
        <v-avatar v-bind="attrs" v-on="on" color="primary" size="40">
          <v-img v-if="user.avatar" :src="user.avatar.url"></v-img>
          <span v-else-if="user" class="white--text font-weight-bold">
            {{ user.first_name.substring(0, 1).toUpperCase()
            }}{{ user.last_name.substring(0, 1).toUpperCase() }}
          </span>
        </v-avatar>
      </template>
      <v-card class="align-center" flat>
        <div class="pa-3 pr-10 d-flex align-center">
          <v-avatar color="primary" size="44">
            <v-img v-if="user && user.avatar" :src="user.avatar.url"></v-img>
            <v-avatar v-else class="white--text font-weight-bold">
              {{ user.first_name.substring(0, 1).toUpperCase()
              }}{{ user.last_name.substring(0, 1).toUpperCase() }}
            </v-avatar>
          </v-avatar>
          <div class="ml-3">
            <div class="name-text">
              <span>
                {{ user && user.first_name }} {{ user && user.last_name }}
              </span>
              <span>
                ({{ roleNames[`${user.role}`] }})
              </span>
            </div>
            <div class="caption light_secondary--text">
              {{ user && user.email }}
            </div>
          </div>
        </div>
        <v-divider></v-divider>
        <v-hover v-slot="{ hover }">
          <v-list-item
            :class="{ light_primary2: hover }"
            class="pl-0 pr-3"
            dense
          >
            <v-list-item-content class="pl-3 mb-2">
              <v-switch
                class="pa-0 ma-0"
                v-model="isFontIncreased"
                hide-details
                @change="changeGlobalFontSize()"
              >
                <template v-slot:label>
                  <span class="caption font-switch__label">
                    {{ $t("global_labels.toggle_font_size") }}
                  </span>
                </template>
              </v-switch>
            </v-list-item-content>
          </v-list-item>
        </v-hover>
        <v-hover v-slot="{ hover }">
          <v-list-item
            :class="{ light_primary2: hover }"
            class="px-3 cursor-pointer"
            dense
            @click="changePasswordDialogState()"
          >
            <v-list-item-icon>
              <v-icon :class="{ 'primary--text': hover }"
              >mdi-lock-outline
              </v-icon>
            </v-list-item-icon>
            <v-list-item-content class="caption">
              <div :class="{ 'primary--text': hover }" class="text-left">
                {{ $t("global_labels.change_password") }}
              </div>
            </v-list-item-content>
          </v-list-item>
        </v-hover>
        <v-hover v-slot="{ hover }">
          <v-list-item
            v-confirm="{
              title: $t('global_titles.exit'),
              message: $t('global_messages.exit_message'),
              callback: () => logout()
            }"
            :class="{ light_primary2: hover }"
            class="px-3"
            dense
          >
            <v-list-item-icon>
              <v-icon :class="{ 'primary--text': hover }"
                >mdi-exit-to-app
              </v-icon>
            </v-list-item-icon>
            <v-list-item-content class="caption">
              <div :class="{ 'primary--text': hover }" class="text-left">
                {{ $t("global_titles.exit") }}
              </div>
            </v-list-item-content>
          </v-list-item>
        </v-hover>
      </v-card>
    </v-menu>
    <ChangePasswordDialog
      :show="showPasswordDialog"
      @closeDialog="changePasswordDialogState()"
    />
  </div>
</template>

<style scoped>
.name-text {
  font-weight: 600;
  font-size: 14px;
  line-height: 19px;
  color: #2a2a4b;
}

.list-item-text {
  font-style: normal;
  font-weight: normal;
  font-size: 12px;
  line-height: 20px;
  letter-spacing: 0.25px;
  color: #707b90;
}

.font-switch__label {
  color: rgba(0, 0, 0, 0.87)
}
</style>
