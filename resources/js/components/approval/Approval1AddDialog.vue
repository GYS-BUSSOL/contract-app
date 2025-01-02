<script setup>
import { getListMerPaymentTemplate, getListMerPaymentType, getListMerVendor } from '@db/apps/mer/db';
import { Link } from '@tiptap/extension-link';
import { Placeholder } from '@tiptap/extension-placeholder';
import { Underline } from '@tiptap/extension-underline';
import { StarterKit } from '@tiptap/starter-kit';
import {
  EditorContent,
  useEditor,
} from '@tiptap/vue-3';
import dayjs from "dayjs";
import { reactive } from 'vue';
import 'vue-skeletor/dist/vue-skeletor.css';

const emit = defineEmits([
  'update:isDialogVisible',
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
  conReqId: {
    type: Number,
    required: true
  },
  conDurationStart: {
    type: String,
    required: true,
  },
  conDurationEnd: {
    type: String,
    required: true,
  },
  conBu: {
    type: String,
    required: true,
  },
  totalDays: {
    type: Number,
    required: true,
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
const conReqId = computed(() => props.conReqId)
const conBU = computed(() => props.conBu)
const conDurationStart = computed(() => props.conDurationStart)
const conDurationEnd = computed(() => props.conDurationEnd)
const totalDays = computed(() => props.totalDays)
const pathData = ref('')
const con_comment_coo_approve = ref(null)
const isDialogViewPathVisible = ref(false)
const token = useCookie('accessToken')
const loadingBtn = ref([])
const loadingBtnSecond = ref([])
const isDisabled = ref(false)
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
const costData = reactive({
  total_budget: null,
  total_expense: null,
  total_balance: null,
})
const estimatedCostData = reactive({
  days: null,
  con_duration_start: null,
  con_duration_end: null,
})
const estimatedCostDetailData = reactive({
  mandays: null,
  not_mandays: null,
  grand_total: null,
})
const dataMerVendor = ref([])
const dataMerPaymentType = ref([])
const dataMerPaymentTemplate = ref([])
const valuationData = ref([])
const historyData = ref([])
const allDataVendor = ref([])

// Editor Comment
const editorComment = useEditor({
  content: '',
  extensions: [
    StarterKit,
    Placeholder.configure({ placeholder: 'Type your comment...' }),
    Underline,
    Link.configure({ openOnClick: true }),
  ],
  onUpdate: ({ editor }) => {
    con_comment_coo_approve.value = editor.getHTML()
  },
})

const setLinkComment = () => {
  const previousUrl = editorComment.value?.getAttributes('link').href
  const url = window.prompt('URL', previousUrl)

  if (url === null)
    return

  if (url === '') {
    editorComment.value?.chain().focus().extendMarkRange('link').unsetLink().run()
    return
  }

  editorComment.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}

const dialogModelValueUpdate = () => {
  // Contract Data
  contractData.vendor_name = null;
  contractData.bu = null;
  contractData.cc = [];
  contractData.wc = [];
  contractData.con_work_location = null;
  contractData.con_id_project = null;
  contractData.con_pps_no = null;
  contractData.con_old_pps_no = null;
  contractData.con_priority_id = null;
  contractData.shift = [];
  contractData.aud_user = null;
  contractData.con_cp_name = null;
  contractData.con_cp_dept = null;
  contractData.con_cp_exthp = null;
  contractData.con_cp_email = null;
  contractData.con_comment_bu = null;
  contractData.con_duration_start = null;
  contractData.con_duration_end = null;
  contractData.con_file_attachment = null;
  contractData.vnd_contact_person = null;
  contractData.suggest_vendor = null;
  // Cost Data
  costData.total_budget = null;
  costData.total_expense = null;
  costData.total_balance = null;
  // Estimated Data
  estimatedCostData.days = null;
  estimatedCostData.con_duration_start = null;
  estimatedCostData.con_duration_end = null;
  // Comment Approval
  con_comment_coo_approve.value = null;
  loadingBtn.value[0] = false;
  loadingBtnSecond.value[0] = false;
  emit('update:isDialogVisible', false)
}

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
}

const resolveStatusVariant = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === '7' || statLowerCase === '5' || statLowerCase === '15')
    return 'error'
  else if(statLowerCase === '16')
    return 'warning'
  return 'success'
}

const openPathDialog = (path) => {
  pathData.value = path;
  isDialogViewPathVisible.value = true;
}

