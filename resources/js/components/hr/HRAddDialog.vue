<script setup>
import { getListMerBU } from '@db/apps/mer/db';
import 'vue-skeletor/dist/vue-skeletor.css';

const emit = defineEmits([
  'update:isDialogVisible',
  'UserHRData',
  'isSnackbarResponse',
  'isSnackbarResponseAlertColor',
  'errorMessages',
  'errors',
  'updateTypeDialog'
])

const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  typeDialog: {
    type: String,
    required: true
  },
  userhrId: {
    type: Number,
    required: true
  },
  fetchTrigger: {
    type: Number,
    default: 0
  },
  errors: {
    type: Object,
    required: false
  }
})

const isLoading = ref(true)
const refVForm = ref()
const typeDialog = computed(() => props.typeDialog)
const userhrId = computed(() => props.userhrId)
const loadingBtn = ref([])
const token = useCookie('accessToken').value
const userHRData = reactive({
  Username: "",
  UserDisplay: "",
  NoTlp: "",
  Access: null,
  BU: []
})
const dataMerBU = ref([])

const dataAccessRole = ref([
  {
    title: 'Admin',
    value: 'admin'
  },
  {
    title: 'Approval',
    value: 'approval'
  },
  {
    title: 'Requester',
    value: 'requester'
  },
])

const dialogModelValueUpdate = () => {
  userHRData.Username = "";
  userHRData.UserDisplay = "";
  userHRData.NoTlp = "";
  userHRData.Access = null;
  userHRData.BU = [];
  // Form reset
  refVForm.value?.reset()
  refVForm.value?.resetValidation()
  // Falsing button
  loadingBtn.value[0] = false;
  emit('update:isDialogVisible', false)
}

const fetchMerBUData = async () => {
  try {
    const response = await getListMerBU();
    if (response.status === 200) {

      const rows = response.data.rows || [];
      dataMerBU.value = rows.map((row) => ({
        title: (row.number != null && row.number != '' ? row.number + ' - ' : '') + row.description,
        value: row.id,
      }));
    } else {
      throw new Error("Failed to fetch mer business unit data");
    }
  } catch (error) {
    throw new Error("Failed to fetch mer business unit data");
  }
}

const fetchUserHREdit = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/configurations/human-resources/edit/${userhrId.value}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      onResponseError({ response }) {
        const responseData = response._data;
        const responseMessage = responseData.message;
        const responseErrors = responseData.errors;
        emit('errors', responseErrors);
        emit('errorMessages', responseMessage);
        emit('update:isDialogVisible', false)
        throw new Error("Get data failed");
      },
    });
    
    const dataResponse = JSON.parse(JSON.stringify(response));
    if (dataResponse.status == 200) {
      const dataResult = dataResponse.data;

      isLoading.value = false;
      userHRData.Username = dataResult.usr_name;
      userHRData.UserDisplay = dataResult.usr_display_name;
      userHRData.NoTlp = dataResult.usr_no_tlp;
      userHRData.Access = dataResult.usr_access;
      userHRData.BU = dataResult.bu_id.split(",")
        .map((id) => parseInt(id.trim(), 10));

    } else {
      emit('update:isDialogVisible', false)
      emit('isSnackbarResponse',true)
      emit('isSnackbarResponseAlertColor', 'error')
      throw new Error("Get data failed");
    }
  } catch (error) {
    isLoading.value = false;
    emit('update:isDialogVisible', false)
    emit('isSnackbarResponse',true)
    emit('isSnackbarResponseAlertColor', 'error')
  }
}

const onSubmit = async () => {
  refVForm.value?.validate().then(({ valid }) => {
    try {
      if (valid) {
        loadingBtn.value[0] = true
        const mode = props.typeDialog;
        emit("UserHRData", { mode, formData: { ...userHRData }, dialogUpdate: dialogModelValueUpdate });
      } else {
        loadingBtn.value[0] = false
      }
    } catch (err) {
      loadingBtn.value[0] = false
    }
  })
}

