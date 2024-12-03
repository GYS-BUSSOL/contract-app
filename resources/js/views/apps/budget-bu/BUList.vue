<script setup>
import { getListMerContractStatus } from '@db/apps/range/db';

const emit = defineEmits(['sumBudget','sumExpense','sumBalance','activeYear']);
// Store
const searchQuery = ref('')
const selectedYears = ref()
const dataRangeYear = ref([])
// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const isRenewalAddDialogVisible = ref(false)

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Actions',
    key: 'actions',
    sortable: false,
  },
  {
    title: 'Year',
    key: 'join_first_bgt_year',
  },
  {
    title: 'Business Unit',
    key: 'description',
  },
  {
    title: 'Business Unit Head',
    key: 'join_first_bgt_bu_head',
  },
  {
    title: 'Budget (IDR)',
    key: 'join_first_bgt_amount',
  },
  {
    title: 'Expense (IDR)',
    key: 'join_first_bgt_expense',
  },
  {
    title: 'Balance (IDR)',
    key: 'join_first_bgt_balance',
  },
]

const {
  data: budgetBUData,
  execute: fetchBudgetBU,
} = await useApi(createUrl('/apps/budget-bu/search', {
  query: {
    q: searchQuery,
    year: selectedYears,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const budgets = computed(() => budgetBUData.value.budgets || [])
const totalBudgetBU = computed(() => budgetBUData.value.totalBudgetBU || 0)
const sumBudget = computed(() => budgetBUData.value.sumBudgetBU || 0)
const sumExpense = computed(() => budgetBUData.value.sumExpenseBU || 0)
const sumBalance = computed(() => budgetBUData.value.sumBalanceBU || 0)

watch([sumBudget, sumExpense, sumBalance, selectedYears],([newBudget, newExpense, newBalance, activeYear], [oldBudget,oldExpense, oldBalance, oldActiveYear]) => {
  emit('sumBudget', newBudget);
  emit('sumExpense', newExpense);
  emit('sumBalance', newBalance);
  emit('activeYear', activeYear);
}, { immediate: true })

const deleteBudgetBU = async id => {
  await $api(`/apps/budget-bu/${ id }`, { method: 'DELETE' })

  // Delete from selectedRows
  const index = selectedRows.value.findIndex(row => row === id)
  if (index !== -1)
    selectedRows.value.splice(index, 1)

  // Refetch Budget BU
  fetchBudgetBU()
}

const fetchRangeYear = async () => {
  try {
    const response = await getListMerContractStatus('trn_budget','bgt_year');
    if (response.status === 200) {
      const rows = response.data.rows || {};
      const minYear = Number(rows.min_year) || new Date().getFullYear();
      const maxYear = Number(rows.max_year) || new Date().getFullYear();
      
      for (let i = minYear; i <= maxYear; i++) {
        dataRangeYear.value.push({ title: i, value: i });
      }
    } else {
      console.error('Failed to fetch get range year');
    }
    
  } catch (error) {
    console.error('Error fetching get range year');
  }
};

const IDRFormat = (data) => {
  return 'Rp. ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(data).replace('Rp', '').trim()
}

onMounted(() => {
  fetchRangeYear();
});
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filters</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <!-- Select Year -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedYears"
              placeholder="Select Year"
              :items="dataRangeYear"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <AppSelect
            :model-value="itemsPerPage"
            :items="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: 100, title: '100' },
            ]"
            style="inline-size: 6.25rem;"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
          <!-- Search  -->
          <div style="inline-size: 15.625rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="Search..."
              clearable
            />
          </div>

          <!-- Export button -->
          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-upload"
          >
            Export
          </VBtn>
          <!-- Create New Renewal button -->
          <VBtn
            color="primary"
            prepend-icon="tabler-plus"
            @click="isRenewalAddDialogVisible = !isRenewalAddDialogVisible"
          >
            Create New
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="budgets"
        item-value="con_id"
        :items-length="totalBudgetBU"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Year -->
        <template #item.join_first_bgt_year="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_first_bgt_year }}
          </div>
        </template>

        <!-- Business Unit -->
        <template #item.description="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ (item.number ? item.number + ' - ': '' ) + item.description }}
          </div>
        </template>

        <!-- Business Unit Head -->
        <template #item.join_first_bgt_bu_head="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_first_bgt_bu_head != null && item.join_first_bgt_bu_head != '' ? item.join_first_bgt_bu_head : '-' }}
          </div>
        </template>

        <!-- Budget -->
        <template #item.join_first_bgt_amount="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ IDRFormat(item.join_first_bgt_amount) ?? '-' }}
          </div>
        </template>
        
        <!-- Expense -->
        <template #item.join_first_bgt_expense="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ IDRFormat(item.join_first_bgt_expense) ?? '-' }}
          </div>
        </template>

        <!-- Balance -->
        <template #item.join_first_bgt_balance="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ IDRFormat(item.join_first_bgt_balance) ?? '-' }}
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="deleteBudgetBU(item.con_id)">
            <VIcon icon="tabler-edit" />
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Edit Data</span>
            </VTooltip>
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalBudgetBU"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
  <RenewalAddDialog
    v-model:isDialogVisible="isRenewalAddDialogVisible"
    :user-data="customerData"
  />
</template>
