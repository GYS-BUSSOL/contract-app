<script setup>

const emit = defineEmits(['updateTotalNotActive','updateTotalActive'])
// Store
const searchQuery = ref('')
const selectedStatus = ref()
// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const isAreaCCDialogVisible = ref(false)
const isAreaCCDialogDeleteVisible = ref(false)
const isAreaCCTypeDialog = ref('Add')
const IDAreaCC = ref(0)
const isSnackbarResponse = ref(false)
const isSnackbarResponseAlertColor = ref('error')
const fetchTrigger = ref(0);
const errorMessages = ref('Internal server error')
const successMessages = ref('Successfully')
const errors = ref({
  description: undefined,
  number: undefined
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
    title: 'Number',
    key: 'number',
  },
  {
    title: 'Description',
    key: 'description',
  },
  {
    title: 'Status',
    key: 'is_active',
    sortable: false,
  },
]

// search filters
const status = [
  {
    title: 'Active',
    value: '0',
  },
  {
    title: 'Not Active',
    value: '1',
  },
  {
    title: 'Undifinied',
    value: 'null',
  },
]

const {
  data: areaCCData,
  execute: fetchAreaCC,
} = await useApi(createUrl('/configurations/area-management-cc/search', {
  query: {
    q: searchQuery,
    status: selectedStatus,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const areaCC = computed(() => areaCCData.value.areaCC)
const totalAreaCC = computed(() => areaCCData.value.totalAreaCC)
const totalActiveCount = computed(() => areaCCData.value.totalActiveCount)
const totalNotActiveCount = computed(() => areaCCData.value.totalNotActiveCount)

watch(
  [totalNotActiveCount, totalActiveCount],
  ([newNotActiveStatus, newActiveStatus]) => {
    emit('updateTotalNotActive', newNotActiveStatus);
    emit('updateTotalActive', newActiveStatus);
  },
  { immediate: true }
);

const openDialog = async ({ id = null, type }) => {
  isAreaCCTypeDialog.value = type
  isAreaCCDialogVisible.value = true
  if(type == 'Edit')
    IDAreaCC.value = id
    fetchTrigger.value += 1;
}

const openDialogDelete = async (id) => {
  IDAreaCC.value = id
  isAreaCCDialogDeleteVisible.value = true
}

const resolveAreaCCStatusVariant = stat => {
  if (stat == 0 && stat != null )
    return 'success'
  else if(stat == 1)
    return 'error'
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

const fetchAddData = async (areaCCData, clearedForm) => {
  try {
    const response = await $api('/configurations/area-management-cc/add', {
      method: 'POST',
      body: JSON.stringify(areaCCData),
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
      fetchAreaCC()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isAreaCCDialogVisible.value = false
    } else {
      alertErrorResponse()
      throw new Error("Created data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const fetchAreaCCUpdate = async (id, formData, clearedForm) => {
  try {
    const response = await $api(`/configurations/area-management-cc/update/${id}`, {
        method: 'PUT',
        body: JSON.stringify(formData),
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
      fetchAreaCC()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isAreaCCDialogVisible.value = false
    } else {
      alertErrorResponse()
      throw new Error("Updated data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const deleteAreaCC = async id => {
  try {
    isAreaCCTypeDialog.value = 'Delete'
    const idAreaCC = Number(id);
    const response = await $api(`/configurations/area-management-cc/delete/${idAreaCC}`, {
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
      fetchAreaCC()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isAreaCCDialogDeleteVisible.value = false;
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
    fetchAreaCCUpdate(IDAreaCC.value, formData, dialogUpdate)
  }
}
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardText>
        <VRow>
          <!-- Select Status -->
          <VCol cols="12" sm="4">
            <AppSelect
              v-model="selectedStatus"
              placeholder="Select status"
              :items="status"
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

          <!-- Create New PBL button -->
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
        :items="areaCC"
        item-value="id"
        :items-length="totalAreaCC"
        :headers="headers"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <!-- Number -->
        <template #item.number="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.number != null && item.number != '' ? item.number : '-' }}
          </div>
        </template>

        <!-- Description -->
        <template #item.description="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.description != null && item.description != '' ? item.description : '-' }}
          </div>
        </template>

        <!-- Status -->
        <template #item.is_active="{ item }">
          <VChip
            :color="resolveAreaCCStatusVariant(item.is_active)"
            size="small"
            label
            class="text-capitalize"
          >
            {{ item.is_active != null && item.is_active != '' ? (item.is_active == 0 ? 'Active' : 'Not Active') : '-' }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="openDialog({id: item.id, type: 'Edit'})">
            <VIcon icon="tabler-edit" />
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Edit data</span>
            </VTooltip>
          </IconBtn>
          <IconBtn @click="openDialogDelete(item.id)">
            <VIcon icon="tabler-trash" />
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Edit data</span>
            </VTooltip>
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalAreaCC"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
  <AreaCCAddDialog
    v-model:isDialogVisible="isAreaCCDialogVisible"
    :errors="errors"
    :typeDialog="isAreaCCTypeDialog"
    :areaCC-id="IDAreaCC"
    :fetch-trigger="fetchTrigger"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @AreaCCData="handleFormSubmit"
    @errorMessages="updateErrorMessages"
    @errors="updateErrors"
  />
  <AreaCCDeleteDialog
    v-model:isDialogDeleteVisible="isAreaCCDialogDeleteVisible"
    :areaCC-id="IDAreaCC"
    :fetch-trigger="fetchTrigger"
    @id-deleted="deleteAreaCC"
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
