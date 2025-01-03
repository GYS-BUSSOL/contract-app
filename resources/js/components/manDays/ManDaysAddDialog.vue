<script setup>
import { getListMerLaborType } from '@db/apps/mer/db';
import 'vue-skeletor/dist/vue-skeletor.css';
const loadingBtn = ref([])
const token = useCookie('accessToken')
const emit = defineEmits([
  'update:isDialogVisible',
  'ManDaysData',
  'isSnackbarResponse',
  'isSnackbarResponseAlertColor',
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
  mandaysId: {
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

const InptRate = ref('')
const dataMerLaborType = ref([])
const isLoading = ref(true)
const refVForm = ref()
const typeDialog = computed(() => props.typeDialog)
const mandaysId = computed(() => props.mandaysId)

const manDaysData = reactive({
  laborType: null,
  rate: null,
  effectiveDate: "",
})

const formattedRate = computed({
  get() {
    if (InptRate.value === "") return "";
    return new Intl.NumberFormat("id-ID", {
      style: "decimal",
      maximumFractionDigits: 0,
    }).format(InptRate.value);
  },
  set(value) {
    InptRate.value = value.replace(/[^0-9]/g, "");
  },
})

const dialogModelValueUpdate = () => {
  manDaysData.laborType = null;
  manDaysData.rate = null;
  manDaysData.effectiveDate = "";
  // Form reset
  refVForm.value?.reset()
  refVForm.value?.resetValidation()
  loadingBtn.value[0] = false;
  emit('updateTypeDialog')
  emit('update:isDialogVisible', false)
}

const fetchMerLaborTypeData = async () => {
  try {
    const response = await getListMerLaborType();
    if (response.status === 200) {
      const rows = response.data.rows || [];
      dataMerLaborType.value = rows.map((row) => ({
        title: row.labor_type,
        value: row.labor_type,
      }));
    } else {
      throw new Error("Failed to fetch mer labor type data");
    }
  } catch (error) {
    throw new Error("Failed to fetch mer labor type data");
  }
}

const fetchMandaysEdit = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/configurations/man-days-rate/edit/${mandaysId.value}`, {
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
      manDaysData.laborType = dataResult.rtk_id_jenis_tk;
      manDaysData.rate = dataResult.rtk_rate;
      manDaysData.effectiveDate = dataResult.rtk_effective_date;
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

watch(
  [() => mandaysId.value, () => typeDialog.value, () => props.fetchTrigger],
    ([newId,newType]) => {
      if (newType === "Edit" && newId) {
        fetchMandaysEdit();
      } else if (newType === "Add") {
        isLoading.value = false;
      }
    fetchMerLaborTypeData()
    loadingBtn.value[0] = false;
  }
);

const onSubmit = async () => {
  refVForm.value?.validate().then(({ valid }) => {
    try {
      if (valid) {
        loadingBtn.value[0] = true
        const mode = props.typeDialog;
        emit("ManDaysData", { mode, formData: { ...manDaysData }, dialogUpdate: dialogModelValueUpdate });
      } else {
        loadingBtn.value[0] = false
      }
    } catch (err) {
      loadingBtn.value[0] = false
    }
  })
}
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
                      {{ typeDialog == 'Edit' ? 'Edit' : 'Create New' }} Man Days Rate
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card__text mb-5" v-if="isLoading">
              <Skeletor v-for="i in 3" :key="i" height="40" pill class="mt-5"/>
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
                      <VCol cols="12">
                        <AppDateTimePicker
                          v-model="manDaysData.effectiveDate"
                          label="Effective Date*"
                          placeholder="Select date"
                          :rules="[requiredValidator]"
                          :error-messages="props.errors?.effectiveDate"
                          clearable
                          required
                        />
                      </VCol>
                      <VCol>
                        <AppAutocomplete
                          v-model="manDaysData.laborType"
                          placeholder="Select type"
                          label="Labor Type*"
                          :items="dataMerLaborType"
                          :rules="[requiredValidator]"
                          :error-messages="props.errors?.laborType"
                          clearable
                          required
                        />
                      </VCol>
                      <VCol>
                        <AppTextField
                          v-model="manDaysData.rate"
                          persistent-placeholder
                          label="Rate*"
                          placeholder="Type here..."
                          :rules="[requiredValidator]"
                          :error-messages="props.errors?.rate"
                          type="number"
                          required
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
