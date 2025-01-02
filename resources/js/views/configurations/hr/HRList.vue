<script setup>
import { watch } from "vue";

const emit = defineEmits(['updateTotalNotActive','updateTotalActive'])
const searchQuery = ref('')
const selectedRole = ref()
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const isUserHRDialogVisible = ref(false)
const isUserHRDialogDeleteVisible = ref(false)
const isUserHRTypeDialog = ref('Add')
const IDUserHR = ref(0)
const isSnackbarResponse = ref(false)
const isSnackbarResponseAlertColor = ref('error')
const fetchTrigger = ref(0);
const errorMessages = ref('Internal server error')
const successMessages = ref('Successfully')
const errors = ref({
  Username: undefined,
  UserDisplay: undefined,
  NoTlp: undefined,
  Access: undefined,
  BU: undefined,
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
    title: 'Username',
    key: 'usr_name',
  },
  {
    title: 'Display Name',
    key: 'usr_display_name',
  },
  {
    title: 'Role',
    key: 'usr_access',
    sortable: false,
  },
  {
    title: 'Phone',
    key: 'usr_no_tlp',
    sortable: false,
  },
  {
    title: 'Approver 1',
    key: 'usr_approver1',
    sortable: false,
  },
  {
    title: 'Approver 2',
    key: 'usr_approver2',
    sortable: false,
  },
  {
    title: 'Jabatan',
    key: 'usr_jabatan',
    sortable: false,
  },
  {
    title: 'BU List',
    key: 'arr_business_unit',
    sortable: false,
  },
]

// search filters
const role = [
  {
    title: 'Admin',
    value: 'admin',
  },
  {
    title: 'Requester',
    value: 'requester',
  },
  {
    title: 'PBL',
    value: 'pbl',
  },
  {
    title: 'Approval',
    value: 'approval',
  },
]

const {
  data: userHRData,
  execute: fetchUserHR,
} = await useApi(createUrl('/configurations/human-resources/search', {
  query: {
    q: searchQuery,
    role: selectedRole,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const userHR = computed(() => userHRData.value.userHR)
const totalUserHR = computed(() => userHRData.value.totalUserHR)
const totalActiveCount = computed(() => userHRData.value.totalActiveCount)
const totalNotActiveCount = computed(() => userHRData.value.totalNotActiveCount)

watch(
  [totalNotActiveCount, totalActiveCount],
  ([newNotActiveStatus, newActiveStatus]) => {
    emit('updateTotalNotActive', newNotActiveStatus);
    emit('updateTotalActive', newActiveStatus);
  },
  { immediate: true }
)

const openDialog = async ({ id = null, type }) => {
  isUserHRTypeDialog.value = type
  isUserHRDialogVisible.value = true
  if(type == 'Edit')
    IDUserHR.value = id
    fetchTrigger.value += 1;
}

const openDialogDelete = async (id) => {
  IDUserHR.value = id
  isUserHRDialogDeleteVisible.value = true
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

const fetchAddData = async (userHRData, clearedForm) => {
  try {
      const response = await $api('/configurations/human-resources/add', {
        method: 'POST',
        body: JSON.stringify(userHRData),
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
      fetchUserHR()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isUserHRDialogVisible.value = false
    } else {
      alertErrorResponse()
      throw new Error("Created data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const fetchUserHRUpdate = async (id, formData, clearedForm) => {
  try {
    const idPPSOngoing = Number(id);
    const response = await $api(`/configurations/human-resources/update/${idPPSOngoing}`, {
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
      fetchUserHR()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isUserHRDialogVisible.value = false
      alertSuccessResponse()
    } else {
      alertErrorResponse()
      throw new Error("Updated data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const deletedUserHR = async id => {
  try {
    isUserHRTypeDialog.value = 'Delete'
    const idUserHR = Number(id);
    const response = await $api(`/configurations/human-resources/delete/${idUserHR}`, {
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
      fetchUserHR()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isUserHRDialogDeleteVisible.value = false;
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
    fetchUserHRUpdate(IDUserHR.value, formData, dialogUpdate)
  }
}
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardText>
        <VRow>
          <!-- Select Role -->
          <VCol cols="12" sm="4">
            <AppSelect
              v-model="selectedRole"
              placeholder="Select role"
              :items="role"
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
              placeholder="Search name..."
              clearable
              prepend-inner-icon="tabler-search"
            />
          </div>

          <!-- Create New HR button -->
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
        :items="userHR"
        item-value="usr_id"
        :items-length="totalUserHR"
        :headers="headers"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <!-- Username -->
        <template #item.usr_name="{ item }">
          <div class="text-body-1 text-high-emphasis">
            {{ item.usr_name }}
          </div>
        </template>

        <!-- Display Name -->
        <template #item.usr_display_name="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.usr_display_name }}
          </div>
        </template>

        <!-- Role -->
        <template #item.usr_access="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.usr_access.toUpperCase()  }}
          </div>
        </template>

        <!-- Phone -->
        <template #item.usr_no_tlp="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.usr_no_tlp != null && item.usr_no_tlp != '' ? item.usr_no_tlp : '-' }}
          </div>
        </template>

        <!-- Approver 1 -->
        <template #item.usr_approver1="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize text-center">
            <VIcon v-if="item.usr_approver1"
              :size="26"
              color="success"
              icon="tabler-check"
            />
            <VIcon v-else
              :size="26"
              color="error"
              icon="tabler-x"
            />
          </div>
        </template>

        <!-- Approver 2 -->
        <template #item.usr_approver2="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize text-center">
            <VIcon v-if="item.usr_approver2"
              :size="26"
              color="success"
              icon="tabler-check"
            />
            <VIcon v-else
              :size="26"
              color="error"
              icon="tabler-x"
            />
          </div>
        </template>

        <!-- Jabatan -->
        <template #item.usr_jabatan="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.usr_jabatan != null && item.usr_jabatan != '' ? item.usr_jabatan : '-' }}
          </div>
        </template>

        <!-- BU List -->
        <template #item.arr_business_unit="{ item }">
          <div v-if="item.arr_business_unit.length > 0" class="text-body-1 text-high-emphasis text-capitalize">
            <ul>
              <div v-for="(hr, index) in item.arr_business_unit" :key="hr.number">
                <template v-if="item.arr_business_unit.length > 1">
                  <li class="list-unstyled">{{ (hr.number + ' - ' ?? '') + hr.description }}</li>
                </template>
                <template v-else>
                  {{ (hr.number + ' - ' ?? '') + hr.description }}
                </template>
              </div>
            </ul>
          </div>
          <template v-else>
            <spa>-</spa>
          </template>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="openDialog({id: item.usr_id, type: 'Edit'})">
            <VIcon icon="tabler-edit" />
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Edit data</span>
            </VTooltip>
          </IconBtn>
          <IconBtn @click="openDialogDelete(item.usr_id)">
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
            :total-items="totalUserHR"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
  <HRAddDialog
    v-model:isDialogVisible="isUserHRDialogVisible"
    :errors="errors"
    :typeDialog="isUserHRTypeDialog"
    :userhr-id="IDUserHR"
    :fetch-trigger="fetchTrigger"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @UserHRData="handleFormSubmit"
    @errorMessages="updateErrorMessages"
    @errors="updateErrors"
  />
  <HRDeleteDialog
    v-model:isDialogDeleteVisible="isUserHRDialogDeleteVisible"
    :userhr-id="IDUserHR"
    :fetch-trigger="fetchTrigger"
    @idDeleted="deletedUserHR"
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
