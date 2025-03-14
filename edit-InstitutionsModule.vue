<script>
export default {
  name: "EditInstitution",

  data: () => ({
    model: {},
    errorMessages: {}
  }),

  async mounted() {
    await this.loadData();
  },

  methods: {
    async loadData() {
      try {
        this.model = await this.$API.institutions().getForEdit(this.$route.params.id);
      } catch (e) {
        console.log(e);
      }
    },
    async submit() {
      try {
        const { valid } = await this.$refs.form.validate();

        if (valid) {
          await this.$API.institutions().edit(
            this.$route.params.id,
            this.model,
            { message: this.$t("institutions.messages.edit") }
          );
          await this.$router.push("/institutions");
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
      {{ $t("institutions.titles.edit") }}
    </v-card-title>
    <v-card-text>
      <v-form
        ref="form"
        validate-on="lazy"
        @submit.stop.prevent
      >
        <v-row>
          <v-col class="pb-0" cols="12">
            <v-text-field
              v-model="model.name"
              :label="$t('institutions.labels.name')"
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
        </v-row>
      </v-form>
    </v-card-text>
    <v-card-actions class="d-flex justify-center align-center">
      <v-btn
        to="/institutions"
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
