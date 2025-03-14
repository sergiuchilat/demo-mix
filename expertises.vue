<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import configHeaders from '@/views/profile/data/headers.json';
import ExpertisesFilters from '@/components/modules/reports/ExpertisesFilters.vue';
import DownloadExpertisesReport from '@/components/modules/reports/DownloadExpertisesReport.vue';
import { REPORTS_LIMIT } from '@/static/consts.js';
import { formatDate } from '@/utils';
import { useStore } from 'vuex';
import { useApi } from '@/api';
import { Header, Filter, Item } from '@/ts/interfaces';
import { useTablePagination } from '@/composables/useTablePagination';

defineOptions({
  name: 'ReportsExpertises',
});

// Hooks
const { t, tc } = useI18n();
const store = useStore();
const API = useApi();

const { itemsPerPageOptions, paginationOptions, updatePagination } = useTablePagination(10);

// State
const loading = ref<boolean>(true);
const headers = ref<Header[]>([]);
const items = ref<Item[]>([]);
const totalItems = ref<number>(0);
const filters = ref<Filter>({});
const showDownloadBtns = ref<boolean>(false);
const reportsLimit = REPORTS_LIMIT;

// Methods
const loadData = async (): Promise<void> => {
  try {
    items.value = await API.reports().getExpertises(filters.value);
  } catch (e) {
    console.error(e);
  }
};

const loadHeaders = (): Promise<void> => {
  headers.value = configHeaders.expertise.map((header: Header) => ({
    ...header,
    title: t(header.title),
  }));
};

const applyFilters = async (newFilters: Filter): Promise<void> => {
  filters.value = newFilters;

  await loadData();

  showDownloadBtns.value = Object.values(newFilters).some((field) =>
    Array.isArray(field) ? field.length > 0 : !!field
  );
};

const checkLimits = (): void => {
  const totalItemsCount = items.value.reduce((sum, item) => sum + item.total, 0);

  if (totalItemsCount > reportsLimit) {
    store.dispatch(
      'alert/showWarning',
      tc('global_validation.report_limit', { limit: reportsLimit, current: totalItemsCount })
    );
  }
};

// Lifecycle
onMounted(async () => {
  await loadData();
  loadHeaders();
  loading.value = false;
});
</script>

<template>
  <div>
    <v-row>
      <v-col cols="12" class="d-flex justify-center align-center mb-4">
        <h2 class="text-h5">
          {{ t('reports.titles.expertises') }}
        </h2>
        <v-spacer />
        <DownloadExpertisesReport
          v-if="showDownloadBtns"
          :filters="filters"
        />
        <ExpertisesFilters
          :value="filters"
          @updateFilters="applyFilters"
        />
      </v-col>
    </v-row>
    <v-skeleton-loader
      v-if="loading"
      type="table-row-divider, table-row@5"
    />
    <v-card
      v-else
      class="position-relative elevation-1 rounded-lg"
    >
      <v-data-table
        :headers="headers"
        :items="items"
        :items-per-page="paginationOptions.itemsPerPage"
        :page="paginationOptions.page"
        :items-per-page-options="itemsPerPageOptions"
        height="568px"
        fixed-header
        hover
        @update:options="updatePagination"
      >
        <template #item.date="{ item }">
          <span>
            {{ formatDate(item.date) }}
          </span>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>

<style scoped lang="scss"></style>