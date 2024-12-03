<script setup>
import dayjs from "dayjs";

const emit = defineEmits(['updateTotalNotExpired','updateTotalExpired'])
// Store
const searchQuery = ref('')
const selectedExpiredStatus = ref()
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
    title: 'Comment',
    key: 'con_comment_bu',
    sortable: false,
  },
]

const {
  data: approvalLvl1DataCompleted,
  execute: fetchApprovalLvl1Completed,
} = await useApi(createUrl('/apps/approval-lvl1-completed/search', {
  query: {
    qCompleted: searchQuery,
    expiredStatus: selectedExpiredStatus,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const approvalLvl1Completed = computed(() => approvalLvl1DataCompleted.value.approvalLvl1Completed)
const totalApprovalLvl1Completed = computed(() => approvalLvl1DataCompleted.value.totalApprovalLvl1Completed)
const totalExpiredCount = computed(() => approvalLvl1DataCompleted.value.totalExpiredCount)
const totalNotExpiredCount = computed(() => approvalLvl1DataCompleted.value.totalNotExpiredCount)
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
        :items="approvalLvl1Completed"
        item-value="con_id"
        :items-length="totalApprovalLvl1Completed"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Approval Lvl1 -->
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
                {{ item.aud_user == '' || null ? '-' : item.aud_user }}
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
            {{ (item.join_fourth_spk_no == null ? '-' : item.join_fourth_spk_no) + 
            (item.join_fourth_spk_renewal_box == null ? '' : item.join_fourth_spk_renewal_box) }}
          </div>
        </template>

        <!-- Comment -->
        <template #item.con_comment_bu="{ item }">
          <div class="text-body-1 text-wrap text-high-emphasis text-capitalize">
            {{ item.con_comment_bu != null || item.con_comment_bu != '' ? item.con_comment_bu : '-' }}
          </div>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalApprovalLvl1Completed"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
</template>
