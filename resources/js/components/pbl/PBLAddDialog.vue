<script setup>
import { getListContract, getListMerMeasurementUnit, getListMerPaymentTemplate, getListMerPaymentType, getListMerVendor, getListSignatureType } from '@db/apps/mer/db';
import { Link } from '@tiptap/extension-link';
import { Placeholder } from '@tiptap/extension-placeholder';
import { Underline } from '@tiptap/extension-underline';
import { StarterKit } from '@tiptap/starter-kit';
import {
  EditorContent,
  useEditor,
} from '@tiptap/vue-3';
import dayjs from "dayjs";
import 'vue-skeletor/dist/vue-skeletor.css';

const userAuth = useCookie('userData')
const emit = defineEmits([
  'update:isDialogVisible',
  'createSPK',
  'saveSPK',
  'isSnackbarResponse',
  'isSnackbarResponseAlertColor',
  'errorMessages',
  'errors',
  'isSuccessNextStep',
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
  SPKId: {
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
  },
  isSuccessNextStep: {
    type: Boolean,
    required: true,
    default: false,
  },
})

// Props
const typeDialog = computed(() => props.typeDialog)
const SPKId = computed(() => props.SPKId)
// Condition variable
const isLoadingContract = ref(true)
const isLoadingContractJob = ref(true)
const isLoadingSPK = ref(true)
const loadingBtn = ref([])
const loadingBtnSecond = ref([])
const loadingBtnThird = ref([])
const isDisabled = ref(false)
// Form
const refSPKForm = ref()
const refSPKPrintForm = ref()
const currentStep = ref(0)
const isCurrentStepValid = ref(true)
// Data
const dataMerVendor = ref([])
const dataContractList = ref([])
const dataSignatureTypeList = ref([])
const allDataVendor = ref([])
const allDataContract = ref([])
const allUserSignature = ref([])
const shiftData = ref()
const countShiftData = ref()
const dataMerPaymentType = ref([])
const dataMerPaymentTemplate = ref([])
const dataMerMeasurementUnit = ref([])
// Cookie
const token = useCookie('accessToken')
// Initialized
const numberedSteps = ref([
  {
    title: 'Create',
    subtitle: 'Add SPK',
  },
  {
    title: 'SPK Print',
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
})

const vendorData = reactive({
  vnd_name: null,
  vnd_contact_person: null,
  vnd_address: null,
})

const signType = reactive({
  st_user: null,
  st_id: null,
})

const userData = reactive({
  usr_display_name: null,
  usr_jabatan: null,
})

const companyData = reactive({
  company_alamat: null,
  company_name: null,
})

const SPKData = reactive({
  spk_jobdesc_summary: null,
  signature_type: null,
  spk_date: null,
  spk_web_id: null,
  spk_no: null,
  ven_id: null,
  duration: null,
  spk_renewal_box: null,
  spk_start_date: null,
  spk_end_date: null,
  spk_box_bpjs: null,
  spk_cara_bayar: null,
  spk_tahap_bayar: null,
  spk_lain_lain: null,
})

// Reset All
const dialogModelValueUpdate = () => {
  // Vendor
  vendorData.vnd_name = null;
  vendorData.vnd_contact_person = null;
  vendorData.vnd_address = null;
  // Company
  companyData.company_name = null;
  companyData.company_alamat = null;
  // SPK
  SPKData.spk_jobdesc_summary = null;
  SPKData.signature_type = null;
  SPKData.spk_date = null;
  SPKData.spk_web_id = null;
  SPKData.spk_no = null;
  SPKData.ven_id = null;
  SPKData.duration = null;
  SPKData.spk_renewal_box = null;
  SPKData.spk_start_date = null;
  SPKData.spk_end_date = null;
  SPKData.spk_box_bpjs = null;
  SPKData.spk_cara_bayar = null;
  SPKData.spk_tahap_bayar = null;
  SPKData.spk_lain_lain = null;
  // Contract Job
  contractJobData.contract_job = [];
  contractJobData.job_type = [];
  contractJobData.job_labor = [];
  // User Signature
  allUserSignature.value = [];
  // Shift
  shiftData.value = null;
  countShiftData.value = null;
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
  // Sign Type
  signType.st_user = null;
  signType.st_id = null;
  // User Data
  userData.usr_display_name = null;
  userData.usr_jabatan = null;
  // Form reset
  refSPKPrintForm.value?.reset()
  refSPKPrintForm.value?.resetValidation()
  refSPKForm.value?.reset()
  refSPKForm.value?.resetValidation()
  loadingBtn.value[0] = false
  loadingBtnSecond.value[0] = false
  loadingBtnThird.value[0] = false
  isDisabled.value = false
  // Back step
  currentStep.value--
  isCurrentStepValid.value = true
  // Falsing button
  isDisabled.value = false
  loadingBtn.value[0] = false
  loadingBtnSecond.value[0] = false
  loadingBtnThird.value[0] = false
  // Emit
  emit('updateTypeDialog');
  emit('update:isDialogVisible', false)
  emit('isSuccessNextStep',false)
}

// Editor Payment Method
const editorPaymentMethod = useEditor({
  extensions: [
    StarterKit,
    Placeholder.configure({ placeholder: 'Type here...' }),
    Underline,
    Link.configure({ openOnClick: true }),
  ],
  onUpdate: ({ editor }) => {
    SPKData.spk_cara_bayar = editor.getHTML()
  },
})

const setLinkPaymentMethod = () => {
  const previousUrl = editorPaymentMethod.value?.getAttributes('link').href
  const url = window.prompt('URL', previousUrl)

  if (url === null)
    return

  if (url === '') {
    editorPaymentMethod.value?.chain().focus().extendMarkRange('link').unsetLink().run()
    return
  }

  editorPaymentMethod.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}

// Editor Payment Stages
const editorPaymentStages = useEditor({
  extensions: [
    StarterKit,
    Placeholder.configure({ placeholder: 'Type here...' }),
    Underline,
    Link.configure({ openOnClick: true }),
  ],
  onUpdate: ({ editor }) => {
    SPKData.spk_tahap_bayar = editor.getHTML()
  },
})

const setLinkPaymentStages = () => {
  const previousUrl = editorPaymentStages.value?.getAttributes('link').href
  const url = window.prompt('URL', previousUrl)

  if (url === null)
    return

  if (url === '') {
    editorPaymentStages.value?.chain().focus().extendMarkRange('link').unsetLink().run()
    return
  }

  editorPaymentStages.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}

// Editor Other
const editorOther = useEditor({
  extensions: [
    StarterKit,
    Placeholder.configure({ placeholder: 'Type here...' }),
    Underline,
    Link.configure({ openOnClick: true }),
  ],
  onUpdate: ({ editor }) => {
    SPKData.spk_lain_lain = editor.getHTML()
  },
})

const setLinkOther = () => {
  const previousUrl = editorOther.value?.getAttributes('link').href
  const url = window.prompt('URL', previousUrl)

  if (url === null)
    return

  if (url === '') {
    editorOther.value?.chain().focus().extendMarkRange('link').unsetLink().run()
    return
  }

  editorOther.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}

const formatDate = (date, time = false) => {
  return dayjs(date).format(`DD MMM YYYY${time ? ", HH:mm" : ""}`);
}

const onFalsingButton = () => {
  // Falsing button
  isDisabled.value = false
  loadingBtn.value[0] = false
  loadingBtnSecond.value[0] = false
  loadingBtnThird.value[0] = false
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
      throw new Error("Failed to fetch mer vendor data");
    }
    
  } catch (error) {
    throw new Error("Failed to fetch mer vendor data");
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
      throw new Error("Failed to fetch contract data");
    }
    
  } catch (error) {
    throw new Error("Failed to fetch contract data");
  }
}