const IDRFormat = (data) => {
  return 'Rp. ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(data).replace('Rp', '').trim()
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
    const response = await $api(`/apps/contract/edit/${conReqId.value}`, {
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
        isLoading.value = false;
        throw new Error("Get data failed");
      },
    });
    
    const dataResponse = JSON.parse(JSON.stringify(response));
    if (dataResponse.status == 200) {
      isLoading.value = false;
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
      isLoading.value = false;
      throw new Error("Get data failed");
    }
  } catch (error) {
    emit('update:isDialogVisible', false)
    emit('isSnackbarResponse',true)
    emit('isSnackbarResponseAlertColor', 'error')
    isLoading.value = false;
    throw new Error("Get data failed");
  }
}

const fetchValuationList = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/valuation/list/${conReqId.value}`, {
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

const fetchCostDetail = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/estimated-cost/list/${conReqId.value}/${totalDays.value}`, {
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
      const rows = dataResponse.data || {};

      estimatedCostDetailData.mandays = rows.mandays;
      estimatedCostDetailData.not_mandays = rows.not_mandays;
      estimatedCostDetailData.grand_total = rows.grand_total;
      
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
    throw new Error("Get data failed", error);
  }
}

const fetchHistoryList = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/history-cost/list/${conReqId.value}`, {
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
      console.log({historyData: historyData.value.length, data : JSON.stringify(historyData.value)});
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

const fetchCostData = async () => {
  try {
    const payload = {
      'con_req_id': conReqId.value,
      'con_bu': conBU.value,
      'con_duration_start': conDurationStart.value,
      'con_duration_end': conDurationEnd.value,
      'days': totalDays.value
    }
    const response = await $api(`/apps/estimated-cost`, {
      method: 'POST',
      body: payload,
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
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
    const data = responseParse.data || {};
    
    costData.total_budget = data.totBudget;
    costData.total_expense = data.totalExpense;
    costData.total_balance = data.balanceBudget;
    estimatedCostData.con_duration_start = data.durationStart;
    estimatedCostData.con_duration_end = data.durationEnd;
    estimatedCostData.days = data.durationDay;
  } else {
    emit('isSnackbarResponse',true)
    emit('isSnackbarResponseAlertColor', 'error')
    throw new Error("Created data failed");
  }
  } catch (error) {
    emit('isSnackbarResponse',true)
    emit('isSnackbarResponseAlertColor', 'error')
  }
}

const onSubmit = (type) => {
  let payload;
  try {
    isDisabled.value = true
    if(type == 'Approve') {
      loadingBtn.value[0] = true
      payload = {
        con_comment_coo_approve: con_comment_coo_approve.value,
        con_req_id: conReqId.value,
        grand_total: parseInt(estimatedCostDetailData.grand_total || 0),
        avg_expense: costData.total_expense,
        avg_balance: costData.total_balance
      }
    } else if(type == 'Reject') {
      loadingBtnSecond.value[0] = true
      payload = {
        con_comment_coo_approve: con_comment_coo_approve.value,
        con_req_id: conReqId.value,
      }
    }
    emit("ApprovalData", { type, payload, dialogUpdate: dialogModelValueUpdate });
  } catch (err) {
    isDisabled.value = false
    loadingBtn.value[0] = false
    loadingBtnSecond.value[0] = false
  }
}

watch(
  [() => conReqId.value, () => conBU.value, () => conDurationStart.value, () => typeDialog.value, () => props.fetchTrigger],
    ([newConreqId,newConReqBU,newConDurationStart,newType]) => {
      if (newType === "Add" && newConreqId && newConReqBU && newConDurationStart) {
        fetchContractEdit()
        fetchValuationList()
        fetchHistoryList()
        fetchCostData()
        fetchCostDetail()
      }
      isDisabled.value = false
      loadingBtn.value[0] = false;
      loadingBtnSecond.value[0] = false;
      fetchMerVendorData()
      fetchMerPaymentType()
      fetchMerPaymentTemplate()
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
        <VRow>
          <!-- Estimated Cost -->
          <VCol cols="12" md="6">
            <div class="card__image mt-5" v-if="isLoading">
              <Skeletor class="mt-5 mb-3" height="30" width="100"/>
              <Skeletor height="120" />
            </div>
            <h3 class="mt-5" v-if="!isLoading">Estimated Cost</h3>
            <VTable class="border collapse mt-3 mb-5" v-if="!isLoading" height="max-content" fixed-header>
              <tbody>
                <tr>
                  <th>Available Budget</th>
                  <td>:</td>
                  <td>{{ IDRFormat(Number(costData.total_budget || 0)) }}</td>
                </tr>
                <tr>
                  <th>Estimated Cost</th>
                  <td>:</td>
                  <td>{{ IDRFormat(Number(costData.total_expense || 0)) }}</td>
                </tr>
                <tr>
                  <th>Balance Budget</th>
                  <td>:</td>
                  <td>{{ IDRFormat(Number(costData.total_balance || 0)) }}</td>
                </tr>
              </tbody>
            </VTable>
          </VCol>
          <!-- Estimated Time -->
          <VCol cols="12" md="6">
            <div class="card__image mt-5" v-if="isLoading">
              <Skeletor class="mt-5 mb-3" height="30" width="90"/>
              <Skeletor height="100" />
            </div>
            <h3 class="mt-5" v-if="!isLoading">Estimated Time</h3>
            <VTable class="border collapse mt-3 mb-5" v-if="!isLoading" height="max-content" fixed-header>
              <tbody>
                <tr>
                  <th>Planning Time Duration</th>
                  <td>:</td>
                  <td>{{ formatDate(estimatedCostData.con_duration_start) + ' until ' + formatDate(estimatedCostData.con_duration_end) }}</td>
                </tr>
                <tr>
                  <th>Estimated Time</th>
                  <td>:</td>
                  <td>{{ estimatedCostData.days || 0 }} Work days</td>
                </tr>
              </tbody>
            </VTable>
          </VCol>
          <!-- Mandays -->
          <VCol cols="12">
            <h3 class="mt-5" v-if="!isLoading">Cost Detail</h3>
            <VTable class="border collapse mt-3 mb-5" v-if="!isLoading && estimatedCostDetailData.mandays" :height="estimatedCostDetailData.mandays.length <= 5 ? 'max-content' : '300'" fixed-header>
              <thead>
                <tr>
                  <th scope="col">
                    Rate
                  </th>
                  <th scope="col">
                    Workdays
                  </th>
                  <th scope="col">
                    Man Power Quantity
                  </th>
                  <th scope="col">
                    Total Rate
                  </th>
                  <th scope="col">
                    Sub Total Rate
                  </th>
                </tr>
              </thead>
              <tbody>
                <template v-if="estimatedCostDetailData.mandays && estimatedCostDetailData.mandays.length > 0">
                  <template v-for="(data, index) in estimatedCostDetailData.mandays">
                    <tr>
                      <td colspan="5">
                        {{ data.cjb_desc + ', ' + data.jum + ' Shift ( Job Target Qty: ' + parseInt(data.cjb_qty) + ' hari' + ' - PIC: ' + data.cjb_pic }} )
                      </td>
                    </tr>
                    <template v-if="data.job_labor.length > 0">
                      <tr v-for="(jl, indexJl) in data.job_labor">
                        <td>{{ IDRFormat(parseInt(jl.cjl_rate) * 1.08) }}</td>
                        <td>{{ estimatedCostData.days }}</td>
                        <td>{{ jl.cjl_type }}</td>
                        <td>{{ parseInt(jl.cjl_qty) }}</td>
                        <td>{{ IDRFormat(parseInt(jl.cjl_rate) * 1.08 * parseInt(jl.cjl_qty) * estimatedCostData.days) }}</td>
                      </tr>
                    </template>
                    <tr v-else class="text-center">
                      <td colspan="5">Data is empty</td>
                    </tr>
                  </template>
                </template>
                <tr v-else class="text-center">
                  <td colspan="5"> Data is empty</td>
                </tr>
              </tbody>
            </VTable>
          </VCol>
          <!-- Not Mandays & Grand total -->
          <VCol cols="12">
            <VTable class="border collapse mt-3 mb-5" v-if="!isLoading && estimatedCostDetailData.not_mandays" :height="estimatedCostDetailData.not_mandays.length <= 5 ? 'max-content' : '300'" fixed-header>
              <thead>
                <tr>
                  <th scope="col">
                    Rate
                  </th>
                  <th scope="col">
                    Job Target Qty	
                  </th>
                  <th scope="col">
                    UoM
                  </th>
                  <th scope="col">
                    Payment Type
                  </th>
                  <th scope="col">
                    Total Rate
                  </th>
                </tr>
              </thead>
              <tbody>
                <template v-if="estimatedCostDetailData.not_mandays && estimatedCostDetailData.not_mandays.length > 0">
                  <template v-for="(data, index) in estimatedCostDetailData.not_mandays">
                    <tr>
                      <td colspan="5">
                        {{ data.cjb_desc + ', ' + data.jum + ' Shift ( ' + data.job_labor.map(jl => jl.cjl_type + ' - ' + parseInt(jl.cjl_qty)).join(', ') }} )
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <span v-if="data.cjb_pay_template == 'p1'">
                          {{ IDRFormat(parseInt(data.cjb_rate) * 1.08) }}
                        </span>
                        <span v-if="data.cjb_pay_template == 'rit'">
                          {{ IDRFormat(parseInt(data.cjb_rate)) }}
                        </span>
                      </td>
                      <td>
                        {{ parseInt(data.cjb_qty) }}
                      </td>
                      <td>
                        {{ data.unt_id }}
                      </td>
                      <td>
                        {{ data.cjb_pay_type }}
                      </td>
                      <td>
                        {{ IDRFormat(data.total) }}
                      </td>
                    </tr>
                  </template>
                </template>
                <tr v-else>
                  <td colspan="5" class="text-center"> Data is empty</td>
                </tr>
              </tbody>
            </VTable>
            <VTable>
              <tbody>
                <tr>
                  <td class="text-end"><strong>Grand Total : </strong> {{ IDRFormat(parseInt(estimatedCostDetailData.grand_total || 0)) }}</td>
                </tr>
              </tbody>
            </VTable>
          </VCol>
        </VRow>
        <!-- History & Comment-->
        <VRow class="mt-5">
          <VCol cols="12" md="6">
            <h3 class="mt-5" v-if="!isLoading">History</h3>
            <VTable class="border collapse mt-3" v-if="!isLoading" :height="historyData.length <= 5 ? 'max-content' : '300'" fixed-header>
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
                      {{ dayjs(h.aud_date).format('YYYY-MM-DD') }}
                    </td>
                    <td>
                      {{ h.ths_comment }}
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
          </VCol>
          <VCol cols="12" md="6">
            <h3 class="mt-5" v-if="!isLoading">Comment</h3>
            <div class="border rounded px-3 py-1 mt-3">
              <EditorContent :editor="editorComment"/>
              <div
                v-if="editorComment"
                class="d-flex justify-end flex-wrap gap-x-2"
              >
                <VIcon
                  icon="tabler-bold"
                  :color="editorComment.isActive('bold') ? 'primary' : ''"
                  size="20"
                  @click="editorComment.chain().focus().toggleBold().run()"
                />
                <VIcon
                  :color="editorComment.isActive('underline') ? 'primary' : ''"
                  icon="tabler-underline"
                  size="20"
                  @click="editorComment.commands.toggleUnderline()"
                />
                <VIcon
                  :color="editorComment.isActive('italic') ? 'primary' : ''"
                  icon="tabler-italic"
                  size="20"
                  @click="editorComment.chain().focus().toggleItalic().run()"
                />
                <VIcon
                  :color="editorComment.isActive('bulletList') ? 'primary' : ''"
                  icon="tabler-list"
                  size="20"
                  @click="editorComment.chain().focus().toggleBulletList().run()"
                />
                <VIcon
                  :color="editorComment.isActive('orderedList') ? 'primary' : ''"
                  icon="tabler-list-numbers"
                  size="20"
                  @click="editorComment.chain().focus().toggleOrderedList().run()"
                />
                <VIcon
                  icon="tabler-link"
                  size="20"
                  @click="setLinkComment"
                />
              </div>
            </div>
          </VCol>
        </VRow>
        <VRow class="d-flex justify-end mt-5">
          <div class="card__actions d-flex justify-end" v-if="isLoading">
            <Skeletor width="96" height="36" class="me-3"/>
            <Skeletor width="96" height="36" />
          </div>
          <div v-if="!isLoading">
            <!-- Action Button -->
            <VCol cols="12">
              <div class="d-flex flex-wrap gap-4 justify-end mt-8">
                <VBtn
                  color="success"
                  type="submit"
                  :loading="loadingBtn[0]"
                  :disabled="isDisabled"
                  @click="onSubmit('Approve')"
                >
                  <VIcon
                    icon="tabler-checklist"
                    start
                    class="flip-in-rtl"
                  />
                  Approve
                </VBtn>
                
                <VBtn
                  color="error"
                  type="submit"
                  :loading="loadingBtnSecond[0]"
                  :disabled="isDisabled"
                  @click="onSubmit('Reject')"
                >
                  <VIcon
                    icon="tabler-ban"
                    start
                    type="submit"
                    class="flip-in-rtl"
                  />
                  Reject
                </VBtn>
              </div>
            </VCol>
          </div>
        </VRow>
      </VCard>
  </VDialog>

  <ApprovalViewPathDialog
    v-model:isDialogViewPathVisible="isDialogViewPathVisible"
    :path-data="pathData"
  />
  
</template>
