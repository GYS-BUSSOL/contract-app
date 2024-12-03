<script setup>
import dayjs from "dayjs";

const emit = defineEmits(['updateTotalNotExpired','updateTotalExpired'])
// Store
const searchQuery = ref('')
const selectedExpiredStatus = ref()
const selectedPriorityCompleted = ref()
// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])

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
    key: 'join_fifth_spk_no',
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
    title: 'Approval 1 Name',
    key: 'join_third_aud_user',
    sortable: false,
  },
  {
    title: 'Approval Date',
    key: 'join_third_aud_date',
    sortable: false,
  },
  {
    title: 'Planning Duration',
    key: 'con_duration_start',
    sortable: false,
  },
]

const {
  data: approvalLvl2DataCompleted,
  execute: fetchApprovalLvl2Completed,
} = await useApi(createUrl('/apps/approval-lvl2-completed/search', {
  query: {
    qCompleted: searchQuery,
    expiredStatus: selectedExpiredStatus,
    priorityCompleted: selectedPriorityCompleted,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const approvalLvl2Completed = computed(() => approvalLvl2DataCompleted.value.approvalLvl2Completed)
const totalApprovalLvl2Completed = computed(() => approvalLvl2DataCompleted.value.totalApprovalLvl2Completed)
const totalExpiredCount = computed(() => approvalLvl2DataCompleted.value.totalExpiredCount)
const totalNotExpiredCount = computed(() => approvalLvl2DataCompleted.value.totalNotExpiredCount)
emit('updateTotalNotExpired', totalNotExpiredCount.value);
emit('updateTotalExpired', totalExpiredCount.value);

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

const priorityCompleted = [
  {
    title: 'Tidak Segera',
    value: '2',
  },
  {
    title: 'Segera',
    value: '1',
  }
]

const resolveApprovalLvl2PriorityVariant = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === '1')
    return 'success'

  return 'error'
}

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
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
              v-model="selectedPriorityCompleted"
              placeholder="Select Priority"
              :items="priorityCompleted"
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
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="approvalLvl2Completed"
        item-value="con_id"
        :items-length="totalApprovalLvl2Completed"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Approval Level 2 -->
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
        <template #item.join_fifth_spk_no="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ (item.join_fifth_spk_no == null ? '-' : item.join_fifth_spk_no) + 
            (item.join_fifth_spk_renewal_box == null ? '' : item.join_fifth_spk_renewal_box) }}
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
            :color="resolveApprovalLvl2PriorityVariant(item.con_priority_id)"
            size="small"
            label
            class="text-capitalize"
          >
            {{ item.con_priority_id == 1 ? "Segera" : "Tidak Segera" }}
          </VChip>
        </template>

        <!-- Approval 1 Name -->
        <template #item.join_third_aud_user="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_third_aud_user ?? '-' }}
          </div>
        </template>

        <!-- Approval Date -->
        <template #item.join_third_aud_date="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ formatDate(item.join_third_aud_date, true) + " WIB" ?? '-' }}
          </div>
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
          <IconBtn @click="deletePBL(item.con_id)">
            <VIcon icon="tabler-eye" />
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>View Detail</span>
            </VTooltip>
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalApprovalLvl2Completed"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
</template>
