<script setup>
import { getListBU, getListMerVendor } from '@db/apps/mer/db';
import dayjs from "dayjs";

const emit = defineEmits(['updateTotalNotActive','updateTotalActive'])
// Store
const searchQuery = ref('')
const selectedVendor = ref('All')
const selectedBU = ref('All')
const selectedStartDate = ref(dayjs().format('MMMM YYYY'));
const selectedEndDate = ref(dayjs().format('MMMM YYYY'));
const selectedSPKStatus = ref('All')
// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const dataMerVendor = ref([])
const dataMerBU = ref([])

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'BU',
    key: 'BU',
  },
  {
    title: 'Vendor Name',
    key: 'vendor_name',
  },
  {
    title: 'PPS No',
    key: 'con_req_no',
  },
  {
    title: 'PPS Date',
    key: 'con_req_date',
  },
  {
    title: 'Cost Center',
    key: 'cost_center',
  },
  {
    title: 'Work Center',
    key: 'work_center',
  },
  {
    title: 'SPK No',
    key: 'spk_no',
  },
  {
    title: 'SPK Summary',
    key: 'spk_jobdesc_summary',
  },
  {
    title: 'Start Date',
    key: 'start_date',
  },
  {
    title: 'End Date',
    key: 'end_date',
  },
  {
    title: 'Labor Qty',
    key: 'labor_qty',
  },
  {
    title: 'Labor Type',
    key: 'labor_type',
  },
  {
    title: 'Job Qty',
    key: 'job_qty',
  },
  {
    title: 'Job Type',
    key: 'job_type',
  },
  {
    title: 'Payment Type',
    key: 'job_payment_type',
  },
  {
    title: 'Job Rate',
    key: 'job_rate',
  },
  {
    title: 'SPK Status',
    key: 'spk_status_active',
  },
]

