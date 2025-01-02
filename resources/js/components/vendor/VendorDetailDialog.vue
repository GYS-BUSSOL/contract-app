<script setup>
import { getListMerPaymentTemplate, getListMerPaymentType, getListMerVendor } from '@db/apps/mer/db';
import dayjs from "dayjs";
import 'vue-skeletor/dist/vue-skeletor.css';

const emit = defineEmits([
  'update:isDialogVisible',
  'vendorData',
  'isSnackbarResponse',
  'isSnackbarResponseAlertColor',
  'errorMessages',
  'errors'
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
  }
})

const isLoading = ref(true)
const refVForm = ref()
const typeDialog = computed(() => props.typeDialog)
const contractReqNo = computed(() => props.contractReqNo)
const contractReqId = computed(() => props.contractReqId)
const loadingBtn = ref([])
const loadingBtnSecond = ref([])
const pathData = ref('')
const isDialogViewPathVisible = ref(false)
const token = useCookie('accessToken')
const contractData = reactive({
  vendor_name: null,
  bu: null,
  cc: [],
  wc: [],
  con_work_location: null,
  con_id_project: null,
  con_pps_no: null,
  con_old_pps_no: null,
  con_priority_id: null,
  shift: [],
  aud_user: null,
  con_cp_name: null,
  con_cp_dept: null,
  con_cp_exthp: null,
  con_cp_email: null,
  con_comment_bu: null,
  con_duration_start: null,
  con_duration_end: null,
  con_file_attachment: null,
  vnd_contact_person: null,
  suggest_vendor: null
})
const contractJobData = reactive({
  contract_job: [],
  job_type: [],
  job_labor: [],
  con_comment_pbl_reject: null
})
const dataMerVendor = ref([])
const dataMerPaymentType = ref([])
const dataMerPaymentTemplate = ref([])
const allDataVendor = ref([])

const dialogModelValueUpdate = () => {
  contractJobData.contract_job = [];
  contractJobData.job_type = [];
  contractJobData.job_labor = [];
  contractJobData.con_comment_pbl_reject = null;
  refVForm.value?.reset()
  refVForm.value?.resetValidation()
  loadingBtn.value[0] = false;
  loadingBtnSecond.value[0] = false;
  emit('update:isDialogVisible', false)
}

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
}

const openPathDialog = (path) => {
  pathData.value = path;
  isDialogViewPathVisible.value = true;
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

const fetchMerPaymentType = async () => {
  try {
    const response = await getListMerPaymentType();
    if (response.status === 200) {

      const rows = response.data.rows || [];
      dataMerPaymentType.value = rows.map((row) => ({
        title: row.paytype_name,
        value: row.paytype_code,
      }));
    } else {
      console.error('Failed to fetch mer payment type data');
    }
    
  } catch (error) {
    console.error('Error fetching mer payment type data');
  }
}

const fetchMerPaymentTemplate = async () => {
  try {
    const response = await getListMerPaymentTemplate();
    if (response.status === 200) {

      const rows = response.data.rows || [];
      dataMerPaymentTemplate.value = rows.map((row) => ({
        title: row.payment_name,
        value: row.payment_code,
      }));
    } else {
      console.error('Failed to fetch mer payment template data');
    }
    
  } catch (error) {
    console.error('Error fetching mer payment template data');
  }
}

const fetchContractEdit = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/contract/edit/${contractReqNo.value}`, {
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
      contractData.company_name = dataResult.company_name;
      contractData.bu = (dataResult.con_bu != null && dataResult.con_bu != '' ? dataResult.con_bu + ' - ' : '') + dataResult.description;
      contractData.cc = dataResult.cc.length > 0 ? dataResult.cc.map(cc => cc.tbc_code + ' - ' + cc.description).join(', ') : '-';
      contractData.wc = dataResult.wc.length > 0 ? dataResult.wc.map(wc => wc.tbc_code + ' - ' + wc.description).join(', ') : '-';
      contractData.con_work_location = dataResult.con_work_location != null && dataResult.con_work_location != '' ? dataResult.con_work_location : '-';
      contractData.con_id_project = dataResult.con_id_project != null && dataResult.con_id_project != '' ? dataResult.con_id_project : '-';
      contractData.con_pps_no = dataResult.con_pps_no != null && dataResult.con_pps_no != '' ? dataResult.con_pps_no : '-';
      contractData.con_old_pps_no = dataResult.con_old_pps_no != null && dataResult.con_old_pps_no != '' ? dataResult.con_old_pps_no : '-';
      contractData.con_priority_id = dataResult.con_priority_id == 1 ? "Segera" : "Tidak segera";
      contractData.shift = dataResult.shift.length > 0 ? dataResult.shift.map(shift => shift.sh_shift == 4 ? 'Non Shift' : 'Shift '+ shift.sh_shift).join(', ') : '-';
      contractData.aud_user = dataResult.aud_user;
      contractData.con_cp_name = dataResult.con_cp_name;
      contractData.con_cp_dept = dataResult.con_cp_dept;
      contractData.con_cp_exthp = dataResult.con_cp_exthp;
      contractData.con_cp_email = dataResult.con_cp_email;
      contractData.con_comment_bu= dataResult.con_comment_bu != null && dataResult.con_comment_bu != '' ? dataResult.con_comment_bu : '-';
      contractData.con_duration_start = dataResult.con_duration_start;
      contractData.con_duration_end = dataResult.con_duration_end;
      contractData.con_file_attachment = dataResult.con_file_attachment;
      contractData.vnd_contact_person = dataResult.vnd_contact_person;
      contractData.suggest_vendor = dataResult.vnd_id;
      // contractData.BU = dataResult.bu_id.split(",")
      //   .map((id) => parseInt(id.trim(), 10));
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
    throw new Error("Get data failed");
  }
}

const fetchContractJobEdit = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/contract-job/list/${contractReqId.value}`, {
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
      contractJobData.job_type = dataResult.job_type.length > 0 ? dataResult.job_type : [];
      contractJobData.contract_job = dataResult.contract_job.length > 0 ? dataResult.contract_job : [];
      contractJobData.job_labor = dataResult.job_labor.length > 0 ? dataResult.job_labor : [];
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
    throw new Error("Get data failed");
  }
}

