<script setup>
import dayjs from "dayjs";
import { watch } from "vue";

const emit = defineEmits(['updateTotalNotActive','updateTotalActive'])
// Store
const searchQuery = ref('')
const selectedStatus = ref()
// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const isManDaysDialogVisible = ref(false)
const isManDaysDialogDeleteVisible = ref(false)
const isManDaysTypeDialog = ref('Add')
const IDManDays = ref(0)
const isSnackbarResponse = ref(false)
const isSnackbarResponseAlertColor = ref('error')
const fetchTrigger = ref(0);
const errorMessages = ref('Internal server error')
const successMessages = ref('Successfully')
const errors = ref({
  laborType: undefined,
  rate: undefined,
  effectiveDate: undefined,
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
    title: 'Effective Date',
    key: 'rtk_effective_date',
    sortable: false,
  },
  {
    title: 'Labor Type',
    key: 'rtk_id_jenis_tk',
  },
  {
    title: 'Rate',
    key: 'rtk_rate',
    sortable: false,
  },
  {
    title: 'Status',
    key: 'rtk_active_status',
    sortable: false,
  },
]

// search filters
const status = [
  {
    title: 'Active',
    value: 'Active',
  },
  {
    title: 'Not Active',
    value: 'Non Active',
  },
]

const {
  data: manDaysData,
  execute: fetchManDays,
} = await useApi(createUrl('/configurations/man-days-rate/search', {
  query: {
    q: searchQuery,
    status: selectedStatus,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const manDays = computed(() => manDaysData.value.manDays)
const totalManDays = computed(() => manDaysData.value.totalManDays)
const totalActiveCount = computed(() => manDaysData.value.totalActiveCount)
const totalNotActiveCount = computed(() => manDaysData.value.totalNotActiveCount)

watch(
  [totalNotActiveCount, totalActiveCount],
  ([newNotActiveStatus, newActiveStatus]) => {
    emit('updateTotalNotActive', newNotActiveStatus);
    emit('updateTotalActive', newActiveStatus);
  },
  { immediate: true }
);

const openDialog = async ({ id = null, type }) => {
  isManDaysTypeDialog.value = type
  isManDaysDialogVisible.value = true
  if(type == 'Edit')
    IDManDays.value = id
    fetchTrigger.value += 1;
}

const openDialogDelete = async (id) => {
  IDManDays.value = id
  isManDaysDialogDeleteVisible.value = true
}

const resolveManDaysStatusVariant = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'active')
    return 'success'

  return 'error'
}

const updateSnackbarResponse = res => {
  isSnackbarResponse.value = res;
}

const updateSnackbarResponseAlertColor = color => {
  isSnackbarResponseAlertColor.value = color;
}

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
}

const IDRFormat = (data) => {
  return 'Rp. ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(data).replace('Rp', '').trim()
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

const fetchAddData = async (manDaysData, clearedForm) => {
  try {
      const response = await $api('/configurations/man-days-rate/add', {
        method: 'POST',
        body: JSON.stringify(manDaysData),
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
      fetchManDays()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isManDaysDialogVisible.value = false
    } else {
      alertErrorResponse()
      throw new Error("Created data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const fetchMandaysUpdate = async (id, formData, clearedForm) => {
  try {
    const response = await $api(`/configurations/man-days-rate/update/${id}`, {
        method: 'PUT',
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
      fetchManDays()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isManDaysDialogVisible.value = false
    } else {
      alertErrorResponse()
      throw new Error("Updated data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const deleteManDays = async id => {
  try {
    isManDaysTypeDialog.value = 'Delete'
    const idManDays = Number(id);
    const response = await $api(`/configurations/man-days-rate/delete/${idManDays}`, {
        method: 'DELETE',
        onResponseError({ response }) {
        alertErrorResponse()
        const responseData = response._data;
        const responseMessage = responseData.message;
        const responseErrors = responseData.errors;
        errors.value = responseErrors;
        errorMessages.value = responseMessage;
        throw new Error("Deleted data failed");
      },
    })
    const responseStringify = JSON.stringify(response);
    const responseParse = JSON.parse(responseStringify);

    if(responseParse?.status == 200) {
      fetchManDays()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isManDaysDialogDeleteVisible.value = false;
    } else {
      alertErrorResponse()
      throw new Error("Deleted data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const handleFormSubmit = async ({mode, formData, dialogUpdate}) => {
  if (mode === "Add") {
    fetchAddData(formData, dialogUpdate)
  } else if(mode === 'Edit') {
    fetchMandaysUpdate(IDManDays.value, formData, dialogUpdate)
  }
}
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardText>
        <VRow>
          <!-- Select Status -->
          <VCol cols="12" sm="4">
            <AppSelect
              v-model="selectedStatus"
              placeholder="Select status"
              :items="status"
              clearable
              prepend-inner-icon="tabler-filter-search"
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

          <!-- Export button -->
          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-upload"
          >
            Export
          </VBtn>
          <!-- Create New PBL button -->
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
        :items="manDays"
        item-value="rtk_id"
        :items-length="totalManDays"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Effective Date -->
        <template #item.rtk_effective_date="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ formatDate(item.rtk_effective_date) }}
          </div>
        </template>

        <!-- Labor Type -->
        <template #item.rtk_id_jenis_tk="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.rtk_id_jenis_tk }}
          </div>
        </template>

        <!-- Business Unit -->
        <template #item.con_bu="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.con_bu + ' - ' + item.join_second_description  }}
          </div>
        </template>

        <!-- Rate -->
        <template #item.rtk_rate="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ IDRFormat(item.rtk_rate) }}
          </div>
        </template>

        <!-- Status -->
        <template #item.rtk_active_status="{ item }">
          <VChip
            :color="resolveManDaysStatusVariant(item.rtk_active_status)"
            size="small"
            label
            class="text-capitalize"
          >
            {{ item.rtk_active_status }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="openDialog({id: item.rtk_id, type: 'Edit'})">
            <VIcon icon="tabler-edit" />
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Edit data</span>
            </VTooltip>
          </IconBtn>
          <IconBtn @click="openDialogDelete(item.rtk_id)">
            <VIcon icon="tabler-trash" />
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Edit data</span>
            </VTooltip>
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalManDays"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
  <ManDaysAddDialog
    v-model:isDialogVisible="isManDaysDialogVisible"
    :errors="errors"
    :typeDialog="isManDaysTypeDialog"
    :mandays-id="IDManDays"
    :fetch-trigger="fetchTrigger"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @ManDaysData="handleFormSubmit"
    @errorMessages="updateErrorMessages"
    @errors="updateErrors"
  />
  <ManDaysDeleteDialog
    v-model:isDialogDeleteVisible="isManDaysDialogDeleteVisible"
    :mandays-id="IDManDays"
    :fetch-trigger="fetchTrigger"
    @id-deleted="deleteManDays"
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
