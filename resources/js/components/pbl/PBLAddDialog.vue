<script setup>
import { getListContract, getListMerVendor, getListSignatureType } from '@db/apps/mer/db';
import dayjs from "dayjs";
import 'vue-skeletor/dist/vue-skeletor.css';

const emit = defineEmits([
  'update:isDialogVisible',
  'createSPK',
  'isSnackbarResponse',
  'isSnackbarResponseAlertColor',
  'errorMessages',
  'errors',
  'isSuccessNextStep'
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
  contractReqNo: {
    type: String,
    required: true
  },
  contractReqId: {
    type: String,
    required: true
  },
  fetchTrigger: {
    type: Number,
    default: 0
  },
  errors: {
    type: Object,
    required: false
  },
  isSuccessNextStep: {
    type: Boolean,
    required: true,
    default: false,
  },
})

const isLoading = ref(true)
const refVForm = ref()
const typeDialog = computed(() => props.typeDialog)
const contractReqNo = computed(() => props.contractReqNo)
const contractReqId = computed(() => props.contractReqId)
const loadingBtn = ref([])
const loadingBtnSecond = ref([])
const refSPKForm = ref()
const refSPKPreviewForm = ref()
const isDialogViewPathVisible = ref(false)
const currentStep = ref(0)
const isCurrentStepValid = ref(true)
const dataMerVendor = ref([])
const dataContractList = ref([])
const dataSignatureTypeList = ref([])
const allDataVendor = ref([])
const numberedSteps = ref([
  {
    title: 'Create',
    subtitle: 'Add SPK',
  },
  {
    title: 'Preview',
    subtitle: 'Save & Print SPK',
  },
])

const contractData = reactive({
  con_req_id: null,
  con_req_no: null,
  aud_user: null,
  con_bu: null,
  description: null,
  bu: null,
  cc: [],
  wc: [],
  con_work_location: null,
  con_duration_start: null,
  con_duration_end: null,
  suggest_vendor: null,
})

const contractJobData = reactive({
  contract_job: [],
  job_type: [],
  job_labor: [],
  con_comment_coo_reject: null,
})

const vendorData = reactive({
  vnd_name: null,
})

const SPKData = reactive({
  spk_jobdesc_summary: null,
  durations: null,
  signature_type: null
})

// Reset All
const dialogModelValueUpdate = () => {
  // Vendor
  vendorData.vnd_name = null;
  // SPK
  SPKData.spk_jobdesc_summary = null;
  SPKData.durations = null;
  SPKData.signature_type = null;
  // Contract Job
  contractJobData.contract_job = [];
  contractJobData.job_type = [];
  contractJobData.job_labor = [];
  contractJobData.con_comment_coo_reject = null;
  // Contract
  contractData.con_req_id = null;
  contractData.con_req_no = null;
  contractData.aud_user = null;
  contractData.con_bu = null;
  contractData.description = null;
  contractData.bu = null;
  contractData.cc = [];
  contractData.wc = [];
  contractData.con_work_location = null;
  contractData.con_duration_start = null;
  contractData.con_duration_end = null;
  contractData.suggest_vendor = null;
  // Form reset
  refVForm.value?.reset()
  refVForm.value?.resetValidation()
  loadingBtn.value[0] = false;
  loadingBtnSecond.value[0] = false;
  emit('update:isDialogVisible', false)
  emit('isSuccessNextStep',false)
}

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
}

const validateCreateSPK = async () => {
  refSPKForm.value?.validate().then(valid => {
    if (valid.valid) {
      onSubmitCreateSPK()
    } else {
      isCurrentStepValid.value = false
    }
  })
}

const validatePreviewSPK = () => {
  refSPKPreviewForm.value?.validate().then(valid => {
    if (valid.valid) {
      isCurrentStepValid.value = true
      onSubmitPreviewSPK()
    } else {
      isCurrentStepValid.value = false
    }
  })
}

const fetchMerVendorData = async () => {
  try {
    const response = await getListMerVendor();
    if (response.status === 200) {

      const rows = response.data.rows || [];
      dataMerVendor.value = rows.map((row) => ({
        title: row.vnd_name,
        value: row.vnd_id,
      }));
      allDataVendor.value = rows;
    } else {
      console.error('Failed to fetch mer vendor data');
    }
    
  } catch (error) {
    console.error('Error fetching mer vendor data');
  }
}