const validateCreate = async (type) => {
  if(type == 'Submit') {
    refVForm.value?.validate().then(valid => {
      if (valid.valid) {
        onSubmit()
      }
    })
  } else if(type == 'Reject') {
    onReject(type)
  }
}

const onSubmit = async () => {
  refVForm.value?.validate().then(({ valid }) => {
    try {
      if (valid) {
        loadingBtn.value[0] = true
        const mode = props.typeDialog;
        const payload = {
          con_req_id: contractReqId.value,
          cjb_id: contractJobData.contract_job.length > 0 ? contractJobData.contract_job.map((ci) => ci.cjb_id) : [],
          cjb_pay_type: contractJobData.contract_job.length > 0 ? contractJobData.contract_job.map((cpt) => cpt.cjb_pay_type) : [],
          cjb_pay_template: contractJobData.contract_job.length > 0 ? contractJobData.contract_job.map((cpt) => cpt.cjb_pay_template) : [],
          cjb_rate: contractJobData.contract_job.length > 0 ? contractJobData.contract_job.map((cr) => cr.cjb_rate) : [],
          suggest_vendor: contractData.suggest_vendor
        }
        emit("vendorData", { mode, formData: { ...payload }, dialogUpdate: dialogModelValueUpdate });
      } else {
        loadingBtn.value[0] = false
      }
    } catch (err) {
      loadingBtn.value[0] = false
    }
  })
}

const onReject = async (type) => {
    try {
      loadingBtnSecond.value[0] = true
      const mode = type;
      const payload = {
        con_req_id: contractReqId.value,
        con_comment_pbl_reject: contractJobData.con_comment_pbl_reject,
      }
      emit("vendorData", { mode, formData: { ...payload }, dialogUpdate: dialogModelValueUpdate });
    } catch (err) {
      loadingBtnSecond.value[0] = false
    }
}

