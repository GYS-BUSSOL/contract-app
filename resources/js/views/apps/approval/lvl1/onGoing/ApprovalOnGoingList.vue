<script setup>
import dayjs from "dayjs";

const emit = defineEmits(['updateTotalNotPriority','updateTotalPriority'])
// Store
const searchQuery = ref('')
const selectedPriority = ref()
const selectedExpiredStatus = ref()
// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const isAddDialogVisible = ref(false)
const isSnackbarResponse = ref(false)
const isTypeDialog = ref('')
const isSnackbarResponseAlertColor = ref('error')
const errorMessages = ref('Internal server error')
const successMessages = ref('Successfully')
const conReqNo = ref(0)
const conReqId = ref(0)
const conBU = ref('')
const conDurationStart = ref(dayjs().format('YYYY-MMMM-DD'))
const conDurationEnd = ref(dayjs().format('YYYY-MMMM-DD'))
const fetchTrigger = ref(0)
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

// Headers
const headers = [
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
    title: 'SPK No',
    key: 'join_fourth_spk_no',
  },
  {
    title: 'Status',
    key: 'join_first_sts_description',
  },
  {
    title: 'Priority',
    key: 'con_priority_id',
  },
  {
    title: 'Planning Duration',
    key: 'con_duration_start',
    sortable: false,
  },
]

const {
  data: approvalLvl1Data,
  execute: fetchApproval1Ongoing,
} = await useApi(createUrl('/apps/approval-lvl1-ongoing/search', {
  query: {
    q: searchQuery,
    priority: selectedPriority,
    expiredStatus: selectedExpiredStatus,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const approvalLvl1 = computed(() => approvalLvl1Data.value.approvalLvl1)
const totalApprovalLvl1 = computed(() => approvalLvl1Data.value.totalApprovalLvl1)
const totalPriorityCount = computed(() => approvalLvl1Data.value.totalPriorityCount)
const totalNotPriorityCount = computed(() => approvalLvl1Data.value.totalNotPriorityCount)

watch(
  [totalNotPriorityCount, totalPriorityCount],
  ([newNotPriorityStatus, newPriorityStatus]) => {
    emit('updateTotalNotPriority', newNotPriorityStatus);
    emit('updateTotalPriority', newPriorityStatus);
  },
  { immediate: true }
)

const resolveApprovalLvl1PriorityVariant = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === '1')
    return 'warning'

  return 'success'
}

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
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

const updateErrors = err => {
  errors.value = err;
}

const totalDays = computed(() => {
  if (!conDurationStart.value || !conDurationEnd.value) {
    return 0;
  }

  const start = new Date(conDurationStart.value);
  const end = new Date(conDurationEnd.value);
  end.setDate(end.getDate() + 1);
  const diffTime = end - start;
  return diffTime > 0 ? Math.ceil(diffTime / (1000 * 60 * 60 * 24)) : 0;
})

const fetchApprove = async (payload, clearedForm) => {
  try {
      const response = await $api('/apps/approval-lvl1-approve/add', {
        method: 'POST',
        body: payload,
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
          throw new Error("Updated data failed");
        },
      });

    const responseStringify = JSON.stringify(response);
    const responseParse = JSON.parse(responseStringify);

    if(responseParse?.status == 200) {
      clearedForm()
      fetchApproval1Ongoing()
      alertSuccessResponse()
      isAddDialogVisible.value = false
      const responseMessage = responseParse?.message;      
      successMessages.value = responseMessage;
    } else {
      alertErrorResponse()
      throw new Error("Updated data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const fetchReject = async (payload, clearedForm) => {
  try {
      const response = await $api('/apps/approval-lvl1-reject/add', {
        method: 'POST',
        body: payload,
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
          throw new Error("Updated data failed");
        },
      });

    const responseStringify = JSON.stringify(response);
    const responseParse = JSON.parse(responseStringify);

    if(responseParse?.status == 200) {
      clearedForm()
      fetchApproval1Ongoing()
      alertSuccessResponse()
      isAddDialogVisible.value = false
      const responseMessage = responseParse?.message;      
      successMessages.value = responseMessage;
    } else {
      alertErrorResponse()
      throw new Error("Updated data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const handleFormSubmit = async ({type, payload, dialogUpdate}) => {
  if(type == 'Approve') {
    fetchApprove(payload, dialogUpdate)
  } else if(type == 'Reject') {
    fetchReject(payload, dialogUpdate)
  }
}

const openDialog = async ({ type, item }) => {
  isTypeDialog.value = type
  conReqNo.value = item.con_req_no
  conReqId.value = item.con_req_id
  conBU.value = item.con_bu
  conDurationStart.value = item.con_duration_start
  conDurationEnd.value = item.con_duration_end

  if(type == 'Add') {
    isAddDialogVisible.value = true
    fetchTrigger.value += 1;
  }
}

</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardText>
        <VRow>
          <!-- Select Expired Status -->
          <VCol cols="12" sm="4">
            <AppSelect
              v-model="selectedExpiredStatus"
              placeholder="Select expired status"
              :items="expiredStatus"
              clearable
              prepend-inner-icon="tabler-filter-search"
            />
          </VCol>
          <!-- Select Priority -->
          <VCol cols="12" sm="4">
            <AppSelect
              v-model="selectedPriority"
              placeholder="Select Priority"
              :items="priority"
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
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="approvalLvl1"
        item-value="con_id"
        :items-length="totalApprovalLvl1"
        :headers="headers"
        class="text-no-wrap"
        @update:options="updateOptions"
      >

        <!-- Approval Level 1 -->
        <template #item.con_req_no="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base text-primary cursor-pointer"
                @click="openDialog({type: 'Add', item})"
              >
                {{ item.con_req_no }}
              </h6>
              <div class="text-sm">
                {{ item.aud_user != null && item.aud_user != '' ? item.aud_user : '-'}}
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
            {{ item.con_bu + '-' + item.join_second_description }}
          </div>
        </template>

        <!-- PPS No -->
        <template #item.con_pps_no="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.con_pps_no }}
          </div>
        </template>

        <!-- SPK No -->
        <template #item.join_fourth_spk_no="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ (item.join_fourth_spk_no ?? '-') + 
            (item.join_fourth_spk_renewal_box ?? '') }}
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
            :color="resolveApprovalLvl1PriorityVariant(item.con_priority_id)"
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

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalApprovalLvl1"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>

  <Approval1AddDialog
    v-model:isDialogVisible="isAddDialogVisible"
    :errors="errors"
    :type-dialog="isTypeDialog"
    :con-req-id="conReqId"
    :con-bu="conBU"
    :con-duration-start="conDurationStart"
    :con-duration-end="conDurationEnd"
    :total-days="totalDays"
    :fetch-trigger="fetchTrigger"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @ApprovalData="handleFormSubmit"
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