const fetchContractList = async () => {
  try {
    const response = await getListContract();
    if (response.status === 200) {

      const rows = response.data.rows || [];
      dataContractList.value = rows.map((row) => ({
        title: row.con_req_no,
        value: row.con_req_id,
      }));
    } else {
      console.error('Failed to fetch contract data');
    }
    
  } catch (error) {
    console.error('Error fetching contract data');
  }
}

const fetchSignatureTypeList = async () => {
  try {
    const response = await getListSignatureType();
    if (response.status === 200) {

      const rows = response.data.rows || [];
      dataSignatureTypeList.value = rows;
    } else {
      console.error('Failed to fetch signature type data');
    }
    
  } catch (error) {
    console.error('Error fetching signature type data');
  }
}

const fetchContractEdit = async (conReqId) => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/contract/edit/${conReqId}`, {
      method: 'GET',
      onResponseError({ response }) {
        const responseData = response._data;
        const responseMessage = responseData.message;
        const responseErrors = responseData.errors;
        emit('errors', responseErrors);
        emit('errorMessages', responseMessage);
        throw new Error("Get data failed");
      },
    });
    
    const dataResponse = JSON.parse(JSON.stringify(response));
    if (dataResponse.status == 200) {
      const dataResult = dataResponse.data;
      contractData.bu = (dataResult.con_bu != null && dataResult.con_bu != '' ? dataResult.con_bu + ' - ' : '') + dataResult.description;
      contractData.cc = dataResult.cc.length > 0 ? dataResult.cc.map(cc => cc.tbc_code + ' - ' + cc.description).join(', ') : '-';
      contractData.wc = dataResult.wc.length > 0 ? dataResult.wc.map(wc => wc.tbc_code + ' - ' + wc.description).join(', ') : '-';
      contractData.con_work_location = dataResult.con_work_location != null && dataResult.con_work_location != '' ? dataResult.con_work_location : '-';
      contractData.aud_user = dataResult.aud_user;
      contractData.con_req_no = dataResult.con_req_no;
      contractData.con_duration_start = dataResult.con_duration_start;
      contractData.con_duration_end = dataResult.con_duration_end;
      vendorData.vnd_name = dataResult.vnd_name;
      await fetchVendorEdit(dataResult.ven_id)
    } else {
      emit('isSnackbarResponse',true)
      emit('isSnackbarResponseAlertColor', 'error')
      throw new Error("Get data failed");
    }
  } catch (error) {
    emit('isSnackbarResponse',true)
    emit('isSnackbarResponseAlertColor', 'error')
  }
}

const fetchContractJobEdit = async (conReqId) => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/contract-job/edit/${conReqId}`, {
      method: 'GET',
      onResponseError({ response }) {
        const responseData = response._data;
        const responseMessage = responseData.message;
        const responseErrors = responseData.errors;
        emit('errors', responseErrors);
        emit('errorMessages', responseMessage);
        throw new Error("Get data failed");
      },
    });
    
    const dataResponse = JSON.parse(JSON.stringify(response));
    if (dataResponse.status == 200) {
      const dataResult = dataResponse.data;
      isLoading.value = false;
      contractJobData.job_type = dataResult.job_type.length > 0 ? dataResult.job_type : [];
      contractJobData.contract_job = dataResult.contract_job.length > 0 ? dataResult.contract_job : [];
      contractJobData.job_labor = dataResult.job_labor.length > 0 ? dataResult.job_labor : [];
    } else {
      emit('isSnackbarResponse',true)
      emit('isSnackbarResponseAlertColor', 'error')
      throw new Error("Get data failed");
    }
  } catch (error) {
    emit('isSnackbarResponse',true)
    emit('isSnackbarResponseAlertColor', 'error')
  }
}

