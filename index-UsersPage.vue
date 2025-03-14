<script>
import API from "@/api/index.js";
import tablePagination from "@/mixins/table-pagination.js";
import tableFilters from "@/mixins/table-filters.js";
import configHeaders from "@/views/users/employees/data/headers.json";

export default {
  name: "Users",

  mixins: [tablePagination, tableFilters],

  async beforeRouteEnter(to, from, next) {
    try {
      const params = {
        filter: to.query,
        page: to.params.page,
        perPage: 10
      };

      const response = await API.users().get(params);

      next((vm) => {
        vm.setHeaders();
        vm.setServerResponse(response);
      })
    } catch (e) {
      console.log(e);
    }
  },

  data: () => ({
    loading: false,
    filter: {},
    headers: [],
    items: [],
    selected: [],
    totalItems: 0
  }),

  methods: {
    setHeaders() {
      this.headers = configHeaders.map(header => ({
        ...header,
        title: this.$t(header.title),
      }));
    },
    async loadData() {
      try {
        this.loading = true;

        const response = await this.$API.users().get({
          page: this.pagination.page,
          perPage: this.pagination.per_page
        });

        this.setServerResponse(response);
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    setServerResponse(response) {
      this.items = response.items;
      this.totalItems = response.total;
      this.pagination.page = response.current;
    },
    editItem(id) {
      this.$router.push(`/users/edit/${id}`);
    },
    async deleteRow(id) {
      try {
        await this.$API.users().delete(id, {
          message: this.$t("global_alert.successful_removal")
        });
        await this.loadData();
      } catch (e) {
        console.log(e)
      }
    },
    async deleteMultiple() {
      try {
        await Promise.all(
          this.selected.map((id) => this.$API.users().delete(id, { hideAlert: true }))
        );
        await this.$store.dispatch(
          "alert/showSuccess",
          this.$t("global_alert.multiple_deletion")
        );
        this.selected = [];
        await this.loadData();
      } catch (e) {
        console.log(e)
      }
    }
  }
}
</script>

<template>
  <v-card flat>
    <v-card-title class="d-flex px-0">
      <span class="text-h5 font-weight-bold text-no-wrap">
        {{ $t("users.titles.main") }}
      </span>
      <v-spacer />
      <v-btn
        variant="outlined"
        color="primary"
        to="/users/create"
      >
        <v-icon icon="mdi-plus" start />
        <span>
          {{ $t("users.buttons.add") }}
        </span>
      </v-btn>
    </v-card-title>
    <v-divider />
    <v-card-text class="px-0">
      <v-card
        class="position-relative elevation-1 rounded-lg"
      >
        <v-fade-transition origin="right">
          <div
            v-if="selected.length"
            class="table-container__action d-flex justify-end align-center"
          >
            <div class="body-2 primary--text ml-6">
              {{ selected.length }} {{ $t("users.messages.selected") }}
            </div>
            <v-spacer />
            <v-btn
              v-confirm="{
                title: $t('users.titles.delete'),
                message: $t('users.messages.delete'),
                onAccept: () => deleteMultiple()
              }"
              :ripple="false"
              class="mr-7"
              color="red"
              variant="text"
            >
              <v-icon icon="mdi-close" size="16" />
              <span class="body-2 ml-1 font-weight-medium">
                {{ $t("global_buttons.delete") }}
              </span>
            </v-btn>
          </div>
        </v-fade-transition>
        <v-data-table-server
          v-model="selected"
          v-model:items-per-page="pagination.per_page"
          v-model:page="pagination.page"
          v-model:sort-by="sort.sortBy"
          :loading="loading"
          :items="items"
          :headers="headers"
          :items-per-page-options="itemsPerPageOptions"
          :items-length="totalItems"
          height="568px"
          fixed-header
          hover
          @update:sort-by="loadData()"
          @update:items-per-page="changeItemsPerPage()"
        >
          <template #[`item.role`]="{ item }">
            <span>
              {{ $t(`users.roles.${item.role}`) }}
            </span>
          </template>
          <template #[`item._actions`]="{ item }">
            <div class="d-flex align-center justify-center">
              <v-hover v-slot="{ isHovering, props }">
                <v-icon
                  v-bind="props"
                  :color="isHovering ? 'grey-darken-3' : 'grey-darken-1'"
                  @click="editItem(item.id)"
                >
                  mdi-pencil
                </v-icon>
              </v-hover>
              <v-hover v-slot="{ isHovering, props }">
                <v-icon
                  v-bind="props"
                  v-confirm="{
                    title: $t('global_titles.deletion'),
                    message: $t('global_messages.delete_message'),
                    onAccept: () => deleteRow(item.id)
                  }"
                  :color="isHovering ? 'red-darken-2' : 'red'"
                  class="ml-3"
                >
                  mdi-delete
                </v-icon>
              </v-hover>
            </div>
          </template>
        </v-data-table-server>
      </v-card>
    </v-card-text>
  </v-card>
</template>

<style scoped lang="scss">
</style>
