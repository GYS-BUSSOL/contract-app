<script setup>
import 'vue-skeletor/dist/vue-skeletor.css';

const emit = defineEmits([
  'update:isDialogVisible',
  'AreaBUData',
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
  areaBUId: {
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
const areaBUId = computed(() => props.areaBUId)
const loadingBtn = ref([])
const token = useCookie('accessToken')

const areaBUData = reactive({
  number: null,
  description: null,
});

const dialogModelValueUpdate = () => {
  areaBUData.number = null;
  areaBUData.description = null;
  // Reset form
  refVForm.value?.reset()
  refVForm.value?.resetValidation()
  // Falsing button
  loadingBtn.value[0] = false;
  // Emit
  emit('updateTypeDialog')
  emit('update:isDialogVisible', false)
}

const fetchAreaBUEdit = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/configurations/area-management-bu/edit/${areaBUId.value}`, {
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
    })
    
    const dataResponse = JSON.parse(JSON.stringify(response));
    if (dataResponse.status == 200) {
      const dataResult = dataResponse.data;
      isLoading.value = false;
      areaBUData.number = dataResult.number;
      areaBUData.description = dataResult.description;
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
        emit("AreaBUData", { mode, formData: { ...areaBUData }, dialogUpdate: dialogModelValueUpdate });
      } else {
        loadingBtn.value[0] = false
      }
    } catch (err) {
      loadingBtn.value[0] = false
    }
  })
}

watch(
  [() => areaBUId.value, () => typeDialog.value, () => props.fetchTrigger],
    ([newId,newType]) => {
      if (newType === "Edit" && newId) {
        fetchAreaBUEdit();
      } else if (newType === "Add") {
        isLoading.value = false;
      }
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
                    <div class="card__actions d-flex flex-column gap-5 justify-end" v-if="isLoading">
                      <Skeletor width="200" height="30"/>
                      <Skeletor width="105" height="25"/>
                    </div>
                    <div class="text-h4 font-weight-medium" v-if="!isLoading">
                      {{ typeDialog == 'Edit' ? 'Edit' : 'Create New' }} Area Business Unit
                    </div>
                  </div>
                </div>
              </div>
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
                    <VCol cols="12" md="6"  v-for="i in 2" :key="i">
                      <div class="card__text mb-5" v-if="isLoading">
                        <Skeletor height="40" pill class="mt-5"/>
                      </div>
                    </VCol>
                    <VCol cols="12" v-if="!isLoading">
                      <VRow>
                        <VCol cols="12" md="6">
                          <AppTextField
                            v-model="areaBUData.number"
                            persistent-placeholder
                            label="Number*"
                            placeholder="Type here..."
                            :rules="[requiredValidator]"
                            :error-messages="props.errors?.number"
                            required
                          />
                        </VCol>
                        <VCol md="6">
                          <AppTextField
                            v-model="areaBUData.description"
                            persistent-placeholder
                            label="Description*"
                            placeholder="Type here..."
                            :rules="[requiredValidator]"
                            :error-messages="props.errors?.description"
                            required
                          />
                        </VCol>
                      </VRow>
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
