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
const isPBLAddDialogVisible = ref(false)

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

const deletePBL = async id => {
  await $api(`/apps/pbl/${ id }`, { method: 'DELETE' })

  // Delete from selectedRows
  const index = selectedRows.value.findIndex(row => row === id)
  if (index !== -1)
    selectedRows.value.splice(index, 1)

  // Refetch PBL
  fetchPBL()
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
            @click="isPBLAddDialogVisible = !isPBLAddDialogVisible"
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
            {{ item.con_comment_bu != null || item.con_comment_bu != '' ? item.con_comment_bu : '-' }}
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
            :total-items="totalPBL"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
  <PBLAddDialog
    v-model:isDialogVisible="isPBLAddDialogVisible"
    :user-data="customerData"
  />
</template>