const fetchSignatureTypeList = async () => {
  try {
    const response = await getListSignatureType();
    if (response.status === 200) {

      const rows = response.data.rows || [];
      dataSignatureTypeList.value = rows;
    } else {
      throw new Error("Failed to fetch signature type data");
    }
  } catch (error) {
    throw new Error("Failed to fetch signature type data");
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

const fetchMerMeasurementUnitData = async () => {
  try {
    const response = await getListMerMeasurementUnit();
    if (response.status === 200) {
      const rows = response.data.rows || [];
      dataMerMeasurementUnit.value = rows.map((row) => ({
        title: row.unt_name,
        value: row.unt_code,
      }));
    } else {
      throw new Error("Error to fetch measurement unit data");
    }
    
  } catch (error) {
    throw new Error("Error to fetch measurement unit data");
  }
}

const fetchContractEdit = async (conReqId) => {
  try {
    isLoadingContract.value = true;
    const response = await $api(`/apps/contract/edit/${conReqId}`, {
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
        throw new Error("Get data failed");
      },
    });
    
    const dataResponse = JSON.parse(JSON.stringify(response));
    if (dataResponse.status == 200) {
      isLoadingContract.value = false;
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

const fetchSPKEdit = async () => {
  try {
    isLoadingSPK.value = true;
    const response = await $api(`/apps/spk/edit/${SPKId.value}`, {
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
        throw new Error("Get data failed");
      },
    });
    
    const dataResponse = JSON.parse(JSON.stringify(response));
    if (dataResponse.status == 200) {
      isLoadingSPK.value = false;
      const dataResult = dataResponse.data;
      // SPK
      SPKData.spk_date = dataResult.spk_date;
      SPKData.spk_web_id = dataResult.spk_web_id;
      SPKData.spk_no = dataResult.spk_no;
      SPKData.ven_id = dataResult.ven_id;
      SPKData.spk_renewal_box = dataResult.spk_renewal_box;
      SPKData.spk_jobdesc_summary = dataResult.spk_jobdesc_summary;
      SPKData.spk_start_date = dataResult.spk_start_date;
      SPKData.spk_end_date = dataResult.spk_end_date;
      SPKData.spk_box_bpjs = dataResult.spk_box_bpjs;
      SPKData.spk_cara_bayar = dataResult.spk_cara_bayar;
      editorPaymentMethod.value?.commands.setContent(dataResult.spk_cara_bayar || null)
      SPKData.spk_tahap_bayar = dataResult.spk_tahap_bayar;
      editorPaymentStages.value?.commands.setContent(dataResult.spk_tahap_bayar || null)
      SPKData.spk_lain_lain = dataResult.spk_lain_lain;
      editorOther.value?.commands.setContent(dataResult.spk_lain_lain || null)
      // User Signature
      allUserSignature.value = dataResult.userSignature;
      // Shift
      shiftData.value = Number(dataResult.countShift.jml_shift) && Number(dataResult.countShift.jml_shift) > 0 ? dataResult.shift : '';
      countShiftData.value = Number(dataResult.countShift.jml_shift);
      // Vendor
      vendorData.vnd_name = dataResult.vnd_name;
      vendorData.vnd_contact_person = dataResult.vnd_contact_person;
      vendorData.vnd_address = dataResult.vnd_address;
      // Sign type
      signType.st_id = dataResult.st_id;
      signType.st_user = dataResult.st_user;
      // User
      userData.usr_display_name = dataResult.usr_display_name;
      userData.usr_jabatan = dataResult.usr_jabatan;
      // Company
      companyData.company_name = dataResult.company_name;
      companyData.company_alamat = dataResult.company_alamat;
      // Contract
      allDataContract.value = dataResult.contract;
    } else {
      emit('isSnackbarResponse',true)
      emit('isSnackbarResponseAlertColor', 'error')
      throw new Error("Get data failed");
    }
  } catch (error) {
    emit('isSnackbarResponse',true)
    emit('isSnackbarResponseAlertColor', 'error')
    throw new Error("Get data failed");
  }
}

const fetchSPKLatestNumber = async () => {
  try {
    const response = await $api(`/apps/spk/latest-number`, {
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
      // SPK
      SPKData.spk_no = dataResult;
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
    isLoadingContractJob.value = true;
    const response = await $api(`/apps/contract-job/list/${conReqId}`, {
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
        throw new Error("Get data failed");
      },
    });
    
    const dataResponse = JSON.parse(JSON.stringify(response));
    if (dataResponse.status == 200) {
      const dataResult = dataResponse.data;
      isLoadingContractJob.value = false;
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

const validateCreateSPK = async () => {
  refSPKForm.value?.validate().then(valid => {
    if (valid.valid) {
      onSubmitCreateSPK()
    } else {
      isCurrentStepValid.value = false
    }
  })
}

const validatePrintSPK = (type) => {
  refSPKPrintForm.value?.validate().then(valid => {
    if (valid.valid) {
      isCurrentStepValid.value = true
      printSPK(type)
    } else {
      isCurrentStepValid.value = false
    }
  })
}

const onSubmitCreateSPK = async () => {
  try {
    loadingBtn.value[0] = true
    const mode = typeDialog.value;
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

const printSPK = async (type) => {
  try {
    const mode = type;

    isDisabled.value = true
    const payload = {
      contract: allDataContract.value,
      spk_id: SPKId.value,
      spk_date: SPKData.spk_date,
      spk_no: SPKData.spk_no,
      spk_box_bpjs: SPKData.spk_box_bpjs,
      spk_lain_lain: SPKData.spk_lain_lain,
      spk_cara_bayar: SPKData.spk_cara_bayar,
      spk_tahap_bayar: SPKData.spk_tahap_bayar,
      spk_renewal_box: SPKData.spk_renewal_box,
      shift: shiftData.value
    }
    if(type == 'Save') {
      loadingBtnSecond.value[0] = true
    } else if(type == 'Print') {
      loadingBtnThird.value[0] = true
    }

    emit("printSPK", { mode, formData: { ...payload }, dialogUpdate: dialogModelValueUpdate });

    if(type == 'Print') {
      localStorage.setItem('SPKData', JSON.stringify(SPKData))
      localStorage.setItem('shiftData', shiftData.value)
      localStorage.setItem('range', JSON.stringify(allDataContract.value.map((c) => c.contractJob.map((cj) => cj.range))))
      window.open(
        `${window.location.origin}/apps/pbl/preview/${SPKId.value}`,
        '_blank'
      )
    }
  } catch (err) {
    loadingBtnSecond.value[0] = false
    loadingBtnThird.value[0] = false
    isDisabled.value = false
  }
}

watch(
  [() => typeDialog.value, () => props.fetchTrigger, () => props.isSuccessNextStep],
    ([newType]) => {
    if (newType === "Add") {
      fetchContractList()
      fetchMerVendorData()
      fetchSignatureTypeList()

      if(props.isSuccessNextStep) {
        currentStep.value++
        isCurrentStepValid.value = true
      }
    }
    onFalsingButton()
  }
)

watch(
  [() => SPKId.value],
    ([newSPKId]) => {
    if(newSPKId) {
      fetchSPKEdit()
      fetchSPKLatestNumber()
      fetchMerMeasurementUnitData()
    }
    onFalsingButton()
  }
)

watch([() => contractData.con_req_id], (conReqId) => {
    if(conReqId != '') {
      fetchMerPaymentType()
      fetchMerPaymentTemplate()
      fetchContractEdit(conReqId)
      fetchContractJobEdit(conReqId)
    }
    onFalsingButton()
  }
)
</script>

<template>
  <VDialog
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
                <VCol cols="12" v-if="contractData.con_req_id && contractData.con_req_id != '' && isLoadingContract" class="mt-5">
                  <Skeletor height="250" />
                </VCol>
                <!-- Contract Table -->
                <VCol cols="12" v-if="contractData.con_req_id && contractData.con_req_id != '' && !isLoadingContract">
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
                <VCol cols="12" v-if="contractData.con_req_id && contractData.con_req_id != '' && isLoadingContractJob" class="mt-5">
                  <Skeletor height="250" />
                </VCol>
                <!-- Contract Job -->
                <VCol cols="12" v-if="contractData.con_req_id && contractData.con_req_id != '' && !isLoadingContractJob">
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
                        <td style="width: 300px !important; height: 50px;">
                          <AppTextarea
                            v-model="data.cjb_desc"
                            placeholder="Type here..."
                            :rules="[requiredValidator]"
                            :error-messages="props.errors?.cjb_desc"
                            no-resize
                            rows="4"
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
            <VForm ref="refSPKPrintForm">
              <VRow>
                <VCol cols="12" v-if="isLoadingSPK" class="mt-5">
                  <Skeletor height="100vh" />
                </VCol>
                <VCol cols="12" v-if="!isLoadingSPK">
                  <VCard class="invoice-preview-wrapper pa-6 pa-sm-12">
                    <img
                      src="../../../../public/GYS-Logo.png"
                      width="150"
                      alt="bg-img"
                      class="custom-checkbox-image"
                    >

                    <VDivider class="my-6" />
                    <!-- Letter date -->
                    <div class="d-flex justify-end">
                      <p class="text-end">Cikarang Barat,</p>
                      <AppDateTimePicker
                        v-model="SPKData.spk_date"
                        placeholder="SPK date"
                        :rules="[requiredValidator]"
                        :error-messages="props.errors?.spk_date"
                        style="width:100px"
                        class="ms-3"
                        density="compact"
                      />
                    </div>
                    <!-- Letter header -->
                    <div class="d-flex align-center flex-column">
                      <h3><u>SURAT PERINTAH KERJA</u></h3>
                      <div class="mt-2 d-flex">
                        <p class="me-3">No: </p>
                        <div class="d-flex flex-column align-center">
                          <AppTextField
                            style="width:200px"
                            placeholder="SPK number"
                            v-model="SPKData.spk_no"
                            :rules="[requiredValidator]"
                            density="compact"
                            :error-messages="props.errors?.spk_no"
                          />
                          <AppTextField
                            class="mt-3"
                            style="width:200px"
                            v-model="SPKData.spk_renewal_box"
                            :rules="[requiredValidator]"
                            :error-messages="props.errors?.spk_renewal_box"
                            density="compact"
                          />
                        </div>
                      </div>
                    </div>
                    <div class="mt-5">
                      <!-- Section 1 -->
                      <section>
                        <p class="mt-5 w-75">
                          Dengan ini kami / <i>Hereby we</i> :
                        </p>
                        <table class="w-100">
                          <tbody>
                            <tr>
                              <td class="pe-16">
                                <h6 class="text-base font-weight-medium">
                                  Nama / <i>Name</i>
                                </h6>
                              </td>
                              <td>:</td>
                              <td class="text-start ms-2">
                                <span class="ms-2">{{ userData.usr_display_name || '-' }}</span>
                              </td>
                            </tr>
                            <tr>
                              <td class="pe-16">
                                <h6 class="text-base font-weight-medium">
                                  Jabatan / <i>Position</i>
                                </h6>
                              </td>
                              <td>:</td>
                              <td class="text-start ms-2">
                                <span class="ms-2">{{ userData.usr_jabatan || '-' }}</span>
                              </td>
                            </tr>
                            <tr>
                              <td class="pe-16">
                                <h6 class="text-base font-weight-medium">
                                  Alamat / <i>Address</i>
                                </h6>
                              </td>
                              <td>:</td>
                              <td class="text-start ms-2">
                                <span class="ms-2">{{ companyData.company_alamat || '-' }}</span>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                        <p class="mt-5 w-75">
                          Selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong> <i>/ Hereinafter referred to as the <strong>FIRST PARTY</strong></i> Memberikan Perintah Kerja kepada <i>/ To issue work order to :</i>
                        </p>
                      </section>
                      <!-- Section 2 -->
                      <section>
                        <table class="w-100">
                          <tbody>
                            <tr>
                              <td class="pe-16">
                                <h6 class="text-base font-weight-medium">
                                  Nama / <i>Name</i>
                                </h6>
                              </td>
                              <td>:</td>
                              <td class="text-base ms-2">
                                <span class="ms-2">{{ vendorData.vnd_contact_person || '-' }}</span>
                              </td>
                            </tr>
                            <tr>
                              <td class="pe-16">
                                <h6 class="text-base font-weight-medium">
                                  Jabatan / <i>Position</i>
                                </h6>
                              </td>
                              <td>:</td>
                              <td class="text-start ms-2">
                                <span class="ms-2">Direktur {{ vendorData.vnd_name || '-' }}</span>
                              </td>
                            </tr>
                            <tr>
                              <td class="pe-16">
                                <h6 class="text-base font-weight-medium">
                                  Alamat / <i>Address</i>
                                </h6>
                              </td>
                              <td>:</td>
                              <td>
                                <span class="ms-2">{{ vendorData.vnd_address || '-' }}</span>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                        <p class="mt-5 w-75">
                          Selanjutnya disebut sebagai <strong>PIHAK KEDUA</strong> <i>/ Hereinafter referred to as the <strong>SECOND PARTY</strong></i> Untuk melaksanakan pekerjaan <i>/ To carry out the work :</i>
                        </p>
                      </section>
                      <p class="my-5 text-center">
                        <strong>"{{ SPKData.spk_jobdesc_summary || '-' }}"</strong>
                      </p>
                      <!-- Section 3 -->
                      <section>
                        <template v-if="allDataContract" v-for="(c, indexC) in allDataContract">
                          <table class="w-100">
                            <tbody>
                              <tr>
                                <td class="pe-16">
                                  <h6 class="text-base font-weight-medium">
                                    Waktu pelaksanaan / <i>Execution time</i>
                                  </h6>
                                </td>
                                <td>:</td>
                                <td class="text-start ms-2">
                                  <span class="ms-2">{{ dayjs(SPKData.spk_start_date).format('DD MMMM YYYY') }} S/d {{ dayjs(SPKData.spk_end_date).format('DD MMMM YYYY') }}</span>
                                </td>
                              </tr>
                              <tr>
                                <td class="pe-16">
                                  <h6 class="text-base font-weight-medium">
                                    Sifat Perintah Kerja / <i>Type Of Work Order</i>
                                  </h6>
                                </td>
                                <td>:</td>
                                <td class="text-start ms-2">
                                  <span class="ms-2">{{ c.data.con_priority_id == '1' ? 'Segera' : 'Tidak Segera' }}</span>
                                </td>
                              </tr>
                              <tr>
                                <td class="pe-16">
                                  <h6 class="text-base font-weight-medium">
                                    Jam Kerja / <i>Working Hour</i>
                                  </h6>
                                </td>
                                <td>:</td>
                                <td class="d-flex align-content-center gap-3">
                                  <div>
                                    <AppTextField
                                      class="ms-2"
                                      style="width:200px"
                                      v-model="shiftData"
                                      :rules="[requiredValidator]"
                                      :error-messages="props.errors?.shiftData"
                                      density="compact"
                                    />
                                    <VTooltip open-delay="100" location="top" activator="parent">
                                      {{ shiftData }}
                                    </VTooltip>
                                  </div>
                                  <p><small class="text-error">hh.mm-hh.mm (ex: 08.00-17.00)</small></p>
                                </td>
                              </tr>
                              <tr>
                                <td class="pe-16">
                                  <h6 class="text-base font-weight-medium">
                                    ID Project
                                  </h6>
                                </td>
                                <td>:</td>
                                <td>
                                  <span class="ms-2">{{ c.data.con_id_project || '-' }}</span>
                                </td>
                              </tr>
                              <tr>
                                <td class="pe-16">
                                  <h6 class="text-base font-weight-medium">
                                    Keterangan / <i>Description</i> :
                                  </h6>
                                </td>
                              </tr>
                              <tr>
                                <td class="pe-16">
                                  <h6 class="text-base font-weight-medium">
                                    - Bussiness Unit
                                  </h6>
                                </td>
                                <td>:</td>
                                <td>
                                  <span class="ms-2">{{ c.data.con_bu + ' - ' + c.data.description }}</span>
                                </td>
                              </tr>
                              <tr>
                                <td class="pe-16">
                                  <h6 class="text-base font-weight-medium">
                                    - Cost Centre
                                  </h6>
                                </td>
                                <td>:</td>
                                <td>
                                  <span class="ms-2">{{ c.cc.map((dataCC) => dataCC.tbc_code + ' - ' + dataCC.description).join(' ,') || '-' }}</span>
                                </td>
                              </tr>
                              <tr>
                                <td class="pe-16">
                                  <h6 class="text-base font-weight-medium">
                                    - Work Centre
                                  </h6>
                                </td>
                                <td>:</td>
                                <td>
                                  <span class="ms-2">{{ c.wc.map((dataWC) => dataWC.tbc_code + ' - ' + dataWC.description).join(' ,') || '-' }}</span>
                                </td>
                              </tr>
                              <tr>
                                <td class="pe-16">
                                  <h6 class="text-base font-weight-medium">
                                    - PPS Number
                                  </h6>
                                </td>
                                <td>:</td>
                                <td>
                                  <span class="ms-2">{{ c.data.con_pps_no || '-' }}</span>
                                </td>
                              </tr>
                              <tr v-if="c.contractJob" v-for="(cj, indexCJ) in c.contractJob">
                                <td class="pe-16">
                                  <h6 class="text-base font-weight-medium">
                                    Pekerjaan / <i>Work</i>
                                  </h6>
                                </td>
                                <td>:</td>
                                <td>
                                  <span class="ms-2">{{ SPKData.spk_jobdesc_summary || '-' }}</span>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                          <!-- Mandays -->
                          <template v-if="c.contractJob" v-for="(cj, indexCJ) in c.contractJob" :key="cj.data.cjb_id">
                            <template v-if="cj.data.cjb_pay_template === 'mandays'">
                              <VTable class="border collapse mt-3 mb-5 w-100" height="max-content" fixed-header>
                                <tbody>
                                  <tr>
                                    <td>
                                      <h6 class="text-base font-weight-medium">
                                        Tenaga Kerja
                                      </h6>
                                    </td>
                                    <td>
                                      <h6 class="text-base font-weight-medium">
                                        100%*
                                      </h6>
                                    </td>
                                    <td>
                                      <h6 class="text-base font-weight-medium">
                                        108%*
                                      </h6>
                                    </td>
                                    <td>
                                      <h6 class="text-base font-weight-medium">
                                        Tagihan 105%*
                                      </h6>
                                    </td>
                                    <td>
                                      <h6 class="text-base font-weight-medium">
                                        Retensi 3%*
                                      </h6>
                                    </td>
                                  </tr>
                                  <tr v-for="(rate, rateIndex) in cj.rate">
                                    <td class="pe-16">
                                      <h6 class="text-base font-weight-medium">
                                        {{ rate.rtk_id_jenis_tk + ' = ' + parseInt(rate.cjl_qty) + ' Orang' }}
                                      </h6>
                                    </td>
                                    <td class="pe-16">
                                      <h6 class="text-base font-weight-medium">
                                        {{ IDRFormat(parseInt(rate.rtk_rate)) + ' /Org/Hari' }}
                                      </h6>
                                    </td>
                                    <td class="pe-16">
                                      <h6 class="text-base font-weight-medium">
                                        {{ IDRFormat(parseInt(rate.rtk_rate * 1.08)) + ' /Org/Hari' }}
                                      </h6>
                                    </td>
                                    <td class="pe-16">
                                      <h6 class="text-base font-weight-medium">
                                        {{ IDRFormat(parseInt(rate.rtk_rate * 1.05)) + ' /Org/Hari' }}
                                      </h6>
                                    </td>
                                    <td class="pe-16">
                                      <h6 class="text-base font-weight-medium">
                                        {{ IDRFormat(parseInt(rate.rtk_rate * 0.03)) + ' /Org/Hari' }}
                                      </h6>
                                    </td>
                                  </tr>
                                </tbody>
                              </VTable>
                            </template>
                          </template>
                          <!-- Fixed -->
                          <template v-if="c.contractJob" v-for="(cj, indexCJ) in c.contractJob" :key="cj.data.cjb_id">
                            <template v-if="cj.data.cjb_pay_template === 'fixed'">
                              <table class="w-100">
                                <tbody>
                                  <tr>
                                    <td class="pe-16">
                                      <h6 class="text-base font-weight-medium">
                                        Nilai Harga Borongan / <i>Wholesale Price Value</i>
                                      </h6>
                                    </td>
                                    <td>:</td>
                                    <td>
                                      <span class="ms-2">{{ IDRFormat(cj.data.cjb_rate) }}</span>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </template>
                          </template>
                          <!-- Flat -->
                          <template v-if="c.contractJob" v-for="(cj, indexCJ) in c.contractJob" :key="cj.data.cjb_id">
                            <template v-if="cj.data.cjb_pay_template === 'flat'">
                              <table class="w-100">
                                <tbody>
                                  <tr>
                                    <td class="pe-16">
                                      <h6 class="text-base font-weight-medium">
                                        Nilai Harga / <i>Price Value</i>
                                      </h6>
                                    </td>
                                    <td>:</td>
                                    <td>
                                      <span class="ms-2">{{ IDRFormat(cj.data.cjb_rate) + ' /' + cj.data.cjb_pay_type }}</span>
                                      <span v-if="cj.data.cjb_pay_type == 'kg'">
                                        <i>(Dikontrol berdasarkan timbangan)</i>
                                      </span>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </template>
                          </template>
                          <!-- Flat 2 -->
                          <template v-if="c.contractJob" v-for="(cj, indexCJ) in c.contractJob" :key="cj.data.cjb_id">
                            <template v-if="cj.data.cjb_pay_template === 'flat2'">
                              <table class="w-100">
                                <tbody>
                                  <tr>
                                    <td class="pe-16">
                                      <h6 class="text-base font-weight-medium">
                                        Nilai Harga Borongan / <i>Wholesale Price Value</i>
                                      </h6>
                                    </td>
                                    <td>:</td>
                                    <td>
                                      <span class="ms-2">{{ IDRFormat(cj.data.cjb_rate) + ' /' + cj.data.cjb_pay_type }}</span>
                                      <span v-if="cj.data.cjb_pay_type == 'kg'">
                                        <i>(Dikontrol berdasarkan timbangan)</i>
                                      </span>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </template>
                          </template>
                          <!-- P1 & Rit-->
                          <template v-if="c.contractJob" v-for="(cj, indexCJ) in c.contractJob" :key="cj.data.cjb_id">
                            <template v-if="cj.data.cjb_pay_template === 'p1' || cj.data.cjb_pay_template === 'rit'">
                              <VTable class="border collapse mt-3 mb-5 w-100" height="max-content" fixed-header>
                                <tbody>
                                  <tr>
                                    <td>
                                      <h6 class="text-base font-weight-medium">
                                        100%*
                                      </h6>
                                    </td>
                                    <td>
                                      <h6 class="text-base font-weight-medium">
                                        108%*
                                      </h6>
                                    </td>
                                    <td>
                                      <h6 class="text-base font-weight-medium">
                                        Tagihan 105%*
                                      </h6>
                                    </td>
                                    <td>
                                      <h6 class="text-base font-weight-medium">
                                        Retensi 3%*
                                      </h6>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="pe-16">
                                      <h6 class="text-base font-weight-medium">
                                        {{ IDRFormat(parseInt(cj.data.cjb_rate)) + ' /' + cj.data.cjb_pay_type }}
                                      </h6>
                                    </td>
                                    <td class="pe-16">
                                      <h6 class="text-base font-weight-medium">
                                        {{ IDRFormat(parseInt(cj.data.cjb_rate * 1.08)) + ' /' + cj.data.cjb_pay_type }}
                                      </h6>
                                    </td>
                                    <td class="pe-16">
                                      <h6 class="text-base font-weight-medium">
                                        {{ IDRFormat(parseInt(cj.data.cjb_rate * 1.05)) + ' /' + cj.data.cjb_pay_type }}
                                      </h6>
                                    </td>
                                    <td class="pe-16">
                                      <h6 class="text-base font-weight-medium">
                                        {{ IDRFormat(parseInt(cj.data.cjb_rate * 0.03)) + ' /' + cj.data.cjb_pay_type }}
                                      </h6>
                                    </td>
                                  </tr>
                                </tbody>
                              </VTable>
                            </template>
                          </template>
                          <!-- Range-->
                          <template v-if="c.contractJob" v-for="(cj, indexCJ) in c.contractJob" :key="cj.data.cjb_id">
                            <template v-if="cj.data.cjb_pay_template === 'range' && cj.range && cj.range.length > 0" >
                              <VTable class="border collapse mt-3 mb-5 w-100" height="max-content" fixed-header>
                                <thead>
                                  <tr>
                                    <th scope="col">
                                      Minimal Produksi
                                    </th>
                                    <th scope="col">
                                      Maksimal Produksi
                                    </th>
                                    <th scope="col">
                                      UoM
                                    </th>
                                    <th scope="col">
                                      Harga (Rp)
                                    </th>
                                    <th scope="col">
                                      Maksimal Perhitungan
                                    </th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr v-for="(r, indexCJ) in cj.range" :key="r.id">
                                    <td style="width: 150px;">
                                      <AppTextField
                                        v-model="r.min_produksi"
                                        placeholder="Type here..."
                                        :rules="[requiredValidator]"
                                        :error-messages="props.errors?.min_produksi"
                                        type="number"
                                        density="compact"
                                      />
                                    </td>
                                    <td style="width: 150px;">
                                      <AppTextField
                                        v-model="r.max_produksi"
                                        placeholder="Type here..."
                                        :rules="[requiredValidator]"
                                        :error-messages="props.errors?.max_produksi"
                                        type="number"
                                        density="compact"
                                      />
                                    </td>
                                    <td style="width: 150px;">
                                      <AppAutocomplete
                                        placeholder="Select UoM"
                                        v-model="r.uom"
                                        :items="dataMerMeasurementUnit"
                                        :rules="[requiredValidator]"
                                        :item-title="'title'"
                                        :item-value="'value'"
                                        :error-messages="props.errors?.uom"
                                      />
                                    </td>
                                    <td style="width: 150px;">
                                      <AppTextField
                                        v-model="r.harga"
                                        placeholder="Type here..."
                                        :rules="[requiredValidator]"
                                        :error-messages="props.errors?.harga"
                                        type="number"
                                        density="compact"
                                      />
                                    </td>
                                    <td style="width: 190px;">
                                      <AppTextField
                                        v-model="r.max_batas"
                                        placeholder="Type here..."
                                        :rules="[requiredValidator]"
                                        :error-messages="props.errors?.max_batas"
                                        density="compact"
                                      />
                                    </td>
                                  </tr>
                                </tbody>
                              </VTable>
                            </template>
                          </template>
                          <!-- Job Target -->
                          <template v-if="c.contractJob" v-for="(cj, indexCJ) in c.contractJob" :key="cj.data.cjb_id">
                            <table class="w-100">
                              <tbody>
                                <tr>
                                  <td class="pe-16">
                                    <h6 class="text-base font-weight-medium">
                                      Job Target
                                    </h6>
                                  </td>
                                  <td>:</td>
                                  <td>
                                    <span class="ms-2">{{ c.data.con_comment_jobtarget || '-' }}</span>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </template>
                          <!-- Retensi Noted -->
                          <template v-if="c.contractJob" v-for="(cj, indexCJ) in c.contractJob" :key="cj.data.cjb_id">
                            <template v-if="cj.data.cjb_pay_template === 'mandays' || cj.data.cjb_pay_template === 'p1' || cj.data.cjb_pay_template === 'rit'">
                              <table class="w-100">
                                <tbody>
                                  <tr>
                                    <td>
                                      <p class="w-50">*Retensi dibayar setiap 6 (enam) bulan sekali dengan dasar penilaian (terlampir)</p>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </template>
                          </template>
                        </template>
                      </section>
                      <!-- Section 4 -->
                      <section class="mt-5">
                        <AppTextarea
                          v-model="SPKData.spk_box_bpjs"
                          rows="2"
                          placeholder="Type here..."
                          :rules="[requiredValidator]"
                          :error-messages="props.errors?.spk_box_bpjs"
                          auto-grow
                        />
                        <table class="w-100 mt-5">
                          <tbody>
                            <tr>
                              <td class="pe-16">
                                <h6 class="text-base font-weight-medium">
                                  Cara Pembayaran / <i>Payment Method</i>
                                </h6>
                              </td>
                              <td>:</td>
                              <td>
                                <div class="border rounded px-3 py-1 ms-2">
                                  <EditorContent :editor="editorPaymentMethod" :error-messages="props.errors?.spk_cara_bayar"/>
                                  <div
                                    v-if="editorPaymentMethod"
                                    class="d-flex justify-end flex-wrap gap-x-2"
                                  >
                                    <VIcon
                                      icon="tabler-bold"
                                      :color="editorPaymentMethod.isActive('bold') ? 'primary' : ''"
                                      size="20"
                                      @click="editorPaymentMethod.chain().focus().toggleBold().run()"
                                    />

                                    <VIcon
                                      :color="editorPaymentMethod.isActive('underline') ? 'primary' : ''"
                                      icon="tabler-underline"
                                      size="20"
                                      @click="editorPaymentMethod.commands.toggleUnderline()"
                                    />

                                    <VIcon
                                      :color="editorPaymentMethod.isActive('italic') ? 'primary' : ''"
                                      icon="tabler-italic"
                                      size="20"
                                      @click="editorPaymentMethod.chain().focus().toggleItalic().run()"
                                    />

                                    <VIcon
                                      :color="editorPaymentMethod.isActive('bulletList') ? 'primary' : ''"
                                      icon="tabler-list"
                                      size="20"
                                      @click="editorPaymentMethod.chain().focus().toggleBulletList().run()"
                                    />

                                    <VIcon
                                      :color="editorPaymentMethod.isActive('orderedList') ? 'primary' : ''"
                                      icon="tabler-list-numbers"
                                      size="20"
                                      @click="editorPaymentMethod.chain().focus().toggleOrderedList().run()"
                                    />

                                    <VIcon
                                      icon="tabler-link"
                                      size="20"
                                      @click="setLinkPaymentMethod"
                                    />
                                  </div>
                                </div>
                              </td>
                            </tr>
                            <tr>
                              <td class="pe-16">
                                <h6 class="text-base font-weight-medium">
                                  Tahapan Pembayaran / <i>Payment Stages</i>
                                </h6>
                              </td>
                              <td>:</td>
                              <td>
                                <div class="border rounded px-3 py-1 ms-2 mt-5">
                                  <EditorContent :editor="editorPaymentStages" :error-messages="props.errors?.spk_tahap_bayar"/>
                                  <div
                                    v-if="editorPaymentStages"
                                    class="d-flex justify-end flex-wrap gap-x-2"
                                  >
                                    <VIcon
                                      icon="tabler-bold"
                                      :color="editorPaymentStages.isActive('bold') ? 'primary' : ''"
                                      size="20"
                                      @click="editorPaymentStages.chain().focus().toggleBold().run()"
                                    />

                                    <VIcon
                                      :color="editorPaymentStages.isActive('underline') ? 'primary' : ''"
                                      icon="tabler-underline"
                                      size="20"
                                      @click="editorPaymentStages.commands.toggleUnderline()"
                                    />

                                    <VIcon
                                      :color="editorPaymentStages.isActive('italic') ? 'primary' : ''"
                                      icon="tabler-italic"
                                      size="20"
                                      @click="editorPaymentStages.chain().focus().toggleItalic().run()"
                                    />

                                    <VIcon
                                      :color="editorPaymentStages.isActive('bulletList') ? 'primary' : ''"
                                      icon="tabler-list"
                                      size="20"
                                      @click="editorPaymentStages.chain().focus().toggleBulletList().run()"
                                    />

                                    <VIcon
                                      :color="editorPaymentStages.isActive('orderedList') ? 'primary' : ''"
                                      icon="tabler-list-numbers"
                                      size="20"
                                      @click="editorPaymentStages.chain().focus().toggleOrderedList().run()"
                                    />

                                    <VIcon
                                      icon="tabler-link"
                                      size="20"
                                      @click="setLinkPaymentStages"
                                    />
                                  </div>
                                </div>
                              </td>
                            </tr>
                            <tr>
                              <td class="pe-16">
                                <h6 class="text-base font-weight-medium">
                                  Lainnya / <i>Other</i>
                                </h6>
                              </td>
                              <td>:</td>
                              <td>
                                <div class="border rounded px-3 py-1 ms-2 mt-5">
                                  <EditorContent :editor="editorOther" :error-messages="props.errors?.spk_lain_lain"/>
                                  <div
                                    v-if="editorOther"
                                    class="d-flex justify-end flex-wrap gap-x-2"
                                  >
                                    <VIcon
                                      icon="tabler-bold"
                                      :color="editorOther.isActive('bold') ? 'primary' : ''"
                                      size="20"
                                      @click="editorOther.chain().focus().toggleBold().run()"
                                    />

                                    <VIcon
                                      :color="editorOther.isActive('underline') ? 'primary' : ''"
                                      icon="tabler-underline"
                                      size="20"
                                      @click="editorOther.commands.toggleUnderline()"
                                    />

                                    <VIcon
                                      :color="editorOther.isActive('italic') ? 'primary' : ''"
                                      icon="tabler-italic"
                                      size="20"
                                      @click="editorOther.chain().focus().toggleItalic().run()"
                                    />

                                    <VIcon
                                      :color="editorOther.isActive('bulletList') ? 'primary' : ''"
                                      icon="tabler-list"
                                      size="20"
                                      @click="editorOther.chain().focus().toggleBulletList().run()"
                                    />

                                    <VIcon
                                      :color="editorOther.isActive('orderedList') ? 'primary' : ''"
                                      icon="tabler-list-numbers"
                                      size="20"
                                      @click="editorOther.chain().focus().toggleOrderedList().run()"
                                    />

                                    <VIcon
                                      icon="tabler-link"
                                      size="20"
                                      @click="setLinkOther"
                                    />
                                  </div>
                                </div>
                              </td>
                            </tr>
                        </tbody>
                      </table>
                      </section>
                      <!-- Section 5 -->
                      <section class="mt-5">
                        <p>
                          Demikian Surat Perintah Kerja ini dibuat dan agar dapat digunakan sebagaimana mestinya / <i>This work order letter is hereby made and should be used accordingly</i>
                        </p>
                        <p>
                          Hormat Kami / <i>Yours Sincerely</i>
                        </p>
                        <div class="mt-5 d-flex justify-space-between">
                          <div class="text-center">
                            <p>Penerima SPK / <i>SPK Recipient</i></p>
                            <strong>{{ vendorData.vnd_name || '-' }}</strong>
                            <div style="margin-top: 130px">
                              <p>
                                <u>
                                  {{ vendorData.vnd_contact_person || '-' }}
                                </u>
                              </p>
                              <p v-if="signType.st_id">Pemborong</p>
                            </div>
                          </div>
                          <div class="text-center">
                            <p>Diketahui / <i>Acknowledged by</i></p>
                            <div style="margin-top: 170px">
                              <p>Bag. Pembelian</p>
                            </div>
                          </div>
                          <div class="text-center">
                            <p>Pemberi SPK / <i>SPK Giver</i></p>
                            <strong>{{ companyData.company_name || '-' }}</strong>
                            <div v-if="signType.st_id" class="d-flex justify-space-between" style="width: 350px;">
                              <div style="margin-top: 130px" v-for="(user, userIndex) in allUserSignature">
                                <p>
                                  <u>
                                    {{ user.usr_display_name || '-' }}
                                  </u>
                                </p>
                                <p>{{ user.usr_jabatan }}</p>
                              </div>
                            </div>
                          </div>
                        </div>
                        <p class="border" style="padding: 10px;">
                          The company does not collect any fees from SPK recipients. If any party attempts to collect fees, SPK recipients should immediately send a letter to whistleblower@gyssteel.com or send SMS/call to mobile number 0813 828 50 888. SPK recipients are PROHIBITED from giving anything in any form to employees of PT Garuda Yamato Steel and their families.
                        </p>
                        <p class="border" style="padding: 10px;">
                          If these above provisions are violated, the company will not make payment to the SPK recipient (Black List).
                        </p>
                        <p class="mt-5">
                          <small>Created by: {{ userAuth.usr_display_name }} versi {{ dayjs().format(`DD/M/YYYY`) }}</small>
                        </p>
                      </section>
                    </div>
                  </VCard>
                </VCol>
                <VCol cols="12">
                  <div class="d-flex flex-wrap gap-4 justify-end mt-8">
                    <div class="card__actions d-flex justify-end" v-if="isLoadingSPK">
                      <Skeletor width="96" height="36" class="me-3"/>
                      <Skeletor width="120" height="36" />
                    </div>
                    <div v-if="!isLoadingSPK">
                      <VBtn
                        class="me-5"
                        :loading="loadingBtnSecond[0]"
                        :disabled="isDisabled"
                        color="success"
                        type="button"
                        @click="validatePrintSPK('Save')"
                      >
                        Save
                        <VIcon
                          icon="tabler-device-floppy"
                          end
                          class="flip-in-rtl"
                        />
                      </VBtn>
                      <VBtn
                        variant="outlined"
                        type="button"
                        @click="validatePrintSPK('Print')"
                      >
                        Save & Print
                        <VIcon
                          :loading="loadingBtnThird[0]"
                          :disabled="isDisabled"
                          icon="tabler-printer"
                          end
                          class="flip-in-rtl"
                        />
                      </VBtn>
                    </div>
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