watch(
  [() => contractReqNo.value, () => contractReqId.value, () => typeDialog.value, () => props.fetchTrigger],
    ([newConreqNo,newConreqId,newType]) => {
      if (newType === "Add" && newConreqNo && newConreqId) {
        fetchContractEdit()
        fetchContractJobEdit()
      }
      fetchMerVendorData()
      fetchMerPaymentType()
      fetchMerPaymentTemplate()
      loadingBtn.value[0] = false;
      loadingBtnSecond.value[0] = false;
  },
  { immediate: true }
);
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
        <VForm
          ref="refVForm"
          lazy-validation
        >
          <VExpansionPanels variant="accordion" class="expansion-panels-width-border">
            <VExpansionPanel>
              <Skeletor height="43" v-if="isLoading"/>
              <VExpansionPanelTitle v-if="!isLoading">
                Contract Detail
              </VExpansionPanelTitle>
              <VExpansionPanelText v-if="!isLoading">
                <div class="invoice-header-preview d-flex justify-space-between flex-row print-row gap-6 rounded pa-6 mb-6">
                  <!-- Left Content -->
                  <div>
                    <VTable>
                      <tbody>
                        <tr>
                          <td><strong>Company</strong></td>
                          <td>:</td>
                          <td>{{ contractData.company_name }}</td>
                        </tr>
                        <tr>
                          <td><strong>Business Unit</strong></td>
                          <td>:</td>
                          <td>{{ contractData.bu }}</td>
                        </tr>
                        <tr>
                          <td><strong>Cost Center</strong></td>
                          <td>:</td>
                          <td>{{ contractData.cc }}</td>
                        </tr>
                        <tr>
                          <td><strong>Work Center</strong></td>
                          <td>:</td>
                          <td>{{ contractData.wc }}</td>
                        </tr>
                        <tr>
                          <td><strong>Work Location</strong></td>
                          <td>:</td>
                          <td>{{ contractData.con_work_location }}</td>
                        </tr>
                        <tr>
                          <td><strong>ID Project</strong></td>
                          <td>:</td>
                          <td>{{ contractData.con_id_project }}</td>
                        </tr>
                        <tr>
                          <td><strong>PPS No</strong></td>
                          <td>:</td>
                          <td>{{ contractData.con_pps_no }}</td>
                        </tr>
                        <tr>
                          <td><strong>Old PPS No</strong></td>
                          <td>:</td>
                          <td>{{ contractData.con_old_pps_no }}</td>
                        </tr>
                        <tr>
                          <td><strong>Priority</strong></td>
                          <td>:</td>
                          <td>{{ contractData.con_priority_id }}</td>
                        </tr>
                        <tr>
                          <td><strong>Shift</strong></td>
                          <td>:</td>
                          <td>{{ contractData.shift }}</td>
                        </tr>
                      </tbody>
                    </VTable>
                  </div>
                  <!-- Right Content -->
                  <div>
                    <VTable class="left-content">
                      <tbody>
                        <tr>
                          <td><strong>User Name</strong></td>
                          <td>:</td>
                          <td>{{ contractData.aud_user }}</td>
                        </tr>
                        <tr>
                          <td><strong>Contact Person</strong></td>
                          <td>:</td>
                          <td>{{ contractData.con_cp_name }}</td>
                        </tr>
                        <tr>
                          <td><strong>Dep - Jabatan</strong></td>
                          <td>:</td>
                          <td>{{ contractData.con_cp_dept }}</td>
                        </tr>
                        <tr>
                          <td><strong>Ext - HP</strong></td>
                          <td>:</td>
                          <td>{{ contractData.con_cp_exthp }}</td>
                        </tr>
                        <tr>
                          <td><strong>Email</strong></td>
                          <td>:</td>
                          <td>{{ contractData.con_cp_email }}</td>
                        </tr>
                        <tr>
                          <td><strong>Comment</strong></td>
                          <td>:</td>
                          <td>{{ contractData.con_comment_bu }}</td>
                        </tr>
                        <tr>
                          <td><strong>Planning Time Duration</strong></td>
                          <td>:</td>
                          <td>{{ formatDate(contractData.con_duration_start) + ' until ' + formatDate(contractData.con_duration_end) }}</td>
                        </tr>
                        <tr>
                          <td><strong>File Attachment</strong></td>
                          <td>:</td>
                          <td @click="openPathDialog(contractData.con_file_attachment)" class="text-primary cursor-pointer">
                            <VBtn style="box-shadow: none;" icon color="transparent">
                              <VIcon icon="tabler-file" />
                              <VTooltip open-delay="200" location="top" activator="parent">
                                <span>View file</span>
                              </VTooltip>
                            </VBtn>
                            <span>File attachment</span>
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Vendor Name</strong></td>
                          <td>:</td>
                          <td>
                            <AppAutocomplete
                              placeholder="Select suggest vendor"
                              v-model="contractData.suggest_vendor"
                              :rules="[requiredValidator]"
                              :items="dataMerVendor"
                              :item-title="'title'"
                              :item-value="'value'"
                              :error-messages="props.errors?.suggest_vendor"
                              clearable
                              />
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Vendor PIC Name</strong></td>
                          <td>:</td>
                          <td>
                            {{
                              allDataVendor.find((data) => data.vnd_id === contractData.suggest_vendor)?.vnd_contact_person || 'No PIC Found'
                            }}
                          </td>                      
                        </tr>
                      </tbody>
                    </VTable>
                  </div>
                </div>
              </VExpansionPanelText>
            </VExpansionPanel>
          </VExpansionPanels>
          <div class="card__image mt-5" v-if="isLoading">
            <Skeletor height="250" />
          </div>
          <!-- Detail -->
          <VTable class="border text-high-emphasis mt-6" :style="contractJobData.contract_job.length <= 2 ? 'height:max-content;' : 'height:80vh;'" fixed-header  v-if="!isLoading">
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

            <tbody class="text-base" v-for="(data,index) in contractJobData.contract_job" :key="data.cjb_id">
              <tr>
                <td>
                  <VChip
                    label
                    size="small"
                    color="secondary"
                  >
                    {{ index + 1 }}
                  </VChip>
                </td>
                <td>
                  <template v-if="contractJobData.job_type.length > 0">
                    <template v-for="(innerArray, indexOuter) in contractJobData.job_type" :key="indexOuter">
                      {{ 
                        innerArray
                          .filter(jy => jy.cjb_id == data.cjb_id)
                          .map(jy => jy.job_type)
                          .join(', ')
                      }}
                    </template>
                  </template>                
                </td>
                <td>
                  {{ data.cjb_desc || '-' }}
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
                  <VTooltip open-delay="200" location="top" activator="parent" v-if="data.cjb_pay_type != null && data.cjb_pay_type != ''">
                    <span>{{ dataMerPaymentType.find((mpt) => mpt.value == data.cjb_pay_type)?.title }}</span>
                  </VTooltip>
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
                  <VTooltip open-delay="200" location="top" activator="parent" v-if="data.cjb_pay_template != null && data.cjb_pay_template != ''">
                    <span>{{ dataMerPaymentTemplate.find((mpt) => mpt.value == data.cjb_pay_template)?.title }}</span>
                  </VTooltip>
                </td>
                <td style="width: 200px;">
                  <AppTextField
                    v-model="data.cjb_rate"
                    placeholder="Type here..."
                    :rules="[requiredValidator]"
                    :error-messages="props.errors?.cjb_rate"
                    type="number"
                    clearable
                  />
                  <VTooltip open-delay="200" location="top" activator="parent" v-if="data.cjb_rate != null && data.cjb_rate != ''">
                    <span>{{ data.cjb_rate }}</span>
                  </VTooltip>
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
                      <template v-if="contractJobData.job_labor.length > 0" v-for="(innerArray, indexOuter) in contractJobData.job_labor" :key="indexOuter">
                        <tr v-for="(dataCb, indexCb) in innerArray" :key="dataCb.cjb_id">
                          <td v-if="dataCb.cjb_id == data.cjb_id">
                            {{ dataCb.cjl_type }}
                          </td>
                          <td v-if="dataCb.cjb_id == data.cjb_id">
                            {{ dataCb.cjl_qty }}
                          </td>
                        </tr>
                      </template>
                      <tr v-else>
                        <td colspan="2">
                          Data is empty
                        </td>
                      </tr>
                    </tbody>
                  </VTable>
                  <VDivider class="my-6 border-dashed" />
                </td>
              </tr>
            </tbody>
          </VTable>
          <div class="mt-4">
            <Skeletor width="125" height="26" class="mb-2" v-if="isLoading"/>
            <h6 class="text-h6 mb-2" v-if="!isLoading">
              Comment Reject:
            </h6>
            <Skeletor height="80" v-if="isLoading"/>
            <VTextarea v-if="!isLoading"
              v-model="contractJobData.con_comment_pbl_reject"
              placeholder="Write comment here..."
              :rows="2"
            />
          </div>
          <VRow class="d-flex justify-end mt-5">
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
                @click="validateCreate('Submit')"
              >
                Submit
              </VBtn>
              <VBtn
                :loading="loadingBtnSecond[0]"
                :disabled="loadingBtnSecond[0]"
                color="error"
                variant="tonal"
                @click="validateCreate('Reject')"
              >
                Reject
              </VBtn>
            </div>
          </VRow>
        </VForm>
      </VCard>
  </VDialog>
  <VendorViewPathDialog
    v-model:isDialogViewPathVisible="isDialogViewPathVisible"
    :path-data="pathData"
  />
</template>
