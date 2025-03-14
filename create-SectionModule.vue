<script>
export default {
  name: "CreateSection",

  data: () => ({
    model: {},
    errorMessages: {},
    profilesList: []
  }),

  async mounted() {
    await this.loadProfilesList();
  },

  methods: {
    async loadProfilesList() {
      try {
        this.profilesList = await this.$API.profiles().getList();
      } catch (e) {
        console.log(e);
      }
    },
    async submit() {
      try {
        const { valid } = await this.$refs.form.validate();

        if (valid) {
          await this.$API.sections().create(this.model, {
            message: this.$t("sections.messages.create")
          });
          await this.$router.push("/sections");
        }
      } catch (e) {
        console.log(e);
      }
    }
  }
}
</script>

<template>
  <v-card>
    <v-card-title class="pa-4">
      {{ $t("sections.titles.create") }}
    </v-card-title>
    <v-card-text>
      <v-form
        ref="form"
        validate-on="lazy"
        @submit.stop.prevent
      >
        <v-row>
          <v-col class="pb-0" cols="12" md="6">
            <v-text-field
              v-model="model.name"
              :label="$t('sections.labels.name')"
              :error-messages="errorMessages.name"
              :rules="[
                $rules.required(),
                $rules.minCount(3),
                $rules.maxCount(100),
                $rules.noLeadingWhitespace(),
                $rules.onlyCommonCharacters()
              ]"
              variant="outlined"
              counter="100"
            />
          </v-col>
          <v-col class="pb-0" cols="12" md="6">
            <v-autocomplete
              v-model="model.profile_id"
              :label="$t('sections.labels.profile')"
              :error-messages="errorMessages.profile_id"
              :items="profilesList"
              :rules="[$rules.required()]"
              item-title="text"
              variant="outlined"
            />
          </v-col>
        </v-row>
      </v-form>
    </v-card-text>
    <v-card-actions class="d-flex justify-center align-center">
      <v-btn
        to="/sections"
        variant="text"
        size="small"
      >
        {{ $t("global_buttons.cancel") }}
      </v-btn>
      <v-btn
        color="primary"
        variant="outlined"
        size="small"
        @click="submit()"
      >
        {{ $t("global_buttons.send") }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<style scoped lang="scss">
</style>
