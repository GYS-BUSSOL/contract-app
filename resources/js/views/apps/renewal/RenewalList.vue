<script setup>
import dayjs from "dayjs";

const emit = defineEmits(['updateTotalNotPriority','updateTotalPriority'])
// Store
const searchQuery = ref('')
const selectedPriority = ref()
const selectedStatus = ref()
const selectedExpiredStatus = ref()
// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const isAddDialogVisible = ref(false)
const isListJobTypeDialogVisible = ref(false)
const isJobTypeDetailDialogVisible = ref(false)
const isDeleteDialogVisible = ref(false)
const isTypeDialog = ref('Add')
const conId = ref()
const conReqId = ref()
const isSnackbarResponse = ref(false)
const isSnackbarResponseAlertColor = ref('error')
const fetchTrigger = ref(0);
const errorMessages = ref('Internal server error')
const successMessages = ref('Successfully')
const rangeIncrement = ref([])
const isDisabled = ref(false)
const isSuccessNextStep = ref(false)
const cjbId = ref()

const errors = ref({
   // PPS form
  company: undefined,
  bu: undefined,
  cc: undefined,
  wc: undefined,
  work_location: undefined,
  id_project: undefined,
  pps_no: undefined,
  old_pps_no: undefined,
  priority: undefined,
  shift_checklist: undefined,
  cp_name: undefined,
  cp_dept: undefined,
  cp_ext: undefined,
  cp_email: undefined,
  comment: undefined,
  duration: undefined,
  suggest_vendor: undefined,
  con_comment_jobtarget: undefined,
  file_attachment: undefined,
  // Job Type form
  job_type: undefined,
  job_desc: undefined,
  pic: undefined,
  payment_type: undefined,
  total_job_target_qty: undefined,
  uom: undefined,
  cjt_type: undefined,
  cjt_qty: undefined,
  total: undefined,
  labor_type: undefined,
  labor_qty: undefined,
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
    title: 'Request No',
    key: 'con_req_no',
  },
  {
    title: 'Request Date',
    key: 'con_req_date',
  },
  {
    title: 'Business Unit',
    key: 'con_bu',
  },
  {
    title: 'PPS No',
    key: 'con_pps_no',
  },
  {
    title: 'Status',
    key: 'join_first_sts_description',
    sortable: false,
  },
  {
    title: 'Priority',
    key: 'con_priority_id',
    sortable: false,
  },
  {
    title: 'Planning Duration',
    key: 'con_duration_start',
    sortable: false,
  },
]

// search filters
const expiredStatus = [
  {
    title: 'Expired data',
    value: '1',
  },
  {
    title: 'Not expired yet data',
    value: 'null',
  }
]

const priority = [
  {
    title: 'Tidak Segera',
    value: '2',
  },
  {
    title: 'Segera',
    value: '1',
  }
]

const status = [
  {
    title: 'New Request',
    value: 'New Request',
  },
  {
    title: 'Vendor Assignment Rejected',
    value: 'Vendor Assignment Rejected',
  },
  {
    title: 'Approval 1 Rejected',
    value: 'Approval 1 Rejected',
  },
  {
    title: 'Approval 2 Rejected',
    value: 'Approval 2 Rejected',
  },
  {
    title: 'Approval 1 Approved',
    value: 'Approval 1 Approved',
  },
  {
    title: 'Approval 2 Approved',
    value: 'Approval 2 Approved',
  },
  {
    title: 'Rate Assigned',
    value: 'Rate Assigned',
  },
  {
    title: 'SPK Printed',
    value: 'SPK Printed',
  },
  {
    title: 'Canceled',
    value: 'Canceled',
  }
]

