<script setup>
import dayjs from "dayjs";
import { computed } from 'vue';

const emit = defineEmits(['updateTotalNotPriority','updateTotalPriority'])
const searchQuery = ref('')
const selectedPriority = ref()
const selectedStatus = ref()
const selectedExpiredStatus = ref()
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const isPPSOngoingAddDialogVisible = ref(false)
const isPPSOngoingDialogDeleteVisible = ref(false)
const isPPSOngoingTypeDialog = ref('Add')
const IDPPSOngoing = ref(0)
const isSnackbarResponse = ref(false)
const isSnackbarResponseAlertColor = ref('error')
const fetchTrigger = ref(0);
const errorMessages = ref('Internal server error')
const successMessages = ref('Successfully')
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
  data: ppsData,
  execute: fetchPPS,
} = await useApi(createUrl('/apps/pps-ongoing/search', {
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

const pps = computed(() => ppsData.value.pps)
const totalPPS = computed(() => ppsData.value.totalPPS)
const totalPriorityCount = computed(() => ppsData.value.totalPriorityCount)
const totalNotPriorityCount = computed(() => ppsData.value.totalNotPriorityCount)

watch(
  [totalNotPriorityCount, totalPriorityCount],
  ([newNotPriorityStatus, newPriorityStatus]) => {
    emit('updateTotalNotPriority', newNotPriorityStatus);
    emit('updateTotalPriority', newPriorityStatus);
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

const openDialog = async ({ id = null, type }) => {
  isPPSOngoingTypeDialog.value = type
  isPPSOngoingAddDialogVisible.value = true
  if(type == 'Edit')
    IDPPSOngoing.value = id
    fetchTrigger.value += 1;
}

const openDialogDelete = async (id) => {
  IDPPSOngoing.value = id
  isPPSOngoingDialogDeleteVisible.value = true
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

const resolvePPSPriorityVariant = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === '1')
    return 'success'

  return 'error'
}

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
}

const fetchAddData = async (PPSData, clearedForm) => {
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
      clearedForm()
      fetchPPS()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isPPSOngoingAddDialogVisible.value = false
    } else {
      alertErrorResponse()
      throw new Error("Created data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const fetchPPSOngoingUpdate = async (id, PPSData, clearedForm) => {
  try {
    const idPPSOngoing = Number(id);
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
    const response = await $api(`/apps/pps-ongoing/update/${idPPSOngoing}`, {
        method: 'PUT',
        body: formData,
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
      fetchPPS()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isPPSOngoingAddDialogVisible.value = false
      alertSuccessResponse()
    } else {
      alertErrorResponse()
      throw new Error("Updated data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const deletedPPSOngoing = async id => {
  try {
    isPPSOngoingTypeDialog.value = 'Delete'
    const idPPSOngoing = Number(id);
    const response = await $api(`/apps/pps-ongoing/delete/${idPPSOngoing}`, {
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
      fetchPPS()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isPPSOngoingDialogDeleteVisible.value = false;
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
    fetchPPSOngoingUpdate(IDPPSOngoing.value, formData, dialogUpdate)
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
          <VCol cols="12" sm="4">
            <AppSelect
              v-model="selectedPriority"
              placeholder="Select Priority"
              :items="priority"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
          <!-- Select Status -->
          <VCol cols="12" sm="4">
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

          <!-- Create New PPS button -->
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
        :items="pps"
        item-value="con_id"
        :items-length="totalPPS"
        :headers="headers"
        class="text-no-wrap"
        show-select
        expand-on-click
        @update:options="updateOptions"
      >
      <!-- Expanded Row Data -->
        <template #expanded-row="slotProps">
          <tr class="v-data-table__tr">
            <td :colspan="headers.length">
              <p class="my-1">
                Comment Reject App 1 : {{ slotProps.item.con_comment_coo_reject ?? '-' }}
              </p>
              <p class="my-1">
                Comment Reject App 2 : {{ slotProps.item.con_comment_ceo ?? '-' }}
              </p>
              <p>Comment Reject PBL : {{ slotProps.item.con_comment_pbl_reject ?? '-' }}</p>
            </td>
          </tr>
        </template>

        <!-- PPS -->
        <template #item.con_req_no="{ item }">
          <div class="d-flex align-center gap-x-4">
            <div class="d-flex flex-column">
              <h6 class="text-base">
                <RouterLink
                  :to="{ name: 'apps-user-view-id', params: { id: item.con_id } }"
                  class="font-weight-medium text-link"
                >
                  {{ item.con_req_no }}
                </RouterLink>
              </h6>
              <div class="text-sm">
                {{ item.aud_user ?? '-'}}
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
            :color="resolvePPSPriorityVariant(item.con_priority_id)"
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
                <VListItem :to="{ name: 'apps-user-view-id', params: { id: item.con_id } }">
                  <template #prepend>
                    <VIcon icon="tabler-file-pencil" />
                  </template>
                  <VListItemTitle>Edit Jobtype</VListItemTitle>
                </VListItem>
                <VListItem link>
                  <template #prepend>
                    <VIcon icon="tabler-eye-edit" />
                  </template>
                  <VListItemTitle>Edit Request</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Show More Action</span>
            </VTooltip>
          </VBtn>
          <VBtn
            icon
            variant="text"
            color="medium-emphasis"
          >
            <VIcon icon="tabler-message-2" />
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Show Comment</span>
            </VTooltip>
          </VBtn>
          <IconBtn @click="openDialogDelete(item.con_id)">
            <VIcon icon="tabler-trash" />
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Delete data</span>
            </VTooltip>
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalPPS"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
  <PPSOngoingAddDialog
    v-model:isDialogVisible="isPPSOngoingAddDialogVisible"
    :errors="errors"
    :typeDialog="isPPSOngoingTypeDialog"
    :ppsongoing-id="IDPPSOngoing"
    :fetch-trigger="fetchTrigger"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @PPSData="handleFormSubmit"
    @errorMessages="updateErrorMessages"
    @errors="updateErrors"
  />
  <PPSOngoingDeleteDialog
    v-model:isDialogDeleteVisible="isPPSOngoingDialogDeleteVisible"
    :ppsongoing-id="IDPPSOngoing"
    :fetch-trigger="fetchTrigger"
    @idDeleted="deletedPPSOngoing"
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
