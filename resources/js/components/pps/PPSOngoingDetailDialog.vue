<script setup>
import { getListMerPaymentTemplate, getListMerPaymentType, getListMerVendor } from '@db/apps/mer/db';
import dayjs from "dayjs";
import 'vue-skeletor/dist/vue-skeletor.css';

const emit = defineEmits([
  'update:isDialogVisible',
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
const typeDialog = computed(() => props.typeDialog)
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
  job_target: [],
})
const dataMerVendor = ref([])
const dataMerPaymentType = ref([])
const dataMerPaymentTemplate = ref([])
const valuationData = ref([])
const historyData = ref([])
const allDataVendor = ref([])

const dialogModelValueUpdate = () => {
  contractJobData.contract_job = [];
  contractJobData.job_type = [];
  contractJobData.job_labor = [];
  contractJobData.job_target = [];
  // Falsing button
  loadingBtn.value[0] = false;
  loadingBtnSecond.value[0] = false;
  emit('update:isDialogVisible', false)
  emit('updateTypeDialog')
}

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
}

const resolveStatusVariant = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === '16')
    return 'info'
  else if(statLowerCase === '15')
    return 'warning'
  else if(statLowerCase === '7')
    return 'error'
  return 'success'
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
      throw new Error("Failed to fetch mer vendor data");
    }
  } catch (error) {
    throw new Error("Failed to fetch mer vendor data");
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
      throw new Error("Failed to fetch mer payment type data");
    }
  } catch (error) {
    throw new Error("Failed to fetch mer payment type data");
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
      throw new Error("Failed to fetch mer payment template data");
    }
  } catch (error) {
    throw new Error("Failed to fetch mer payment template data");
  }
}

