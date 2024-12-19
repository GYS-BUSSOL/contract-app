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
const isPBLAddDialogVisible = ref(false)
const token = useCookie('accessToken')
const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'SPK No',
    key: 'join_second_spk_no',
  },
  {
    title: 'Request No',
    key: 'arr_con_req_no',
  },
  {
    title: 'PPS No',
    key: 'arr_con_pps_no',
  },
  {
    title: 'SPK Date',
    key: 'join_second_spk_date',
  },
  {
    title: 'Date Duration',
    key: 'join_second_spk_start_date',
  },
  {
    title: 'Vendor Name',
    key: 'join_third_vnd_name',
  },
  {
    title: 'Vendor PIC',
    key: 'join_third_vnd_contact_person',
  },
  {
    title: 'Job Description',
    key: 'join_second_spk_jobdesc_summary',
  },
]

const {
  data: SPKListData,
  execute: fetchSPKList,
} = await useApi(createUrl('/apps/spk-list/search', {
  query: {
    q: searchQuery,
    expiredStatus: selectedExpiredStatus,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
    token
  },
}))

const SPKList = computed(() => SPKListData.value.SPKList)
const totalSPKList = computed(() => SPKListData.value.totalSPKList)
const totalExpiredCount = computed(() => SPKListData.value.totalExpiredCount)
const totalNotExpiredCount = computed(() => SPKListData.value.totalNotExpiredCount)
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

const deleteSPKList = async id => {
  await $api(`/apps/spk-list/${ id }`, { method: 'DELETE' })

  // Delete from selectedRows
  const index = selectedRows.value.findIndex(row => row === id)
  if (index !== -1)
    selectedRows.value.splice(index, 1)

  // Refetch SPK List
  fetchSPKList()
}

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
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
          <!-- Create New SPK List button -->
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
        :items="SPKList"
        item-value="con_id"
        :items-length="totalSPKList"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
      
        <!-- SPK No -->
        <template #item.join_second_spk_no="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_second_spk_no ?? '-' }}
          </div>
        </template>

        <!-- Request No -->
        <template #item.arr_con_req_no="{ item }">
          <div v-if="item.arr_con_req_no.length > 0" class="text-body-1 text-high-emphasis text-capitalize">
            <ul>
              <div v-for="(req, index) in item.arr_con_req_no" :key="req.con_id">
                <div>
                  <template v-if="item.arr_con_req_no.length > 1">
                    <li class="list-unstyled">{{ req.con_req_no }}</li>
                  </template>
                  <template v-else>
                    {{ req.con_req_no }}
                  </template>
                </div>
              </div>
            </ul>
          </div>
          <template v-else>
            <spa>-</spa>
          </template>
        </template>

        <!-- PPS No -->
        <template #item.arr_con_pps_no="{ item }">
          <div v-if="item.arr_con_pps_no.length > 0" class="text-body-1 text-high-emphasis text-capitalize">
            <ul>
              <div v-for="(pps, index) in item.arr_con_pps_no" :key="pps.con_id">
                <template v-if="item.arr_con_pps_no.length > 1">
                  <li class="list-unstyled">{{ pps.con_pps_no }}</li>
                </template>
                <template v-else>
                  {{ pps.con_pps_no }}
                </template>
              </div>
            </ul>
          </div>
          <template v-else>
            <spa>-</spa>
          </template>
        </template>

        <!-- SPK Date -->
        <template #item.join_second_spk_date="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_second_spk_date != NULL ? formatDate(item.join_second_spk_date) : '-' }}
          </div>
        </template>

        <!-- Date Duration -->
        <template #item.join_second_spk_start_date="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ 
              Math.ceil(
                (new Date(item.join_second_spk_end_date) - new Date(item.join_second_spk_start_date)) / (1000 * 60 * 60 * 24)
              ) + ' days'
            }}
          </div>
        </template>

        <!-- Vendor Name -->
        <template #item.join_third_vnd_name="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_third_vnd_name }}
          </div>
        </template>

        <!-- Vendor PIC Name -->
        <template #item.join_third_vnd_contact_person="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_third_vnd_contact_person }}
          </div>
        </template>

        <!-- Job Description -->
        <template #item.join_second_spk_jobdesc_summary="{ item }">
          <div class="text-body-1 text-wrap text-high-emphasis text-capitalize">
            {{ item.join_second_spk_jobdesc_summary }}
          </div>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalSPKList"
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