watch(
  [() => userhrId.value, () => typeDialog.value, () => props.fetchTrigger],
    ([newId,newType]) => {
      if (newType === "Edit" && newId) {
        fetchUserHREdit();
      } else if (newType === "Add") {
        isLoading.value = false;
      }
      fetchMerBUData()
      loadingBtn.value[0] = false;
  }
)
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 1200"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate" />
    <VCard class="pa-sm-10 pa-2" v-model:loading="isLoading">
      <VCardText>
          <div>
            <div class="d-flex flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
              <div class="d-flex flex-column justify-center">
                <div class="d-flex gap-x-4 align-center">
                  <div>
                    <div class="card__actions d-flex justify-end" v-if="isLoading">
                      <Skeletor width="105" height="36" class="me-4" pill/>
                    </div>
                    <div class="text-h4 font-weight-medium" v-if="!isLoading">
                      {{ typeDialog == 'Edit' ? 'Edit' : 'Create New' }} User
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card__text mb-5" v-if="isLoading">
              <Skeletor v-for="i in 5" :key="i" height="40" pill class="mt-5"/>
            </div>
            <VRow>
              <VCol cols="12">
                <VForm
                  ref="refVForm"
                  lazy-validation
                  @submit.prevent="onSubmit"
                >
                  <VRow>
                    <VCol cols="12" v-if="!isLoading">
                      <h5>(*) Is required</h5>
                    </VCol>
                    <VCol cols="12" v-if="!isLoading">
                      <VCol>
                        <AppTextField
                          label="Username*"
                          v-model="userHRData.Username"
                          placeholder="Type here..."
                          :rules="[requiredValidator]"
                          :error-messages="props.errors?.Username"
                          clearable
                        />
                      </VCol>
                      <VCol>
                        <AppTextField
                          label="User Display*"
                          v-model="userHRData.UserDisplay"
                          placeholder="Type here..."
                          :rules="[requiredValidator]"
                          :error-messages="props.errors?.UserDisplay"
                          clearable
                        />
                      </VCol>
                      <VCol>
                        <AppTextField
                          label="Phone*"
                          persistent-placeholder
                          v-model="userHRData.NoTlp"
                          placeholder="Type here..."
                          :rules="[requiredValidator, integerValidator]"
                          :error-messages="props.errors?.NoTlp"
                          clearable
                        />
                      </VCol>
                      <VCol>
                        <AppAutocomplete
                          placeholder="Select access role"
                          label="Access*"
                          v-model="userHRData.Access"
                          :items="dataAccessRole"
                          :rules="[requiredValidator]"
                          :error-messages="props.errors?.Access"
                          :item-title="'title'"
                          :item-value="'value'"
                          clearable
                        />
                      </VCol>
                      <VCol>
                        <AppAutocomplete
                          label="Select business unit* (multiple select)"
                          v-model="userHRData.BU"
                          :items="dataMerBU"
                          :item-title="'title'"
                          :item-value="'value'"
                          :rules="[requiredValidator]"
                          :error-messages="props.errors?.BU"
                          placeholder="Select multiple business unit"
                          chips
                          multiple
                          closable-chips
                          clearable
                        />
                      </VCol>
                    </VCol>
                  </VRow>
                  <VCol cols="12" class="mt-5">
                    <VRow class="d-flex justify-end">
                      <div class="card__actions d-flex justify-end" v-if="isLoading">
                        <Skeletor width="96" height="36" class="me-4"/>
                        <Skeletor width="96" height="36" />
                      </div>
                      <div v-if="!isLoading">
                        <VBtn
                          color="secondary"
                          variant="tonal"
                          @click="dialogModelValueUpdate"
                          class="me-4"
                        >
                          Discard
                        </VBtn>
                        <VBtn
                          :loading="loadingBtn[0]"
                          :disabled="loadingBtn[0]"
                          type="submit"
                        >
                          <span>{{ typeDialog == 'Edit' ? 'Update' : 'Create' }}</span>
                        </VBtn>
                      </div>
                    </VRow>
                  </VCol>
                </VForm>
              </VCol>
            </VRow>
          </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>
