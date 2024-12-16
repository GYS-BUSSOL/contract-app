<script setup>
import { getListMerPaymentType, getListMerVendor } from '@db/apps/mer/db';
import dayjs from "dayjs";
import 'vue-skeletor/dist/vue-skeletor.css';

const emit = defineEmits([
  'update:isDialogVisible',
  'isAddDialogVisible',
  'vendorData',
  'isSnackbarResponse',
  'isSnackbarResponseAlertColor',
  'errorMessages',
  'errors',
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
const refVForm = ref()
const typeDialog = computed(() => props.typeDialog)
const contractReqId = computed(() => props.contractReqId)
const loadingBtn = ref([])
const loadingBtnSecond = ref([])
const pathData = ref('')
const isDialogViewPathVisible = ref(false)
const isDialogSingleVisible = ref(false)
const cjbId = ref(0)
const fetchSingleTrigger = ref(0)
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
  suggest_vendor: null,
  vnd_name: null
})

const contractJobData = reactive({
  contract_job: [],
  job_type: [],
  job_labor: [],
  job_target: [],
  con_comment_pbl_reject: null
})
const dataMerPaymentType = ref([])
const allDataVendor = ref([])
const dataMerVendor = ref([])

const dialogModelValueUpdate = () => {
  contractJobData.contract_job = [];
  contractJobData.job_type = [];
  contractJobData.job_labor = [];
  contractJobData.job_target = [];
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

const fetchContractEdit = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/contract/edit/${contractReqId.value}`, {
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

const openAddJobtypeDialog = () => {
  emit('isAddDialogVisible', {type: 'Add Job Type', stat: true})
}

const openSingleJobtypeDialog = (cjb_id) => {
  emit('isAddDialogVisible', {type: 'Edit Job Type', stat: true, cjb_id})
}

const openDeleteJobtypeDialog = (cjb_id) => {
  emit('isDialogDeleteVisible', {type: 'Delete', stat: true, cjb_id})
}

watch(
  [() => contractReqId.value, () => typeDialog.value, () => props.fetchTrigger],
    ([newConreqId,newType]) => {
      if (newConreqId && newType === "List Job Type" || newType === "Edit Job Type" || newType === "Delete") {
        fetchContractEdit()
        fetchContractJobEdit()
        fetchMerPaymentType()
        fetchMerVendorData()
      }
      loadingBtn.value[0] = false;
      loadingBtnSecond.value[0] = false;
  },
  { immediate: true }
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
                          <td @click="openPathDialog(contractData.con_file_attachment)" class="text-primary" style="cursor: pointer;">
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
            <Skeletor height="250" />
          </div>
          <!-- Detail -->
          <VTable class="border text-high-emphasis mt-6" fixed-header :style="contractJobData.contract_job.length <= 2 ? 'height:max-content;' : 'height:80vh;'" v-if="!isLoading">
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
                  PIC
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
                  Total Job Target Qty
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
                  Actions
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
                  {{ data.cjb_pic || '' }}
                </td>
                <td>
                  {{ dataMerPaymentType.find((mpt) => mpt.value == data.cjb_pay_type)?.title || '-' }}
                </td>
                <td>
                  {{ data.cjb_qty || 0 }}
                </td>
                <td>
                  {{ data.unt_id || '-' }}
                </td>
                <td>
                  <div class="d-flex">
                    <IconBtn @click="openSingleJobtypeDialog(data.cjb_id)">
                      <VIcon icon="tabler-edit" />
                      <VTooltip open-delay="200" location="top" activator="parent">
                        <span>Edit Job Type</span>
                      </VTooltip>
                    </IconBtn>
                    <IconBtn @click="openDeleteJobtypeDialog(data.cjb_id)">
                      <VIcon icon="tabler-trash" />
                      <VTooltip open-delay="200" location="top" activator="parent">
                        <span>Delete data</span>
                      </VTooltip>
                    </IconBtn>
                  </div>
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
                        </tr>
                      </template>
                      <tr v-else>
                        <td colspan="4">
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
          <h3 v-if="contractJobData.contract_job.length == 0 && !isLoading" class="mt-5 d-flex justify-center">Data is empty</h3>
          <VRow class="d-flex justify-center mt-5">
            <div class="card__actions d-flex justify-center" v-if="isLoading">
              <Skeletor width="150" height="36" />
            </div>
            <div v-if="!isLoading">
              <VBtn
                class="me-4"
                variant="outlined"
                @click="openAddJobtypeDialog"
              >
                <VIcon
                  icon="tabler-plus"
                  start
                  class="flip-in-rtl"
                />
                Add New Job Type
              </VBtn>
            </div>
          </VRow>
        </VForm>
      </VCard>
  </VDialog>
  <PPSOngoingViewPathDialog
    v-model:isDialogViewPathVisible="isDialogViewPathVisible"
    :path-data="pathData"
  />

  <PPSOngoingSingleDialog
    v-model:isDialogSingleVisible="isDialogSingleVisible"
    :cjb-id="cjbId"
    :fetch-single-trigger="fetchSingleTrigger"
  />
</template>
