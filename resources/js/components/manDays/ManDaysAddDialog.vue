<script setup>
import { getListMerLaborType } from '@db/apps/mer/db';
import { ref, watch } from 'vue';
import 'vue-skeletor/dist/vue-skeletor.css';
import { VForm } from 'vuetify/components/VForm';
const loadingBtn = ref([])

const emit = defineEmits([
  'update:isDialogVisible',
  'ManDaysData',
  'isSnackbarResponse',
  'isSnackbarResponseAlertColor'
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
});

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
});

const dialogModelValueUpdate = () => {
  manDaysData.laborType = null;
  manDaysData.rate = null;
  manDaysData.effectiveDate = "";
  refVForm.value?.reset()
  refVForm.value?.resetValidation()
  loadingBtn.value[0] = false;
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
      console.error('Failed to fetch mer LaborT ype data');
    }
    
  } catch (error) {
    console.error('Error fetching mer Labor Type data',error);
  }
}

const fetchMandaysEdit = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/configurations/man-days-rate/edit/${mandaysId.value}`, {
      method: 'GET',
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
  },
  { immediate: true }
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
                          :loading="loadingBtn[0]"
                          :disabled="loadingBtn[0]"
                          type="submit"
                          class="me-4"
                        >
                          <span>{{ typeDialog == 'Edit' ? 'Update' : 'Create' }}</span>
                        </VBtn>
                        <VBtn
                          color="error"
                          variant="tonal"
                          @click="dialogModelValueUpdate"
                        >
                          Discard
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