const fetchContractEdit = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/contract/edit/${contractReqId.value}`, {
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
      contractData.vnd_name = dataResult.vnd_name;
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

const fetchContractJobList = async () => {
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
      contractJobData.job_target = dataResult.job_target.length > 0 ? dataResult.job_target : [];
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

const fetchValuationList = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/valuation/list/${contractReqId.value}`, {
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
      const rows = dataResponse.data || [];
      valuationData.value = rows.map((row) => ({
        pn_id: row.pn_id,
        cjt_year: row.cjt_year,
        cjt_month: row.cjt_month,
        pn_rating: row.pn_rating,
        pn_total_nilai: row.pn_total_nilai,
        pn_rating_comment: row.pn_rating_comment != null && row.pn_rating_comment != '' ? row.pn_rating_comment : '-',
      }));
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

const fetchHistoryList = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/history/list/${contractReqId.value}`, {
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
        emit('errorMessages', responseMessage)
        emit('update:isDialogVisible', false)
        throw new Error("Get data failed")
      },
    });
    
    const dataResponse = JSON.parse(JSON.stringify(response));
    if (dataResponse.status == 200) {
      const rows = dataResponse.data || [];
      
      historyData.value = rows.map((row) => ({
        ths_id: row.ths_id,
        sts_id: row.sts_id,
        aud_user: row.aud_user,
        aud_date: dayjs(row.aud_date).format('YYYY-MM-DD'),
        sts_description: row.sts_description,
        ths_comment: row.ths_comment != null && row.ths_comment != '' ? row.ths_comment : '-',
      }));
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
    throw new Error("Get data failed")
  }
}

watch(
  [() => contractReqId.value, () => typeDialog.value, () => props.fetchTrigger],
    ([newConreqId,newType]) => {
      if (newType === "Detail" && newConreqId && newType != '') {
        fetchContractEdit()
        fetchContractJobList()
        fetchValuationList()
        fetchHistoryList()
        fetchMerVendorData()
        fetchMerPaymentType()
        fetchMerPaymentTemplate()
      }
      loadingBtn.value[0] = false;
      loadingBtnSecond.value[0] = false;
  }
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
        <VExpansionPanels variant="accordion" class="expansion-panels-width-border">
          <VExpansionPanel>
            <Skeletor height="55" v-if="isLoading"/>
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
                          {{ contractData.vnd_name }}
                        </td>
                      </tr>
                      <tr>
                        <td><strong>Vendor PIC Name</strong></td>
                        <td>:</td>
                        <td>
                          {{ allDataVendor.find((data) => data.vnd_id === contractData.suggest_vendor)?.vnd_contact_person || 'No PIC Found' }}
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
          <Skeletor class="mt-5 mb-3" height="30" width="90"/>
          <Skeletor height="250" />
        </div>
        <h3 class="mt-5" v-if="!isLoading">Job Detail</h3>
        <!-- Job Detail -->
        <VTable class="border text-high-emphasis mt-6" :height="contractJobData.contract_job.length <= 1 ? 'max-content' : '80vh'" fixed-header  v-if="!isLoading">
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
            <template v-if="contractJobData.contract_job.length > 0">
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
                <td class="text-center">
                  {{ data.cjb_qty || 0 }}
                </td>
                <td class="text-center">
                  {{ data.unt_id || '-' }}
                </td>
                <td class="text-center">
                  {{ dataMerPaymentType.find((mpt) => mpt.value == data.cjb_pay_type)?.title }}
                  <VTooltip open-delay="200" location="top" activator="parent" v-if="data.cjb_pay_type != null && data.cjb_pay_type != ''">
                    <span>{{ dataMerPaymentType.find((mpt) => mpt.value == data.cjb_pay_type)?.title }}</span>
                  </VTooltip>
                </td>
                <td class="text-center">
                  {{ dataMerPaymentTemplate.find((mpt) => mpt.value == data.cjb_pay_template)?.title }}
                  <VTooltip open-delay="200" location="top" activator="parent" v-if="data.cjb_pay_template != null && data.cjb_pay_template != ''">
                    <span>{{ dataMerPaymentTemplate.find((mpt) => mpt.value == data.cjb_pay_template)?.title }}</span>
                  </VTooltip>
                </td>
                <td class="text-center">
                  {{ data.cjb_rate }}
                  <VTooltip open-delay="200" location="top" activator="parent" v-if="data.cjb_rate != null && data.cjb_rate != ''">
                    <span>{{ data.cjb_rate }}</span>
                  </VTooltip>
                </td>
              </tr>
            </template>
            <tr>
              <td colspan="8">
                <VTable class="border collapse mt-3 mb-5">
                  <thead>
                    <tr>
                      <th scope="col">
                        Labor Type
                      </th>
                      <th scope="col" class="text-center">
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
                        <td class="text-center" v-if="dataCb.cjb_id == data.cjb_id">
                          {{ dataCb.cjl_qty }}
                        </td>
                      </tr>
                    </template>
                    <tr v-else>
                      <td colspan="2" class="text-center">
                        Data is empty
                      </td>
                    </tr>
                  </tbody>
                </VTable>
              </td>
            </tr>
            <tr>
              <td colspan="8">
                <VTable class="border collapse mt-3 mb-5">
                  <thead>
                    <tr>
                      <th scope="col">
                        Year-Month
                      </th>
                      <th scope="col">
                        Type
                      </th>
                      <th scope="col">
                        Increment
                      </th>
                      <th scope="col">
                        Total
                      </th>
                      <th scope="col">
                        Action
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <template v-if="contractJobData.job_target.length > 0" v-for="(innerArray, indexOuter) in contractJobData.job_target" :key="indexOuter">
                      <tr v-for="(dataCt, indexCb) in innerArray" :key="dataCt.cjt_id">                        
                        <td v-if="dataCt.cjb_id == data.cjb_id">
                          {{ dayjs(dataCt.cjt_year + '-' + dataCt.cjt_month).format('YYYY-MM') }}
                        </td>
                        <td v-if="dataCt.cjb_id == data.cjb_id">
                          {{ dataCt.cjt_type == '1' ? 'Same With UOM' : 'Percentage' }}
                        </td>
                        <td v-if="dataCt.cjb_id == data.cjb_id">
                          {{ dataCt.cjt_qty }}
                        </td>
                        <td v-if="dataCt.cjb_id == data.cjb_id">
                          {{ dataCt.cjt_total }}
                        </td>
                        <td v-if="dataCt.cjb_id == data.cjb_id">
                          <IconBtn @click="">
                            <VIcon icon="tabler-printer" />
                            <VTooltip open-delay="200" location="top" activator="parent">
                              <span>Print data</span>
                            </VTooltip>
                          </IconBtn>
                        </td>
                      </tr>
                    </template>
                    <tr v-else>
                      <td colspan="4" class="text-center">
                        Data is empty
                      </td>
                    </tr>
                  </tbody>
                </VTable>
                <VDivider v-if="index !== contractJobData.contract_job.length - 1" class="my-6 border-dashed" />
              </td>
            </tr>
          </tbody>
        </VTable>
        <p v-if="contractJobData.contract_job.length == 0 && !isLoading" class="text-center mt-5">Data is empty</p>
        <!-- History & BU Rate Service -->
        <VRow class="mt-5">
          <VCol cols="12" md="6">
            <VExpansionPanels variant="accordion" class="expansion-panels-width-border">
              <VExpansionPanel>
                <Skeletor height="55" v-if="isLoading"/>
                <VExpansionPanelTitle v-if="!isLoading">
                  History
                </VExpansionPanelTitle>
                <VExpansionPanelText v-if="!isLoading">
                  <VTable class="border collapse mt-3 mb-5" v-if="!isLoading" :height="historyData.length <= 5 ? 'max-content' : '300'" fixed-header>
                    <thead>
                      <tr>
                        <th scope="col">
                          Status
                        </th>
                        <th scope="col">
                          User
                        </th>
                        <th scope="col">
                          Date
                        </th>
                        <th scope="col">
                          Comment
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <template v-if="historyData.length > 0">
                        <tr v-for="(h, indexHistory) in historyData" :key="h.ths_id">                        
                          <td>
                            <VChip
                              :color="resolveStatusVariant(h.sts_id)"
                              size="small"
                              label
                              class="text-capitalize"
                            >
                              {{ h.sts_description }}
                            </VChip>
                          </td>
                          <td>
                            {{ h.aud_user }}
                          </td>
                          <td>
                            {{ h.aud_date }}
                          </td>
                          <td>
                            <div v-if="h.sts_id == '5' || h.sts_id == '4' || h.sts_id == '7'">
                              {{ h.ths_comment }}
                            </div>
                            <div v-else>
                              -
                            </div>
                          </td>
                        </tr>
                      </template>
                      <tr v-else>
                        <td colspan="3" class="text-center">
                          Data is empty
                        </td>
                      </tr>
                    </tbody>
                  </VTable>
                </VExpansionPanelText>
              </VExpansionPanel>
            </VExpansionPanels>
          </VCol>
          <VCol cols="12" md="6">
            <VExpansionPanels variant="accordion" class="expansion-panels-width-border">
              <VExpansionPanel>
                <Skeletor height="55" v-if="isLoading"/>
                <VExpansionPanelTitle v-if="!isLoading">
                  BU Rate Service
                </VExpansionPanelTitle>
                <VExpansionPanelText v-if="!isLoading">
                  <VTable class="border collapse mt-3 mb-5" v-if="!isLoading" :height="valuationData.length <= 5 ? 'max-content' : '300'" fixed-header>
                    <thead>
                      <tr>
                        <th scope="col">
                          Year-Month
                        </th>
                        <th scope="col">
                          Performance
                        </th>
                        <th scope="col">
                          Rating
                        </th>
                        <th scope="col">
                          Comment
                        </th>
                        <th scope="col">
                          Action
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <template v-if="valuationData.length > 0">
                        <tr v-for="(v, indexValuation) in valuationData" :key="v.pn_id">                        
                          <td>
                            {{ dayjs(v.cjt_year + '-' + v.cjt_month).format('YYYY-MM') }}
                          </td>
                          <td>
                            {{ v.pn_total_nilai }}
                          </td>
                          <td style="width: 200px;">
                            <VIcon size="15" icon="tabler-star" color="warning" v-for="i in Number(v.pn_rating)" :key="i" />
                          </td>
                          <td>
                            {{ v.pn_rating_comment }}
                          </td>
                          <td>
                            <IconBtn @click="">
                              <VIcon icon="tabler-printer" />
                              <VTooltip open-delay="200" location="top" activator="parent">
                                <span>Print data</span>
                              </VTooltip>
                            </IconBtn>
                          </td>
                        </tr>
                      </template>
                      <tr v-else>
                        <td colspan="5" class="text-center">
                          Data is empty
                        </td>
                      </tr>
                    </tbody>
                  </VTable>
                </VExpansionPanelText>
              </VExpansionPanel>
            </VExpansionPanels>
          </VCol>
        </VRow>
      </VCard>
  </VDialog>
  <PPSOngoingViewPathDialog
    v-model:isDialogViewPathVisible="isDialogViewPathVisible"
    :path-data="pathData"
  />
</template>
