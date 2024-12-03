<script setup>
import VendorCards from '@/views/apps/vendor-assigment/VendorCards.vue';
import dayjs from "dayjs";

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
  data: vendorData,
  execute: fetchVendor,
} = await useApi(createUrl('/apps/vendor-assigment/search', {
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

const vendors = computed(() => vendorData.value.vendors)
const totalVendors = computed(() => vendorData.value.totalVendors)
const totalPriorityCount = computed(() => vendorData.value.totalPriorityCount)
const totalNotPriorityCount = computed(() => vendorData.value.totalNotPriorityCount)

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

const resolveVendorPriorityVariant = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === '1')
    return 'success'

  return 'error'
}

const deleteVendor = async id => {
  await $api(`/apps/vendor-assigment/${ id }`, { method: 'DELETE' })

  // Delete from selectedRows
  const index = selectedRows.value.findIndex(row => row === id)
  if (index !== -1)
    selectedRows.value.splice(index, 1)

  // Refetch Vendor
  fetchVendor()
}

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
}
</script>

<template>
  <section>
    <VRow>
      <VCol cols="12">
        <VendorCards :totalNotPriorityCount="totalNotPriorityCount" :totalPriorityCount="totalPriorityCount" />
      </VCol>
    </VRow>
  </section>
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
              { value: -1, title: 'All' },
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
        :items="vendors"
        item-value="con_id"
        :items-length="totalVendors"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Vendor -->
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

        <!-- Status -->
        <template #item.join_first_sts_description="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_first_sts_description }}
          </div>
        </template>

        <!-- Priority -->
        <template #item.con_priority_id="{ item }">
          <VChip
            :color="resolveVendorPriorityVariant(item.con_priority_id)"
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
          <IconBtn @click="deleteVendor(item.con_id)">
            <VIcon icon="tabler-eye-edit" />
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Vendor Assigment Request</span>
            </VTooltip>
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalVendors"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
</template>
