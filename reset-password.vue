<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import { useStore } from 'vuex';
import { useApi } from '@/api';
import logo from '@/assets/images/logo.png';
import { passwordGeneration } from '@/services/password-generation';
import { ValidationError, ResetPasswordModel } from '@/ts/interfaces';


defineOptions({
  name: 'ResetPasswordPage',
});

// Hooks
const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const store = useStore();
const API = useApi();

// State
const logoImg = ref(logo);
const model = ref<ResetPasswordModel>({
  email: '',
  password: '',
  password_confirmation: '',
});
const errorMessages = ref<Record<string, string[]>>({});
const showPassword = ref({
  current: false,
  repeat: false,
});
const isFormSent = ref<boolean<(false);
const form = ref<any>(null);

const rules = computed(() => ({
  identityPassword: (v: string) =>
    v === model.value.password || t('auth.validation.password_mismatch'),
}));

// Methods
const toggleShowPassword = (type: 'current' | 'repeat'): void => {
  showPassword.value[type] = !showPassword.value[type];
};

const generatePassword = (): void => {
  model.value.password = passwordGeneration();
};

const submit = async (): Promise<void> => {
  try {
    const { valid } = await form.value.validate();

    if (valid) {
      await API.auth().resetPassword({
        ...model.value,
        token: route.query.token as string,
      });
      await router.push('/auth/login');
    }
  } catch (e) {
    const error = e as ValidationError;
    if (error.data?.errors) {
      errorMessages.value = error.data.errors;

      setTimeout(() => {
        errorMessages.value = {};
      }, 3000);
    }
  }
};
</script>

<template>
  <div class="fill-height d-flex align-center justify-center">
    <v-card
      class="rounded-lg py-3 py-lg-6 px-4 px-lg-8"
      elevation="1"
      :width="$vuetify.display.smAndUp ? 440 : 320"
    >
      <v-card-title class="d-flex flex-column pa-0 mb-4 ga-4">
        <div class="cursor-pointer" @click="$router.push('/')">
          <v-img :src="logoImg" alt="logo" />
        </div>
        <div class="text-center text-h5">
          {{ t('auth.titles.password_recovery') }}
        </div>
      </v-card-title>
      <v-card-text class="d-flex flex-column ga-2 px-0">
        <v-form
          v-if="!isFormSent"
          ref="form"
          validate-on="lazy"
          @submit.stop.prevent
        >
          <v-text-field
            v-model="model.email"
            :label="t('auth.labels.username')"
            :rules="[$rules.required()]"
            :error-messages="errorMessages.email"
          />
          <v-text-field
            v-model="model.password"
            :label="t('auth.labels.password')"
            :rules="[
              $rules.required(),
              $rules.minCount(8),
              $rules.maxCount(30),
              $rules.password(),
            ]"
            :error-messages="errorMessages.password"
            :type="showPassword.current ? 'text' : 'password'"
          >
            <template #append-inner>
              <v-tooltip>
                <template #activator="{ props }">
                  <v-icon
                    v-bind="props"
                    class="mr-3"
                    color="secondary"
                    @click="generatePassword()"
                  >
                    mdi-format-letter-matches
                  </v-icon>
                </template>
                <span>
                  {{ t('auth.buttons.generate_password') }}
                </span>
              </v-tooltip>
              <v-icon
                v-if="showPassword.current"
                class="mr-2"
                color="secondary"
                @click="toggleShowPassword('current')"
              >
                mdi-eye-off
              </v-icon>
              <v-icon
                v-else
                class="mr-2"
                color="secondary"
                @click="toggleShowPassword('current')"
              >
                mdi-eye
              </v-icon>
            </template>
          </v-text-field>
          <v-text-field
            v-model="model.password_confirmation"
            :label="t('auth.labels.password_confirmation')"
            :rules="[
              $rules.required(),
              $rules.minCount(8),
              $rules.maxCount(30),
              $rules.password(),
              rules.identityPassword(model.password_confirmation),
            ]"
            :error-messages="errorMessages.password_confirmation"
            :type="showPassword.repeat ? 'text' : 'password'"
          >
            <template #append-inner>
              <v-icon
                v-if="showPassword.repeat"
                class="mr-2"
                color="secondary"
                @click="toggleShowPassword('repeat')"
              >
                mdi-eye-off
              </v-icon>
              <v-icon
                v-else
                class="mr-2"
                color="secondary"
                @click="toggleShowPassword('repeat')"
              >
                mdi-eye
              </v-icon>
            </template>
          </v-text-field>
        </v-form>
      </v-card-text>
      <v-card-actions class="d-flex flex-column justify-center align-center pa-0 ga-2">
        <v-btn
          :ripple="false"
          class="text-none font-weight-bold"
          color="primary"
          variant="elevated"
          block
          @click="submit()"
        >
          {{ t('global_buttons.submit') }}
        </v-btn>
        <v-btn
          :ripple="false"
          class="text-none font-weight-bold"
          color="primary"
          variant="text"
          to="/auth/login"
        >
          {{ t('auth.buttons.back_login') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </div>
</template>

<style lang="scss" scoped>
.verification-title {
  font-size: 18px;
}
</style>