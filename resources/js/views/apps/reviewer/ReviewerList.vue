<script setup>
import { getListMerContractStatus } from '@db/apps/mer/db';
import dayjs from "dayjs";

const emit = defineEmits(['updateTotalNotExpired','updateTotalExpired'])
// Store
const searchQuery = ref('')
const selectedStatus = ref()
const selectedExpiredStatus = ref()
const dataMerCS = ref([])
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
    title: 'PPS No',
    key: 'con_pps_no',
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
    title: 'Vendor',
    key: 'join_third_vnd_name',
  },
  {
    title: 'Last Status',
    key: 'join_first_sts_description',
  },
  {
    title: 'SPK No',
    key: 'join_fifth_spk_no',
  },
  {
    title: 'Comment',
    key: 'con_comment_bu',
    sortable: false,
  },
]

const {
  data: reviewerData,
  execute: fetchReviewer,
} = await useApi(createUrl('/apps/reviewer/search', {
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

const reviewers = computed(() => reviewerData.value.reviewers)
const totalReviewers = computed(() => reviewerData.value.totalReviewers)
const totalExpiredCount = computed(() => reviewerData.value.totalExpiredCount)
const totalNotExpiredCount = computed(() => reviewerData.value.totalNotExpiredCount)
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

const deleteReviewer = async id => {
  await $api(`/apps/reviewer/${ id }`, { method: 'DELETE' })

  // Delete from selectedRows
  const index = selectedRows.value.findIndex(row => row === id)
  if (index !== -1)
    selectedRows.value.splice(index, 1)

  // Refetch Reviewer
  fetchReviewer()
}

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
}

const fetchMerContractStatus = async () => {
  try {
    const response = await getListMerContractStatus();
    const targetIds = ["1", "3", "4", "6", "8", "17", "18"];
    if (response.status === 200) {
      const rows = response.data.rows || [];
      
      dataMerCS.value = rows
      .filter((row) => targetIds.includes(row.sts_id))
      .map((row) => ({
        title: row.sts_description,
        value: row.sts_description,
      }));
    } else {
      console.error('Failed to fetch mer contract status data');
    }
    
  } catch (error) {
    console.error('Error fetching mer contract status data',error);
  }
};
onMounted(() => {
  fetchMerContractStatus()
});
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
          <!-- Select Status -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedStatus"
              placeholder="Select Status"
              :items="dataMerCS"
              :item-title="'title'"
              :item-value="'value'"
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
        :items="reviewers"
        item-value="con_id"
        :items-length="totalReviewers"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Reviewer -->
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

        <!-- PPS No -->
        <template #item.con_pps_no="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.con_pps_no }}
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

        <!-- Vendor Name -->
        <template #item.join_third_vnd_name="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_third_vnd_name }}
          </div>
        </template>

        <!-- Status -->
        <template #item.join_first_sts_description="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_first_sts_description }}
          </div>
        </template>

        <!-- SPK No -->
        <template #item.join_fifth_spk_no="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ (item.join_fifth_spk_no == null ? '-' : item.join_fifth_spk_no) }}
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
          <IconBtn @click="deleteReviewer(item.con_id)">
            <VIcon icon="tabler-eye-edit" />
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Reviewer Request</span>
            </VTooltip>
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalReviewers"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
</template>
