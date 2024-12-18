<script setup>
import dayjs from "dayjs";

const emit = defineEmits(['updateTotalNotExpired','updateTotalExpired'])
// Store
const searchQuery = ref('')
const selectedStatus = ref()
const selectedExpiredStatus = ref()
// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const isTypeDialog = ref('Add')
const isAddDialogVisible = ref(false)
const isSnackbarResponse = ref(false)
const isJobTypeDetailDialogVisible = ref(false)
const isSnackbarResponseAlertColor = ref('error')
const errorMessages = ref('Internal server error')
const successMessages = ref('Successfully')
const conReqNo = ref(0)
const conReqId = ref(0)
const fetchTrigger = ref(0)
const isSuccessNextStep = ref(false)
const token = useCookie('accessToken')
const errors = ref({
  con_req_id: undefined,
  cjb_pay_type: undefined,
  cjb_pay_template: undefined,
  cjb_rate: undefined,
  cjb_desc: undefined,
  suggest_vendor: undefined,
  duration: undefined,
  signature_type: undefined,
  spk_jobdesc_summary: undefined,
})
const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Request No',
    key: 'con_req_no',
  },
  {
    title: 'PPS No',
    key: 'con_pps_no',
  },
  {
    title: 'Request Date',
    key: 'con_req_date',
  },
  {
    title: 'Level 2 Approved Date',
    key: 'aud_date',
  },
  {
    title: 'Business Unit',
    key: 'con_bu',
  },
  {
    title: 'Vendor',
    key: 'join_third_vnd_name',
  },
  {
    title: 'Last Status',
    key: 'join_first_sts_description',
    sortable: false,
  },
  {
    title: 'Comment',
    key: 'con_comment_bu',
    sortable: false,
  },
]

const {
  data: PBLData,
  execute: fetchPBL,
} = await useApi(createUrl('/apps/pbl/search', {
  query: {
    q: searchQuery,
    status: selectedStatus,
    expiredStatus: selectedExpiredStatus,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const pbl = computed(() => PBLData.value.pbl)
const totalPBL = computed(() => PBLData.value.totalPBL)
const totalExpiredCount = computed(() => PBLData.value.totalExpiredCount)
const totalNotExpiredCount = computed(() => PBLData.value.totalNotExpiredCount)

watch(
  [totalNotExpiredCount, totalExpiredCount],
  ([newNotExpiredStatus, newExpiredStatus]) => {
    emit('updateTotalNotExpired', newNotExpiredStatus);
    emit('updateTotalExpired', newExpiredStatus);
  },
  { immediate: true }
)

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

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
}

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

const updateSucccessStep = stat => {
  isSuccessNextStep.value = stat;
}

const fetchAddData = async (SPKData) => {
  try {
      const response = await $api('/apps/spk/add', {
        method: 'POST',
        body: JSON.stringify(SPKData),
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
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
      fetchPBL()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
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

const openDialog = async ({ id = null, type, con_req_no = null, con_req_id = null }) => {
  isTypeDialog.value = type
  conReqNo.value = con_req_no
  conReqId.value = con_req_id
  if(type == 'Add') {
    isAddDialogVisible.value = true
  } else if(type == 'Detail') {
    isJobTypeDetailDialogVisible.value = true
    fetchTrigger.value += 1;
  }
}

const handleFormSubmit = async ({mode, formData, dialogUpdate}) => {
  if (mode === "Add") {
    fetchAddData(formData, dialogUpdate)
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
          <VCol cols="12" sm="6">
            <AppSelect
              v-model="selectedExpiredStatus"
              placeholder="Select expired status"
              :items="expiredStatus"
              clearable
            />
          </VCol>
          <!-- Select Status -->
          <VCol cols="12" sm="6">
            <AppSelect
              v-model="selectedStatus"
              placeholder="Select last process status"
              :items="status"
              clearable
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
              { value: 100, title: '100' }
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
          <!-- Create New PBL button -->
          <VBtn
            color="primary"
            prepend-icon="tabler-plus"
            @click="openDialog({type: 'Add'})"
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
        :items="pbl"
        item-value="con_id"
        :items-length="totalPBL"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- PBL -->
        <template #item.con_req_no="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base text-primary" style="cursor: pointer;" 
                @click="openDialog({type: 'Detail', con_req_no: item.con_req_no, con_req_id: item.con_req_id})"
              >
                {{ item.con_req_no }}
              </h6>
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

        <!-- Approved Date -->
        <template #item.aud_date="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ formatDate(item.aud_date, true) }} WIB
          </div>
        </template>

        <!-- Business Unit -->
        <template #item.con_bu="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.con_bu + ' - ' + item.join_second_description  }}
          </div>
        </template>

        <!-- PPS No -->
        <template #item.con_pps_no="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.con_pps_no }}
          </div>
        </template>

        <!-- Last Status -->
        <template #item.join_first_sts_description="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_first_sts_description }}
          </div>
        </template>

        <!-- Vendor Name -->
        <template #item.join_third_vnd_name="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_third_vnd_name }}
          </div>
        </template>

        <!-- Comment -->
        <template #item.con_comment_bu="{ item }">
          <div class="text-body-1 text-wrap text-high-emphasis text-capitalize">
            {{ item.con_comment_bu != null && item.con_comment_bu != '' ? item.con_comment_bu : '-' }}
          </div>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalPBL"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
  
  <PBLAddDialog
    v-model:isDialogVisible="isAddDialogVisible"
    :errors="errors"
    :type-dialog="isTypeDialog"
    :con-req-no="conReqNo"
    :con-req-id="conReqId"
    :fetch-trigger="fetchTrigger"
    :is-success-next-step="isSuccessNextStep"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @createSPK="handleFormSubmit"
    @errorMessages="updateErrorMessages"
    @errors="updateErrors"
    @isSuccessNextStep="updateSucccessStep"
  />

  <PBLDetailDialog
    v-model:isDialogVisible="isJobTypeDetailDialogVisible"
    :errors="errors"
    :type-dialog="isTypeDialog"
    :con-req-id="conReqId"
    :fetch-trigger="fetchTrigger"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @errorMessages="updateErrorMessages"
    @errors="updateErrors"
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
