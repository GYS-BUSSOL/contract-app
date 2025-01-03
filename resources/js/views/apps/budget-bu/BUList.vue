<script setup>
import { getListMerContractStatus } from '@db/apps/range/db';

const emit = defineEmits(['sumBudget','sumExpense','sumBalance','activeYear']);
// Store
const searchQuery = ref('')
const selectedYears = ref(new Date().getFullYear())
const dataRangeYear = ref([])
// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const isDialogVisible = ref(false)
const isTypeDialog = ref('')
const IDBU = ref()
const yearData = ref()
const isSnackbarResponse = ref(false)
const isSnackbarResponseAlertColor = ref('error')
const fetchTrigger = ref(0);
const errorMessages = ref('Internal server error')
const successMessages = ref('Successfully')
const token = useCookie('accessToken')
const errors = ref({
  year: undefined,
  number: undefined,
  description: undefined,
  bgt_bu_head: undefined,
})
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

watch(
  [sumBudget, sumExpense, sumBalance, selectedYears],
  ([newBudget, newExpense, newBalance, activeYear]) => {
    emit('sumBudget', newBudget);
    emit('sumExpense', newExpense);
    emit('sumBalance', newBalance);
    emit('activeYear', activeYear);
}, { immediate: true })

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
      dataRangeYear.value.push({
        title: new Date().getFullYear(),
        value: new Date().getFullYear()
      });
    } else {
      throw new Error("Failed to fetch get range year data");
    }
  } catch (error) {
    throw new Error("Failed to fetch get range year data");
  }
};

const IDRFormat = (data) => {
  return 'Rp. ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(data).replace('Rp', '').trim()
}

onMounted(() => {
  fetchRangeYear();
})

const updateSnackbarResponse = res => {
  isSnackbarResponse.value = res;
}

const updateSnackbarResponseAlertColor = color => {
  isSnackbarResponseAlertColor.value = color;
}

const alertErrorResponse = () => {
  fetchTrigger.value += 1;
  isSnackbarResponse.value = true;
  isSnackbarResponseAlertColor.value = 'error'
}

const alertSuccessResponse = () => {
  fetchTrigger.value += 1;
  isSnackbarResponse.value = true;
  isSnackbarResponseAlertColor.value = 'success'
}

const updateErrorMessages = err => {
  errorMessages.value = err;
}

const updateErrors = err => {
  errors.value = err;
}

const onUpdateTypeDialog = () => {
  isTypeDialog.value = '';
}

const fetchAddData = async (jobTypeData, clearedForm) => {
  try {
    const response = await $api('/apps/budget-bu/add', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(jobTypeData),
      onResponseError({ response }) {
        alertErrorResponse()
        const responseData = response._data;
        const responseMessage = responseData.message;
        const responseErrors = responseData.errors;
        errors.value = responseErrors;
        errorMessages.value = responseMessage;
        throw new Error("Created data failed");
      },
    });

    const responseStringify = JSON.stringify(response);
    const responseParse = JSON.parse(responseStringify);

    if(responseParse?.status == 200) {
      clearedForm()
      fetchBudgetBU()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isDialogVisible.value = false
    } else {
      alertErrorResponse()
      throw new Error("Created data failed");
    }
  } catch (error) {
    alertErrorResponse()
    throw new Error("Created data failed");
  }
}

const fetchUpdateData = async (id, formData, clearedForm) => {
  try {
    const response = await $api(`/apps/budget-bu/update/${id}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData),
      onResponseError({ response }) {
        alertErrorResponse()
        const responseData = response._data;
        const responseMessage = responseData.message;
        const responseErrors = responseData.errors;
        errors.value = responseErrors;
        errorMessages.value = responseMessage;
        throw new Error("Updated data failed");
      },
    });
    
    const responseStringify = JSON.stringify(response);
    const responseParse = JSON.parse(responseStringify);

    if(responseParse?.status == 200) {
      clearedForm()
      fetchBudgetBU()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isDialogVisible.value = false
    } else {
      alertErrorResponse()
      throw new Error("Updated data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const handleFormSubmit = async ({mode, formData, dialogUpdate}) => {
  if (mode === "Add") {
    fetchAddData(formData, dialogUpdate)
  } else if(mode === 'Edit') {
    fetchUpdateData(IDBU.value, formData, dialogUpdate)
  }
}

const openDialog = async ({ id = null, type, year = null }) => {
  isTypeDialog.value = type
  isDialogVisible.value = true
  if(type == 'Edit') {
    IDBU.value = id;
    yearData.value = year;
  }
  fetchTrigger.value += 1;
}
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardText>
        <VRow>
          <!-- Select Year -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedYears"
              placeholder="Select year"
              :items="dataRangeYear"
              clearable
              prepend-inner-icon="tabler-calendar-month"
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
              prepend-inner-icon="tabler-search"
            />
          </div>
          <!-- Create New Renewal button -->
          <VBtn
            color="primary"
            prepend-icon="tabler-plus"
            @click="openDialog({type:'Add'})"
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
        item-value="id"
        :items-length="totalBudgetBU"
        :headers="headers"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <!-- Year -->
        <template #item.join_first_bgt_year="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_first_bgt_year || '-' }}
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
            {{ IDRFormat(item.join_first_bgt_amount || 0) }}
          </div>
        </template>
        
        <!-- Expense -->
        <template #item.join_first_bgt_expense="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ IDRFormat(item.join_first_bgt_expense || 0) }}
          </div>
        </template>

        <!-- Balance -->
        <template #item.join_first_bgt_balance="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ IDRFormat(item.join_first_bgt_balance || 0) }}
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="openDialog({id: item.number, type: 'Edit', year: item.join_first_bgt_year })">
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
  <BUAddDialog
    v-model:isDialogVisible="isDialogVisible"
    :errors="errors"
    :typeDialog="isTypeDialog"
    :BuId="IDBU"
    :year-data="yearData"
    :fetch-trigger="fetchTrigger"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @BUData="handleFormSubmit"
    @errorMessages="updateErrorMessages"
    @errors="updateErrors"
    @updateTypeDialog="onUpdateTypeDialog"
  />

  <VSnackbar
    v-model="isSnackbarResponse"
    transition="scroll-y-reverse-transition"
    location="top end"
    variant="flat"
    :color="isSnackbarResponseAlertColor"
  >
    {{ isSnackbarResponseAlertColor == 'error' ? errorMessages : successMessages }}
    <template #actions>
      <VBtn
        color="white"
        @click="isSnackbarResponse = false"
      >
        Close
      </VBtn>
    </template>
  </VSnackbar>

</template>