const {
  data: renewalsData,
  execute: fetchRenewal,
} = await useApi(createUrl('/apps/renewal/search', {
  query: {
    q: searchQuery,
    status: selectedStatus,
    priority: selectedPriority,
    expiredStatus: selectedExpiredStatus,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const renewals = computed(() => renewalsData.value.renewals)
const totalRenewals = computed(() => renewalsData.value.totalRenewals)
const totalPriorityCount = computed(() => renewalsData.value.totalPriorityCount)
const totalNotPriorityCount = computed(() => renewalsData.value.totalNotPriorityCount)

watch(
  [totalNotPriorityCount, totalPriorityCount],
  ([newNotPriorityStatus, newPriorityStatus]) => {
    emit('updateTotalNotPriority', newNotPriorityStatus);
    emit('updateTotalPriority', newPriorityStatus);
  },
  { immediate: true }
)

const resolveRenewalsPriorityVariant = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === '1')
    return 'success'

  return 'error'
}

const updateSnackbarResponse = res => {
  isSnackbarResponse.value = res;
}

const updateSnackbarResponseAlertColor = color => {
  isSnackbarResponseAlertColor.value = color;
}

const updateErrorMessages = err => {
  errorMessages.value = err;
}

const updateErrors = err => {
  errors.value = err;
}

const updateRangeIncrement = data => {
  rangeIncrement.value = data
}

const updateAddDialogVisible =  async ({type, stat, cjb_id = null}) => {
  isAddDialogVisible.value = stat
  isTypeDialog.value = type
  if(type == 'Add Job Type' || type == 'Edit Job Type')
    cjbId.value = cjb_id
    await getIncrementPPSOngoing(conReqId.value)
}

const updateDeleteDialogVisible =  async ({type, stat, cjb_id = null}) => {
  isDeleteDialogVisible.value = stat
  cjbId.value = cjb_id
}

const fetchAddPPSData = async (PPSData) => {
  try {
      const formData = new FormData();
      
      for (const [key, value] of Object.entries(PPSData)) {
        if (value instanceof File) {
          formData.append(key, value);
        } else if (typeof value === 'object' && value !== null) {
          formData.append(key, JSON.stringify(value));
        } else {
          formData.append(key, value);
        }
      }
      const response = await $api('/apps/pps-ongoing/add', {
        method: 'POST',
        body: formData,
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
      fetchPPS()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      const data = responseParse?.data.rows;
      conReqId.value = data.con_req_id;
      await getIncrementPPSOngoing(conReqId)
      successMessages.value = responseMessage;
      isSuccessNextStep.value = true;
    } else {
      alertErrorResponse()
      throw new Error("Created data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const fetchAddJobTypeData = async (type, JobTypeData, clearedForm, clearedJobtypeForm) => {
  try {
      const formData = new FormData();
      
      for (const [key, value] of Object.entries(JobTypeData)) {
        if (value instanceof File) {
          formData.append(key, value);
        } else if (typeof value === 'object' && value !== null) {
          formData.append(key, JSON.stringify(value));
        } else {
          formData.append(key, value);
        }
      }
      formData.append('con_id',conId.value)
      formData.append('periode',rangeIncrement.value)
      console.log({rangeIncrement: rangeIncrement.value, con_id: conId.value});
      const response = await $api('/apps/trn-job-type/add', {
        method: 'POST',
        body: formData,
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
      if(type == 'Save') {
        clearedForm()
        isAddDialogVisible.value = false
      } else if(type == 'Continue') {
        clearedJobtypeForm()
        isDisabled.value = false;
      }
      fetchPPS()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;      
      successMessages.value = responseMessage;
    } else {
      alertErrorResponse()
      throw new Error("Created data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const deletedJobType = async id => {
  try {
    const cjbId = Number(id);
    const response = await $api(`/apps/trn-job-type/delete/${cjbId}`, {
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
      isTypeDialog.value = 'Delete'
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isDeleteDialogVisible.value = false;
    } else {
      alertErrorResponse()
      throw new Error("Deleted data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const handlePPSFormSubmit = async ({mode, formData, dialogUpdate}) => {
  if (mode === "Add") {
    fetchAddPPSData(formData)
  }
}

const handleJobTypeFormSubmit = async ({type, formData, dialogUpdate, dialogJobtypeUpdate}) => {
    fetchAddJobTypeData(type, formData, dialogUpdate, dialogJobtypeUpdate)
}

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
}

const openDialog = async ({ id = null, type, con_req_id = null }) => {
  isTypeDialog.value = type
  if(type == 'Edit' || type == 'Add') {
    isAddDialogVisible.value = true
  } else if (type == 'List Job Type') {
    isListJobTypeDialogVisible.value = true
  } else if(type == 'Detail')
    isJobTypeDetailDialogVisible.value = true
  if(type == 'Edit' || type == 'List Job Type' || type == 'Detail' ) {
    conId.value = id;
    conReqId.value = con_req_id
    fetchTrigger.value += 1
  }
}

</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filters</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <!-- Select Expired Status -->
          <VCol cols="12" sm="4">
            <AppSelect
              v-model="selectedExpiredStatus"
              placeholder="Select expired status"
              :items="expiredStatus"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
          <!-- Select Priority -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedPriority"
              placeholder="Select Priority"
              :items="priority"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
          <!-- Select Status -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedStatus"
              placeholder="Select Status"
              :items="status"
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
        :items="renewals"
        item-value="con_id"
        :items-length="totalRenewals"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Renewal -->
        <template #item.con_req_no="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <span style="cursor: pointer;" class="text-base">
                {{ item.con_req_no }}
              </span>
              <div class="text-sm">
                {{ item.aud_user != '' && item.aud_user != null ? item.aud_user : '-' }}
              </div>
            </div>
          </div>
        </template>

        <!-- Request Date -->
        <template #item.con_req_date="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ formatDate(item.con_req_date) }}
          </div>
        </template>

        <!-- Business Unit -->
        <template #item.con_bu="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.con_bu + ' - ' + item.join_second_description }}
          </div>
        </template>

        <!-- PPS No -->
        <template #item.con_pps_no="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.con_pps_no }}
          </div>
        </template>

        <!-- Status -->
        <template #item.join_first_sts_description="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_first_sts_description }}
          </div>
        </template>

        <!-- Priority -->
        <template #item.con_priority_id="{ item }">
          <VChip
            :color="resolveRenewalsPriorityVariant(item.con_priority_id)"
            size="small"
            label
            class="text-capitalize"
          >
            {{ item.con_priority_id == 1 ? "Segera" : "Tidak Segera" }}
          </VChip>
        </template>

        <!-- Planning Duration -->
        <template #item.con_duration_start="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            <div class="d-flex justify-content-between gap-5">
              <div class="d-flex align-center flex-column">
                <small>Start</small>
                <span>{{ formatDate(item.con_duration_start) }}</span>
              </div>
              <div class="d-flex align-center flex-column">
                <small>End</small>
                <span>{{ formatDate(item.con_duration_end) }}</span>
              </div>
            </div>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <VBtn
            icon
            variant="text"
            color="medium-emphasis"
          >
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem @click="openDialog({id: item.con_id, type: 'List Job Type', con_req_id: item.con_req_id})">
                  <template #prepend>
                    <VIcon icon="tabler-file-pencil" />
                  </template>
                  <VListItemTitle>Edit Job Type</VListItemTitle>
                </VListItem>
                <VListItem @click="openDialog({id: item.con_id, type: 'Edit', con_req_id: item.con_req_id})">
                  <template #prepend>
                    <VIcon icon="tabler-eye-edit" />
                  </template>
                  <VListItemTitle>Edit Request</VListItemTitle>
                </VListItem>
                <VListItem @click="openDialog({id: item.con_id, type: 'Detail', con_req_id: item.con_req_id})">
                  <template #prepend>
                    <VIcon icon="tabler-eye" />
                  </template>
                  <VListItemTitle>Detail</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Show More Action</span>
            </VTooltip>
          </VBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalRenewals"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
  <PPSOngoingAddDialog
    v-model:isDialogVisible="isAddDialogVisible"
    :errors="errors"
    :typeDialog="isTypeDialog"
    :con-id="conId"
    :con-req-id="conReqId"
    :cjb-id="cjbId"
    :fetch-trigger="fetchTrigger"
    :range-increment="rangeIncrement"
    :is-success-next-step="isSuccessNextStep"
    :is-disabled="isDisabled"
    @updateRangeIncrement="updateRangeIncrement"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @PPSData="handlePPSFormSubmit"
    @JobTypeData="handleJobTypeFormSubmit"
    @errorMessages="updateErrorMessages"
    @errors="updateErrors"
  />

  <PPSOngoingListJobtypeDialog
    v-model:isDialogVisible="isListJobTypeDialogVisible"
    :errors="errors"
    :type-dialog="isTypeDialog"
    :contract-req-id="conReqId"
    :fetch-trigger="fetchTrigger"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @errorMessages="updateErrorMessages"
    @errors="updateErrors"
    @isAddDialogVisible="updateAddDialogVisible"
    @isDialogDeleteVisible="updateDeleteDialogVisible"
  />

  <PPSOngoingDetailDialog
    v-model:isDialogVisible="isJobTypeDetailDialogVisible"
    :errors="errors"
    :type-dialog="isTypeDialog"
    :contract-req-id="conReqId"
    :fetch-trigger="fetchTrigger"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @errorMessages="updateErrorMessages"
    @errors="updateErrors"
  />

  <PPSOngoingDeleteDialog
    v-model:isDialogDeleteVisible="isDeleteDialogVisible"
    :cjb-id="cjbId"
    :fetch-trigger="fetchTrigger"
    @idDeleted="deletedJobType"
  />
</template>