const fetchVendorEdit = async (vendId) => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/mer-vendor/edit/${vendId}`, {
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
      contractData.suggest_vendor = dataResult.vnd_id;
    } else {
      emit('update:isDialogVisible', false)
      emit('isSnackbarResponse',true)
      emit('isSnackbarResponseAlertColor', 'error')
      throw new Error("Get data failed");
    }
  } catch (error) {
    emit('update:isDialogVisible', false)
    emit('isSnackbarResponse',true)
    emit('isSnackbarResponseAlertColor', 'error')
  }
}

const onSubmitCreateSPK = async () => {
  try {
    loadingBtn.value[0] = true
    const mode = props.typeDialog;
    const payload = {
      con_req_id: contractData.con_req_id,
      cjb_id: contractJobData.contract_job.length > 0 ? contractJobData.contract_job.map((ci) => ci.cjb_id) : [],
      unt_id: contractJobData.contract_job.length > 0 ? contractJobData.contract_job.map((ci) => ci.unt_id) : [],
      cjb_pay_type: contractJobData.contract_job.length > 0 ? contractJobData.contract_job.map((cpt) => cpt.cjb_pay_type) : [],
      cjb_pay_template: contractJobData.contract_job.length > 0 ? contractJobData.contract_job.map((cpt) => cpt.cjb_pay_template) : [],
      cjb_rate: contractJobData.contract_job.length > 0 ? contractJobData.contract_job.map((cr) => cr.cjb_rate) : [],
      cjb_desc: contractJobData.contract_job.length > 0 ? contractJobData.contract_job.map((cd) => cd.cjb_desc) : [],
      suggest_vendor: contractData.suggest_vendor,
      duration: SPKData.duration,
      signature_type: SPKData.signature_type,
      spk_jobdesc_summary: SPKData.spk_jobdesc_summary,
    }
    
    emit("createSPK", { mode, formData: { ...payload }, dialogUpdate: dialogModelValueUpdate });
  } catch (err) {
    loadingBtn.value[0] = false
  }
}

const onSubmitPreviewSPK = async () => {
  try {
      loadingBtn.value[0] = true
      const mode = props.typeDialog;
      emit("previewSPK", { mode, formData: { ...contractData }, dialogUpdate: dialogModelValueUpdate });
  } catch (err) {
    loadingBtn.value[0] = false
  }
}

const printSPK = async () => {
  try {
      loadingBtnSecond.value[0] = true
      const mode = props.typeDialog;
      emit("printSPK", { mode, formData: { ...contractData }, dialogUpdate: dialogModelValueUpdate });
  } catch (err) {
    loadingBtnSecond.value[0] = false
  }

}

watch(
  [() => contractReqNo.value, () => contractReqId.value, () => typeDialog.value, () => props.fetchTrigger, () => props.isSuccessNextStep],
    ([newConreqNo,newConreqId,newType]) => {
      if (newType === "Add") {
        isLoading.value = false;
        if(props.isSuccessNextStep) {
          currentStep.value++
          isCurrentStepValid.value = true
        } else {
          currentStep.value--
        }
      }
      fetchMerVendorData()
      fetchContractList()
      fetchSignatureTypeList()
      loadingBtn.value[0] = false;
      loadingBtnSecond.value[0] = false;
  },
  { immediate: true }
)

watch([() => contractData.con_req_id], (conReqId) => {
    if(conReqId != '') {
      fetchContractEdit(conReqId)
      fetchContractJobEdit(conReqId)
    }
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

    <VCard class="pa-6 pa-sm-12">
        <VCardText>
          <!-- Stepper -->
          <AppStepper
            v-model:current-step="currentStep"
            :items="numberedSteps"
            :is-active-step-valid="isCurrentStepValid"
          />
        </VCardText>
          
        <VDivider />

        <VCardText>
          <VWindow v-model="currentStep" class="disable-tab-transition">
            <VWindowItem>
              <VForm
                ref="refSPKForm"
                @submit.prevent="validateCreateSPK"
                lazy-validation
              >
                <VRow>
                  <VCol cols="12">
                    <h5>(*) Is required</h5>
                  </VCol>
                  <VCol cols="12" md="4">
                    <AppAutocomplete
                      label="Request/ PPS No* (multiple select)"
                      v-model="contractData.con_req_id"
                      :items="dataContractList"
                      placeholder="Select PPS number"
                      chips
                      multiple
                      closable-chips
                      :rules="[requiredValidator]"
                      :item-title="'title'"
                      :item-value="'value'"
                      :error-messages="props.errors?.con_req_id"
                      clearable
                    />
                  </VCol>
                  <!-- <div class="card__image mt-5" >
                    <Skeletor height="250" />
                  </div> -->
                  <!-- Detail Table -->
                  <VCol cols="12" v-if="contractData.con_req_id && contractData.con_req_id != ''">
                    <VTable class="border text-high-emphasis overflow-hidden mt-6" fixed-header>
                      <thead>
                        <tr>
                          <th scope="col">
                            Request No
                          </th>
                          <th
                            scope="col"
                            class="text-center"
                          >
                            User Name	
                          </th>
                          <th
                            scope="col"
                            class="text-center"
                          >
                            Business Unit
                          </th>
                          <th
                            scope="col"
                            class="text-center"
                          >
                            Cost Center
                          </th>
                          <th
                            scope="col"
                            class="text-center"
                          >
                            Work Center
                          </th>
                          <th
                            scope="col"
                            class="text-center"
                          >
                            Work Location
                          </th>
                          <th
                            scope="col"
                            class="text-center"
                          >
                            Vendor
                          </th>
                          <th
                            scope="col"
                            class="text-center"
                          >
                            Planning Time Duration
                          </th>
                        </tr>
                      </thead>
                      <tbody class="text-base">
                        <tr>
                          <td>
                            {{ contractData.con_req_no || '-' }}
                          </td>
                          <td>
                            {{ contractData.aud_user || '-' }}
                          </td>
                          <td>
                            {{ contractData.bu || '-' }}
                          </td>
                          <td>
                            {{ contractData.cc || '-' }}
                          </td>
                          <td>
                            {{ contractData.wc || '-' }}
                          </td>
                          <td>
                            {{ contractData.con_work_location || '-' }}
                          </td>
                          <td>
                            {{ vendorData.vnd_name || '-' }}
                          </td>
                          <td>
                            {{ formatDate(contractData.con_duration_start) + ' - ' + formatDate(contractData.con_duration_end) }}
                          </td>
                        </tr>
                      </tbody>
                    </VTable>
                  </VCol>
                  <!-- Detail Second Table -->
                  <VCol cols="12" v-if="contractData.con_req_id && contractData.con_req_id != ''">
                    <VTable class="border text-high-emphasis overflow-hidden mt-4" fixed-header>
                      <thead>
                        <tr>
                          <th scope="col">
                            No
                          </th>
                          <th scope="col">
                            Job Type
                          </th>
                          <th
                            scope="col"
                            class="text-center"
                          >
                            Job Description	
                          </th>
                          <th
                            scope="col"
                            class="text-center"
                          >
                            Job Target Qty
                          </th>
                          <th
                            scope="col"
                            class="text-center"
                          >
                            UoM
                          </th>
                          <th
                            scope="col"
                            class="text-center"
                          >
                            Payment Type
                          </th>
                          <th
                            scope="col"
                            class="text-center"
                          >
                            Payment Template
                          </th>
                          <th
                            scope="col"
                            class="text-center"
                          >
                            Rate
                          </th>
                        </tr>
                      </thead>
                      <tbody class="text-base" v-for="(data, index) in contractJobData.contract_job" :key="data.cjb_id">
                        <tr>
                          <td>
                            {{ index + 1 }}
                          </td>
                          <td>
                            {{ contractJobData.job_type.find((jy) => jy.cjb_id == data.cjb_id)?.job_type || '-' }}
                          </td>
                          <td style="width: 300px !important;">
                            <AppTextarea
                              v-model="data.cjb_desc"
                              placeholder="Type here..."
                              :rules="[requiredValidator]"
                              :error-messages="props.errors?.spk_job_description"
                              auto-grow
                              clearable
                            />
                          </td>
                          <td>
                            {{ data.cjb_qty || 0 }}
                          </td>
                          <td>
                            {{ data.unt_id || '-' }}
                          </td>
                          <td style="width: 200px;">
                            <AppAutocomplete
                              placeholder="Select payment type"
                              v-model="data.cjb_pay_type"
                              :rules="[requiredValidator]"
                              :items="dataMerPaymentType"
                              :item-title="'title'"
                              :item-value="'value'"
                              :error-messages="props.errors?.cjb_pay_type"
                              clearable
                            />
                          </td>
                          <td style="width: 200px;">
                            <AppAutocomplete
                              placeholder="Select payment template"
                              v-model="data.cjb_pay_template"
                              :rules="[requiredValidator]"
                              :items="dataMerPaymentTemplate"
                              :item-title="'title'"
                              :item-value="'value'"
                              :error-messages="props.errors?.cjb_pay_template"
                              clearable
                            />
                          </td>
                          <td style="width: 200px;">
                            <AppTextField
                              v-model="data.cjb_rate"
                              placeholder="Type here..."
                              :rules="[requiredValidator]"
                              :error-messages="props.errors?.cjb_rate"
                              clearable
                            />
                          </td>
                        </tr>
                        <tr>
                          <td colspan="8">
                            <VTable class="border collapse mt-3 mb-5">
                              <thead>
                                <tr>
                                  <th scope="col">
                                    Labor Type
                                  </th>
                                  <th scope="col">
                                    Labor QTY
                                  </th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr>
                                  <td>
                                    {{ contractJobData.job_labor.find((jl) => jl.cjb_id == data.cjb_id)?.cjl_type || '-' }}
                                  </td>
                                  <td>
                                    {{ contractJobData.job_labor.find((jl) => jl.cjb_id == data.cjb_id)?.cjl_qty || 0 }}
                                  </td>
                                </tr>
                              </tbody>
                            </VTable>
                          </td>
                        </tr>
                      </tbody>
                    </VTable>
                  </VCol>
                </VRow>
                <VDivider class="my-6 border-dashed" />
                <VRow>
                  <!-- Form -->
                  <VCol cols="12" md="6">
                    <AppAutocomplete
                      label="Vendor Name*"
                      placeholder="Select suggest vendor"
                      v-model="contractData.suggest_vendor"
                      :rules="[requiredValidator]"
                      :items="dataMerVendor"
                      :item-title="'title'"
                      :item-value="'value'"
                      :error-messages="props.errors?.suggest_vendor"
                      clearable
                      />
                  </VCol>
                  <VCol cols="12" md="6" class="mt-1">
                    <AppTextField
                    placeholder="Placeholder Text"
                    label="Vendor PIC Name*"
                    :value="allDataVendor.find((data) => data.vnd_id == contractData.suggest_vendor)?.vnd_contact_person || 'No PIC Found'"
                    append-inner-icon="tabler-user"
                    readonly
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <AppDateTimePicker
                    label="SPK Time Duration*"
                    v-model="SPKData.duration"
                    placeholder="Select range date"
                    :config="{ mode: 'range' }"
                    :rules="[requiredValidator]"
                    :error-messages="props.errors?.duration"
                    clearable
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VRadioGroup
                      v-model="SPKData.signature_type"
                      inline
                      label="Signature Type*"
                      :rules="[requiredValidator]"
                      :error-messages="props.errors?.signature_type"
                    >
                      <VRadio
                        v-for="(data,index) in dataSignatureTypeList" :key="data.st_id"
                        :label="data.st_desc"
                        :value="data.st_id"
                        density="compact"
                      />
                    </VRadioGroup>
                  </VCol>
                  <VCol cols="12" md="6">
                    <AppTextarea
                      v-model="SPKData.spk_jobdesc_summary"
                      label="Job Description Summary*"
                      rows="2"
                      placeholder="Type here..."
                      :rules="[requiredValidator]"
                      :error-messages="props.errors?.spk_jobdesc_summary"
                      auto-grow
                      clearable
                    />
                  </VCol>
                </VRow>
                <VRow>
                  <VCol cols="12">
                    <div class="d-flex flex-wrap gap-4 justify-end mt-8">
                      <!-- <div class="card__actions d-flex justify-end" >
                        <Skeletor width="96" height="36" />
                      </div> -->
                      <VBtn
                        :loading="loadingBtn[0]"
                        :disabled="loadingBtn[0]"
                        color="success"
                        type="submit"
                      >
                        Save & Next
                        <VIcon
                          icon="tabler-arrow-right"
                          end
                          class="flip-in-rtl"
                        />
                      </VBtn>
                    </div>
                  </VCol>
                </VRow>
              </VForm>
            </VWindowItem>
            <VWindowItem>
              <VForm ref="refSPKPreviewForm" @submit.prevent="validatePreviewSPK">
                <VRow>
                  <VCol cols="12">
                    <div class="d-flex flex-wrap gap-4 justify-end mt-8">
                        <!-- <div class="card__actions d-flex justify-end" >
                          <Skeletor width="96" height="36" class="me-3"/>
                          <Skeletor width="96" height="36" />
                        </div> -->
                        <VBtn
                        :loading="loadingBtn[0]"
                        :disabled="loadingBtn[0]"
                        color="success"
                        type="submit"
                        >
                        Save
                        <VIcon
                          icon="tabler-device-floppy"
                          end
                          class="flip-in-rtl"
                        />
                      </VBtn>
                      <VBtn
                      :loading="loadingBtnSecond[0]"
                        :disabled="loadingBtnSecond[0]"
                        variant="outlined"
                        color="primary"
                        type="submit"
                        @click="printSPK()"
                      >
                        Print
                        <VIcon
                          icon="tabler-printer"
                          end
                          class="flip-in-rtl"
                        />
                      </VBtn>
                    </div>
                  </VCol>
                </VRow>
              </VForm>
            </VWindowItem>
          </VWindow>
        </VCardText>
      </VCard>
  </VDialog>
  <VendorViewPathDialog
    v-model:isDialogViewPathVisible="isDialogViewPathVisible"
  />
</template>