const {
  data: SPKReportData,
  execute: fetchSPKReport,
} = await useApi(createUrl('/apps/spk-report/search', {
  query: {
    q: searchQuery,
    vendor: selectedVendor,
    bu: selectedBU,
    startDate: selectedStartDate,
    endDate: selectedEndDate,
    spkStatus: selectedSPKStatus,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const SPKReport = computed(() => SPKReportData.value.SPKReport)
const totalSPKReport = computed(() => SPKReportData.value.totalSPKReport)
const totalActiveSPKCount = computed(() => SPKReportData.value.totalActiveSPKCount)
const totalNotActiveSPKCount = computed(() => SPKReportData.value.totalNotActiveSPKCount)
emit('updateTotalNotActive', totalNotActiveSPKCount.value);
emit('updateTotalActive', totalActiveSPKCount.value);
watch([selectedSPKStatus, selectedVendor, selectedBU], ([newSPKStatus, newVendor, newBU]) => {
  if(!newSPKStatus)
    selectedSPKStatus.value = 'All';
  if(!newVendor)
    selectedVendor.value = 'All';
  if(!newBU)
    selectedBU.value = 'All';
});

// search filters
const SPKStatus = [
  {
    title: 'All',
    value: 'All',
  },
  {
    title: 'Active',
    value: 'Active',
  },
  {
    title: 'Not Active',
    value: 'Not Active',
  }
]

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
}

const fetchMerVendorData = async () => {
  try {
    const response = await getListMerVendor();
    if (response.status === 200) {
      const rows = response.data.rows || [];
      dataMerVendor.value = [{
        title: 'All',
        value: 'All',
      }, ...rows.map((row) => ({
        title: row.vnd_name,
        value: row.vnd_name,
      }))];
    } else {
      throw new Error("Failed to fetch vendor data");
    }
  } catch (error) {
    throw new Error("Failed to fetch vendor data");
  }
};

const fetchBUData = async () => {
  try {
    const response = await getListBU();
    
    if (response.data.rows.length > 0) {
      const rows = response.data.rows || [];
      
      dataMerBU.value = [{
        title: 'All',
        value: 'All'
      }, ...rows.map((row) => ({
        title: row.BU,
        value: row.number,
      }))];
    } else {
      throw new Error("Failed to fetch business unit data");
    }
  } catch (error) {
    throw new Error("Failed to fetch business unit data");
  }
};

const resolveSPKStatusVariant = stat => {
  const statLowerCase = stat.toLowerCase()
  
  if (statLowerCase === 'active')
    return 'success'

  return 'error'
}

onMounted(() => {
  fetchMerVendorData()
  fetchBUData()
});
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardText>
        <VRow>
          <!-- Select From Date -->
          <VCol cols="12" sm="4">
            <AppDateTimePicker
              v-model="selectedStartDate"
              label="From"
              placeholder="Select month"
              :config="{ 
                dateFormat: 'F Y',
              }"
              prepend-inner-icon="tabler-calendar-month"
            />
          </VCol>
          <!-- Select To Date -->
          <VCol cols="12" sm="4">
            <AppDateTimePicker
              v-model="selectedEndDate"
              label="To"
              placeholder="Select month"
              :config="{
                dateFormat: 'F Y',
              }"
              prepend-inner-icon="tabler-calendar-month"
            />
          </VCol>
          <!-- Select SPK Status -->
          <VCol cols="12" sm="4">
            <AppSelect
              label="SPK Status"
              v-model="selectedSPKStatus"
              placeholder="Select SPK status"
              :items="SPKStatus"
              prepend-inner-icon="tabler-filter-search"
            />
          </VCol>
        </VRow>
        <VRow>
          <!-- Select Vendor -->
          <VCol cols="12" sm="4">
            <AppAutocomplete
              label="Vendor Name"
              v-model="selectedVendor"
              placeholder="Select vendor"
              :items="dataMerVendor"
              :item-title="'title'"
              :item-value="'value'"
              prepend-inner-icon="tabler-filter-search"
            />
          </VCol>
          <!-- Select BU -->
          <VCol cols="12" sm="4">
            <AppAutocomplete
              label="BU"
              v-model="selectedBU"
              placeholder="Select BU"
              :items="dataMerBU"
              :item-title="'title'"
              :item-value="'value'"
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
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="SPKReport"
        item-value="con_req_id"
        :items-length="totalSPKReport"
        :headers="headers"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
      
        <!-- BU -->
        <template #item.BU="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.BU ?? '-' }}
          </div>
        </template>

        <!-- Vendor Name -->
        <template #item.vendor_name="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.vendor_name ?? '-' }}
          </div>
        </template>

        <!-- PPS No -->
        <template #item.con_req_no="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.con_req_no ?? '-' }}
          </div>
        </template>

        <!-- PPS Date -->
        <template #item.con_req_date="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.con_req_date != NULL ? formatDate(item.con_req_date) : '-' }}
          </div>
        </template>

        <!-- Cost Center -->
        <template #item.cost_center="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.cost_center }}
          </div>
        </template>
        
        <!-- Work Center -->
        <template #item.work_center="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.work_center }}
          </div>
        </template>

        <!-- SPK No -->
        <template #item.spk_no="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.spk_no }}
          </div>
        </template>

        <!-- SPK Summary -->
        <template #item.spk_jobdesc_summary="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.spk_jobdesc_summary }}
          </div>
        </template>

        <!-- Start Date -->
        <template #item.start_date="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.start_date != NULL ? formatDate(item.start_date) : '-' }}
          </div>
        </template>

        <!-- End Date -->
        <template #item.end_date="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.end_date != NULL ? formatDate(item.end_date) : '-' }}
          </div>
        </template>

        <!-- Labor QTY -->
        <template #item.labor_qty="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.labor_qty }}
          </div>
        </template>

        <!-- Labor Type -->
        <template #item.labor_type="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.labor_type }}
          </div>
        </template>

        <!-- Job QTY -->
        <template #item.job_qty="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.job_qty }}
          </div>
        </template>

        <!-- Job Type -->
        <template #item.job_type="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.job_type }}
          </div>
        </template>

        <!-- Payment Type -->
        <template #item.job_payment_type="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.job_payment_type }}
          </div>
        </template>

        <!-- Job Rate -->
        <template #item.job_rate="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.job_rate }}
          </div>
        </template>

        <!-- SPK Status -->
        <template #item.spk_status_active="{ item }">
          <VChip
            :color="resolveSPKStatusVariant(item.spk_status_active)"
            size="small"
            label
            class="text-capitalize"
          >
            {{ item.spk_status_active }}
          </VChip>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalSPKReport"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
</template>
