<script setup>
import { integerValidator } from '@/@core/utils/validators';
import JobType from '@/views/apps/pps/onGoing/JobType.vue';
import { getListCompany } from '@db/apps/company/db';
import { getListMerBU, getListMerCC, getListMerJobType, getListMerLaborType, getListMerMeasurementUnit, getListMerPaymentType, getListMerVendor, getListMerWC } from '@db/apps/mer/db';
import { Link } from '@tiptap/extension-link';
import { Placeholder } from '@tiptap/extension-placeholder';
import { Underline } from '@tiptap/extension-underline';
import { StarterKit } from '@tiptap/starter-kit';
import {
  EditorContent,
  useEditor,
} from '@tiptap/vue-3';
import dayjs from 'dayjs';
import 'vue-skeletor/dist/vue-skeletor.css';

const emit = defineEmits([
  'update:isDialogVisible',
  'PPSData',
  'isSnackbarResponse',
  'isSnackbarResponseAlertColor',
  'errorMessages',
  'errors',
  'updateRangeIncrement'
]);

const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  typeDialog: {
    type: String,
    required: true
  },
  conId: {
    type: Number,
    required: true,
  },
  cjbId: {
    type: Number,
    required: true,
  },
  conReqId: {
    type: Number,
    required: true,
  },
  rangeIncrement: {
    type: Array,
    required: true,
    default: () => {
      return [];
    }
  },
  fetchTrigger: {
    type: Number,
    default: 0
  },
  errors: {
    type: Object,
    required: false,
    default: () => ({})
  },
  isSuccessNextStep: {
    type: Boolean,
    required: true,
    default: false,
  },
  isDisabled: {
    type: Boolean,
    required: true,
    default: false,
  },
});

const dataCompany = ref([])
const dataMerBU = ref([])
const dataMerCC = ref([])
const dataMerWC = ref([])
const dataMerVendor = ref([])
const dataMerJobType = ref([])
const dataMerMeasurementUnit = ref([])
const dataMerPaymentType = ref([])
const dataMerLaborType = ref([])
const allShiftData = ref([])
const numberedSteps = ref([])
const jobTarget = ref()
const isPPSOngoingDialogViewPathVisible = ref(false)
const pathData = ref('')
const isLoading = ref(true)
const refPPSForm = ref()
const refJobTypeForm = ref()
const typeDialog = computed(() => props.typeDialog)
const conId = computed(() => props.conId)
const conReqId = computed(() => props.conReqId)
const loadingBtn = ref([])
const loadingBtnSecond = ref([])
const isDisabled = ref(false)
const currentStep = ref(0)
const isCurrentStepValid = ref(true)
const isFileAttachment = ref('')
const token = useCookie('accessToken')
const laborData = reactive({
  labor: [
    {id: 0, type: null, qty: null},
  ]
})

const dataCJTType = ref([
  {
    title: "Same With UOM",
    value: "1"
  },
  {
    title: "Percentage",
    value: "2"
  },
])

const ppsOngoingData = reactive({
  rows: [],
  // PPS form
  company: null,
  bu: null,
  cc: [],
  wc: [],
  work_location: null,
  ck_wl: false,
  id_project: null,
  pps_no: null,
  old_pps_no: null,
  priority: null,
  shift_checklist: [],
  cp_name: null,
  cp_dept: null,
  cp_ext: null,
  cp_email: null,
  comment: '',
  duration: null,
  suggest_vendor: null,
  con_comment_jobtarget: '',
  file_attachment: null,
  // Job Type form
  job_type: null,
  job_desc: null,
  pic: null,
  payment_type: null,
  total_job_target_qty: null,
  uom: null,
  labor_type: null,
  labor_qty: null,
  cjt_type: null,
  cjt_qty: null,
  total: null,
})

// Editor Target Estimate
const editorTargetEstimate = useEditor({
  content: '',
  extensions: [
    StarterKit,
    Placeholder.configure({ placeholder: 'Enter a target estimate...' }),
    Underline,
    Link.configure({ openOnClick: true }),
  ],
  onUpdate: ({ editor }) => {
    ppsOngoingData.con_comment_jobtarget = editor.getHTML()
  },
})

const setLinkTargetEstimate = () => {
  const previousUrl = editorTargetEstimate.value?.getAttributes('link').href
  const url = window.prompt('URL', previousUrl)

  if (url === null)
    return

  if (url === '') {
    editorTargetEstimate.value?.chain().focus().extendMarkRange('link').unsetLink().run()
    return
  }

  editorTargetEstimate.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}

// Editor Comment
const editorComment = useEditor({
  content: '',
  extensions: [
    StarterKit,
    Placeholder.configure({ placeholder: 'Enter a comment...' }),
    Underline,
    Link.configure({ openOnClick: true }),
  ],
  onUpdate: ({ editor }) => {
    ppsOngoingData.comment = editor.getHTML()
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
  ppsOngoingData.rows = [];
  laborData.labor = [{id: Date.now(), type: null, qty: null},]
  // PPS form
  ppsOngoingData.company = null;
  ppsOngoingData.bu = null;
  ppsOngoingData.cc = [];
  ppsOngoingData.wc = [];
  ppsOngoingData.work_location = null;
  ppsOngoingData.ck_wl = false;
  ppsOngoingData.id_project = null;
  ppsOngoingData.pps_no = null;
  ppsOngoingData.old_pps_no = null;
  ppsOngoingData.priority = null;
  ppsOngoingData.shift_checklist = [];
  ppsOngoingData.cp_name = null;
  ppsOngoingData.cp_dept = null;
  ppsOngoingData.cp_ext = null;
  ppsOngoingData.cp_email = null;
  ppsOngoingData.comment = '';
  ppsOngoingData.duration = null;
  ppsOngoingData.suggest_vendor = null;
  ppsOngoingData.con_comment_jobtarget = '';
  ppsOngoingData.file_attachment = null;
  editorComment.value?.commands.setContent('')
  editorTargetEstimate.value?.commands.setContent('')
  isFileAttachment.value = ''
  jobTarget.value = []
  // Job Type form
  ppsOngoingData.job_type = null;
  ppsOngoingData.job_desc = null;
  ppsOngoingData.pic = null;
  ppsOngoingData.payment_type = null;
  ppsOngoingData.total_job_target_qty = null;
  ppsOngoingData.uom = null;
  ppsOngoingData.labor_type = null;
  ppsOngoingData.labor_qty = null;
  ppsOngoingData.cjt_type = null;
  ppsOngoingData.cjt_qty = null;
  ppsOngoingData.total = null;
  // Form all reset
  refPPSForm.value?.reset()
  refPPSForm.value?.resetValidation()
  refJobTypeForm.value?.reset()
  refJobTypeForm.value?.resetValidation()
  loadingBtn.value[0] = false;
  loadingBtnSecond.value[0] = false;
  isDisabled.value = false;
  emit('updateRangeIncrement',[])
  emit('updateTypeDialog','Add')
  emit('update:isDialogVisible', false)
}

const dialogModelJobtypeValueUpdate = () => {
  laborData.labor = [{id: Date.now(), type: null, qty: null},]
  // Job Type form
  ppsOngoingData.job_type = null;
  ppsOngoingData.job_desc = null;
  ppsOngoingData.pic = null;
  ppsOngoingData.payment_type = null;
  ppsOngoingData.total_job_target_qty = null;
  ppsOngoingData.uom = null;
  ppsOngoingData.labor_type = null;
  ppsOngoingData.labor_qty = null;
  ppsOngoingData.cjt_type = null;
  ppsOngoingData.cjt_qty = null;
  ppsOngoingData.total = null;
  refJobTypeForm.value?.reset()
  refJobTypeForm.value?.resetValidation()
  loadingBtn.value[0] = false;
  loadingBtnSecond.value[0] = false;
  isDisabled.value = false;
}

const updateInptLabor = (index, updatedData) => {
  laborData.labor[index] = updatedData;
}

const balanceCheck = () => {
  let cumulativeTotal = 0;
  ppsOngoingData.rows.forEach((row, index) => {
    cumulativeTotal += parseFloat(row.cjt_qty || 0);
    row.total = cumulativeTotal;
  });
}

const averageEvenly = () => {
  const rowCount = ppsOngoingData.rows.length;
  if (rowCount > 0) {
    const dividedValue = parseFloat(ppsOngoingData.total_job_target_qty || 0) / rowCount;

    ppsOngoingData.rows.forEach((row) => {
      row.cjt_qty = dividedValue.toFixed(2);
    });

    balanceCheck();
  }
}

const syncWorkLocation = () => {
  if (ppsOngoingData.ck_wl && ppsOngoingData.wc.length > 0) {
    console.log({ck_wl: ppsOngoingData.ck_wl});
    
    ppsOngoingData.work_location = ppsOngoingData.wc.length > 0 ? ppsOngoingData.wc : '';
  } else {
    ppsOngoingData.ck_wl = false;
    ppsOngoingData.work_location = null;
  }
}

const validatePPSForm = async () => {
  refPPSForm.value?.validate().then(valid => {
    if (valid.valid) {
      onSubmitPPS()
    } else {
      isCurrentStepValid.value = false
    }
  })
}

const validateJobTypeForm = (type) => {
  refJobTypeForm.value?.validate().then(valid => {
    if (valid.valid) {
      isCurrentStepValid.value = true
      onSubmitJobType(type)
    } else {
      isCurrentStepValid.value = false
    }
  })
}

const addItem = () => {
  laborData.labor.push({ id: Date.now(), type: null, qty: null})
}

const removeLabor = (id) => {
  laborData.labor = laborData.labor.filter((labor) => labor.id !== id);
}

const hasRequiredKeys = (row) => {
  return (
    "cjt_type" in row &&
    "cjt_qty" in row &&
    "total" in row
  )
}

const fetchCompanyData = async () => {
  try {
    const response = await getListCompany();
    if (response.status === 200) {
      const rows = response.data.rows || [];
      dataCompany.value = rows.map((row) => ({
        title: row.company_name,
        value: row.company_code,
      }));
    } else {
      console.error('Failed to fetch company data');
    }
    
  } catch (error) {
    console.error('Error fetching company data',error);
  }
}

const fetchMerBUData = async () => {
  try {
    const response = await getListMerBU();
    if (response.status === 200) {
      const rows = response.data.rows || [];
      dataMerBU.value = rows.map((row) => ({
        title: row.number + ' - ' + row.description,
        value: row.number,
      }));
    } else {
      console.error('Failed to fetch mer business units data');
    }
    
  } catch (error) {
    console.error('Error fetching mer business units data',error);
  }
}

const fetchMerCCData = async () => {
  try {
    const response = await getListMerCC();
    if (response.status === 200) {
      const rows = response.data.rows || [];
      dataMerCC.value = rows.map((row) => ({
        title: row.number + ' - ' + row.description,
        value: row.number,
      }));
    } else {
      console.error('Failed to fetch mer cost center data');
    }
    
  } catch (error) {
    console.error('Error fetching mer cost center data',error);
  }
}

const fetchMerShiftData = async () => {
  try {
    const response = await $api(`/apps/shift/list`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      onResponseError({ response }) {
        const responseData = response._data;
        const responseMessage = responseData.message;
        emit('errorMessages', responseMessage);
        throw new Error("Get data failed");
      },
    });
    
    const dataResponse = JSON.parse(JSON.stringify(response));
    if (dataResponse.status == 200) {
      const dataResult = dataResponse.data.rows;
      allShiftData.value = dataResult;
    } else {
      throw new Error("Get data failed");
    }
    
  } catch (error) {
    throw new Error("Get data failed");
  }
}

const fetchMerWCData = async () => {
  try {
    const response = await getListMerWC();
    if (response.status === 200) {
      const rows = response.data.rows || [];
      dataMerWC.value = rows.map((row) => ({
        title: row.number + ' - ' + row.description,
        value: row.number,
      }));
    } else {
      console.error('Failed to fetch mer work center data');
    }
    
  } catch (error) {
    console.error('Error fetching mer work center data',error);
  }
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
    } else {
      console.error('Failed to fetch mer vendor data');
    }
    
  } catch (error) {
    console.error('Error fetching mer vendor data',error);
  }
}

const fetchMerJobTypeData = async () => {
  try {
    const response = await getListMerJobType();
    if (response.status === 200) {
      const rows = response.data.rows || [];
      dataMerJobType.value = rows.map((row) => ({
        title: row.job_type,
        value: row.job_type_id,
      }));
    } else {
      console.error('Failed to fetch job type data');
    }
    
  } catch (error) {
    console.error('Error fetching job type data');
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
      console.error('Failed to fetch measurement unit data');
    }
    
  } catch (error) {
    console.error('Error fetching measurement unit data');
  }
}

const fetchMerPaymentTypeData = async () => {
  try {
    const response = await getListMerPaymentType();
    if (response.status === 200) {
      const rows = response.data.rows || [];
      dataMerPaymentType.value = rows.map((row) => ({
        title: row.paytype_name,
        value: row.paytype_code,
      }));
    } else {
      console.error('Failed to fetch payment type data');
    }
    
  } catch (error) {
    console.error('Error fetching payment type data');
  }
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
      console.error('Failed to fetch labor type data');
    }
    
  } catch (error) {
    console.error('Error fetching labor type data');
  }
}

const fetchPPSOngodingEdit = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/pps-ongoing/edit/${conId.value}`, {
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

      ppsOngoingData.cc = dataResult.arr_cc.map((cc) => cc.tbc_code);
      ppsOngoingData.wc = dataResult.arr_wc.map((wc) => wc.tbc_code);
      ppsOngoingData.shift_checklist = dataResult.arr_shift.map((shift) => shift.sh_shift);
      // Contract data
      ppsOngoingData.bu = dataResult.con_bu;
      ppsOngoingData.ck_wl = dataResult.con_wc_checklist == 1 ? true : false;
      ppsOngoingData.company = dataResult.con_company;
      ppsOngoingData.work_location = dataResult.con_work_location;
      ppsOngoingData.id_project = dataResult.con_id_project;
      ppsOngoingData.pps_no = dataResult.con_pps_no;
      ppsOngoingData.old_pps_no = dataResult.con_old_pps_no;
      ppsOngoingData.priority = dataResult.con_priority_id == 1 ? "segera" : "tidak segera";
      ppsOngoingData.cp_name = dataResult.con_cp_name;
      ppsOngoingData.cp_dept = dataResult.con_cp_dept;
      ppsOngoingData.cp_ext = dataResult.con_cp_exthp;
      ppsOngoingData.cp_email = dataResult.con_cp_email;
      ppsOngoingData.duration = dataResult.con_duration_start + ' to ' + dataResult.con_duration_end;
      ppsOngoingData.suggest_vendor = dataResult.ven_id;
      ppsOngoingData.con_comment_jobtarget = dataResult.con_comment_jobtarget;
      ppsOngoingData.comment = dataResult.con_comment_bu;

      editorComment.value?.commands.setContent(dataResult.con_comment_bu || null)
      editorTargetEstimate.value?.commands.setContent(dataResult.con_comment_jobtarget || null)
      isFileAttachment.value = dataResult.con_file_attachment;
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

const fetchContractJobSingleEdit = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/contract-job/edit/${props.cjbId}`, {
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
        emit('update:isDialogSingleVisible', false)
        throw new Error("Get data failed");
      },
    });
    
    const dataResponse = JSON.parse(JSON.stringify(response));
    if (dataResponse.status == 200) {
      const dataResult = dataResponse.data;
      isLoading.value = false;

      ppsOngoingData.job_type = dataResult.job_type.map((jt) => jt.job_type_id);
      ppsOngoingData.job_desc = dataResult.contract_job.cjb_desc;
      ppsOngoingData.pic = dataResult.contract_job.cjb_pic;
      ppsOngoingData.payment_type = dataResult.contract_job.cjb_pay_type;
      ppsOngoingData.total_job_target_qty = dataResult.contract_job.cjb_qty;
      ppsOngoingData.uom = dataResult.contract_job.unt_id;
      laborData.labor = dataResult.job_labor.map((jl) => ({
        id: jl.cjl_id,
        type: jl.cjl_type,
        qty: jl.cjl_qty
      }))
      ppsOngoingData.rows = dataResult.job_target.map((jt) => ({
        cjt_type: jt.cjt_type,
        cjt_qty: jt.cjt_qty,
        total: jt.cjt_total
      }))
    } else {
      emit('update:isDialogSingleVisible', false)
      emit('isSnackbarResponse',true)
      emit('isSnackbarResponseAlertColor', 'error')
      throw new Error("Get data failed");
    }
  } catch (error) {
    isLoading.value = false;
    emit('update:isDialogSingleVisible', false)
    emit('isSnackbarResponse',true)
    emit('isSnackbarResponseAlertColor', 'error')
  }
}

const onSubmitPPS = () => {
  const formData = new FormData();
  // Form On Going
  formData.append('company', ppsOngoingData.company || null);
  formData.append('bu', ppsOngoingData.bu || null);
  formData.append('cc', ppsOngoingData.cc || []);
  formData.append('wc', ppsOngoingData.wc || []);
  formData.append('work_location', ppsOngoingData.work_location || null);
  formData.append('ck_wl', ppsOngoingData.ck_wl || false);
  formData.append('id_project', ppsOngoingData.id_project || null);
  formData.append('pps_no', ppsOngoingData.pps_no || null);
  formData.append('old_pps_no', ppsOngoingData.old_pps_no || null);
  formData.append('priority', ppsOngoingData.priority || null);
  formData.append('shift_checklist', JSON.stringify(ppsOngoingData.shift_checklist) || []);
  formData.append('cp_name', ppsOngoingData.cp_name || null);
  formData.append('cp_dept', ppsOngoingData.cp_dept || null);
  formData.append('cp_ext', ppsOngoingData.cp_ext || null);
  formData.append('cp_email', ppsOngoingData.cp_email || null);
  formData.append('comment', ppsOngoingData.comment || '');
  formData.append('duration', JSON.stringify(ppsOngoingData.duration) || null);
  formData.append('suggest_vendor', ppsOngoingData.suggest_vendor || null);
  formData.append('file_attachment', ppsOngoingData.file_attachment || null);
  formData.append('con_comment_jobtarget', ppsOngoingData.con_comment_jobtarget || '');

  const formDataToObject = Object.fromEntries(formData.entries());

  try {
    loadingBtn.value[0] = true
    const mode = props.typeDialog;
    emit("PPSData", { mode, formData: {...formDataToObject}, dialogUpdate: dialogModelValueUpdate });
  } catch (err) {
    loadingBtn.value[0] = false
  }
}

const onSubmitJobType = (type) => {
  const formData = new FormData();
  // Form Job Type
  formData.append('labor_type', ppsOngoingData.labor_type || null);
  formData.append('labor_qty', ppsOngoingData.labor_qty || null);
  formData.append('job_type', ppsOngoingData.job_type || null);
  formData.append('job_desc', ppsOngoingData.job_desc || null);
  formData.append('pic', ppsOngoingData.pic || null);
  formData.append('payment_type', ppsOngoingData.payment_type || null);
  formData.append('total_job_target_qty', ppsOngoingData.total_job_target_qty || null);
  formData.append('uom', ppsOngoingData.uom || null);
  formData.append('cjt_type', ppsOngoingData.cjt_type || null);
  formData.append('cjt_qty', ppsOngoingData.cjt_qty || null);
  formData.append('total', ppsOngoingData.total || null);

  const formDataToObject = Object.fromEntries(formData.entries());

  try {
    isDisabled.value = true
    if(type == 'Save')
      loadingBtn.value[0] = true;
    else if(type == 'Continue')
      loadingBtnSecond.value[0] = true;
    emit("JobTypeData", { type ,formData: {...formDataToObject}, dialogUpdate: dialogModelValueUpdate, dialogJobtypeUpdate: dialogModelJobtypeValueUpdate });
  } catch (err) {
    isDisabled.value = false;
    loadingBtn.value[0] = false;
    loadingBtnSecond.value[0] = false;
  }
}

const openPathDialog = (path) => {
  pathData.value = path;
  isPPSOngoingDialogViewPathVisible.value = true;
}

watch(
  [() => conId.value, () => conReqId.value, () => typeDialog.value, () => props.cjbId, () => props.fetchTrigger, () => props.isSuccessNextStep],
  ([newId,newConReq,newType, newCjbId]) => {
      if (newType === "Edit" && newId) {
        fetchPPSOngodingEdit();
      } else if (newType === "Add") {
        isLoading.value = false;
        if(props.isSuccessNextStep) {
          currentStep.value++
          isCurrentStepValid.value = true
        }
      }

      if(newType === 'Add Job Type') {
        dialogModelJobtypeValueUpdate()
      }
      
      if(newType !== 'List Job Type' && newType != '') {
        fetchCompanyData()
        fetchMerBUData()
        fetchMerCCData()
        fetchMerWCData()
        fetchMerVendorData()
        fetchMerShiftData()
      } 
      if(newType == 'Edit Job Type' && newType != '') {
        fetchContractJobSingleEdit()
      }
      fetchMerJobTypeData()
      fetchMerMeasurementUnitData()
      fetchMerPaymentTypeData()
      fetchMerLaborTypeData()

      numberedSteps.value = [
        ...(newType !== 'List Job Type' && newType !== 'Edit Job Type' && newType !== 'Add Job Type'
          ? [
              {
                title: 'PPS Data',
                subtitle: (newType === 'Edit' ? 'Edit' : 'Add') + ' PPS data',
              },
            ]
          : []),
        ...(newType === 'Add' || newType === 'List Job Type' || newType === 'Edit Job Type' || newType === 'Add Job Type'
          ? [
              {
                title: 'Job Type',
                subtitle:  (newType === 'Edit Job Type' ? 'Edit' : 'Add') +' job type',
              },
            ]
          : []),
      ];
      loadingBtn.value[0] = false;
  },
  { immediate: true }
)

watch(() => ppsOngoingData.ck_wl, (newValue) => {
  syncWorkLocation();
}, { immediate: true })

watch(() => props.isDisabled, (newValue) => {
  isDisabled.value = newValue;
})

watch(() => ppsOngoingData.wc, (newValue) => {
  syncWorkLocation();
}, { immediate: true })

watch(
  () => laborData.labor,
  (newLaborData) => {
    ppsOngoingData.labor_type = newLaborData.map((labor) => labor.type || null);
    ppsOngoingData.labor_qty = newLaborData.map((labor) => labor.qty || null);
  },
  { deep: true }
)

watch(
  () => ppsOngoingData.rows,
  (newCjtData) => {
    ppsOngoingData.cjt_type = newCjtData.map((cjt) => cjt.cjt_type || null);
    ppsOngoingData.cjt_qty = newCjtData.map((cjt) => cjt.cjt_qty || null);
    ppsOngoingData.total = newCjtData.map((cjt) => cjt.total || null);
  },
  { deep: true }
)

watch(
  () => props.rangeIncrement,
  (newRange) => {
    if (Array.isArray(newRange)) {
      ppsOngoingData.rows = newRange.map(() => ({
        cjt_type: null,
        cjt_qty: null,
        total: null
      }));
    }
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
    <VCard>
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
          <!-- PPS Form -->
          <VWindowItem v-if="typeDialog != 'List Job Type' && typeDialog != 'Edit Job Type' && typeDialog != 'Add Job Type'">
            <VForm
              ref="refPPSForm"
              @submit.prevent="validatePPSForm"
              lazy-validation
            >
            <VRow>
              <VCol col="12" md="6">
                <div class="card__text" v-if="isLoading">
                  <Skeletor height="20" width="80" class="mb-2" />
                  <div v-for="i in 10" :key="i">
                    <div style="margin-top: 35px;">
                      <Skeletor height="20" width="80" class="mb-2" />
                      <Skeletor height="40" pill />
                    </div>
                  </div>
                </div>
              </VCol>
              <VCol col="12" md="6">
                <div class="card__text" v-if="isLoading">
                  <div v-for="i in 10" :key="i">
                    <div style="margin-top: 35px;">
                      <Skeletor height="20" width="80" class="mb-3" />
                      <Skeletor height="40" pill />
                    </div>
                  </div>
                </div>
              </VCol>
            </VRow>
              <VRow>
                <VCol cols="12" v-if="!isLoading">
                  <h5>(*) Is required</h5>
                </VCol>
                <!-- Left Column -->
                <VCol cols="12" md="6" v-if="!isLoading">
                  <VCol>
                    <AppAutocomplete
                      placeholder="Select company"
                      label="Company*"
                      v-model="ppsOngoingData.company"
                      :items="dataCompany"
                      :rules="[requiredValidator]"
                      :item-title="'title'"
                      :item-value="'value'"
                      :error-messages="props.errors?.company"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppAutocomplete
                      placeholder="Select business unit"
                      label="Business Units*"
                      v-model="ppsOngoingData.bu"
                      :items="dataMerBU"
                      :rules="[requiredValidator]"
                      :item-title="'title'"
                      :item-value="'value'"
                      :error-messages="props.errors?.bu"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppAutocomplete
                      label="Cost Center* (multiple select)"
                      v-model="ppsOngoingData.cc"
                      :items="dataMerCC"
                      placeholder="Select cost center"
                      chips
                      multiple
                      closable-chips
                      :rules="[requiredValidator]"
                      :item-title="'title'"
                      :item-value="'value'"
                      :error-messages="props.errors?.cc"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppAutocomplete
                      label="Work Center (multiple select)"
                      v-model="ppsOngoingData.wc"
                      :items="dataMerWC"
                      :item-title="'title'"
                      :item-value="'value'"
                      :error-messages="props.errors?.wc"
                      placeholder="Select work center"
                      chips
                      multiple
                      closable-chips
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppAutocomplete
                      v-if="ppsOngoingData.ck_wl"
                      label="Work Location"
                      v-model="ppsOngoingData.work_location"
                      :items="dataMerWC"
                      :item-title="'title'"
                      :item-value="'value'"
                      :readonly="ppsOngoingData.ck_wl"
                      :error-messages="props.errors?.work_location"
                      placeholder="Select work location"
                      chips
                      multiple
                      :closable-chips="false"
                    />
                    <AppTextField
                      v-else
                      label="Work Location"
                      v-model="ppsOngoingData.work_location"
                      :error-messages="props.errors?.work_location"
                      placeholder="Type here..."
                      clearable
                    />
                    <VCheckbox
                      v-model="ppsOngoingData.ck_wl"
                      label="Same with Work Center"
                    />
                  </VCol>
                  <VCol>
                    <AppTextField
                      label="ID Project"
                      v-model="ppsOngoingData.id_project"
                      :error-messages="props.errors?.id_project"
                      placeholder="Type here..."
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppTextField
                      label="PPS No*"
                      v-model="ppsOngoingData.pps_no"
                      placeholder="Type here..."
                      :rules="[requiredValidator]"
                      :error-messages="props.errors?.pps_no"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppTextField
                    label="Old PPS No"
                    v-model="ppsOngoingData.old_pps_no"
                    placeholder="Type here..."
                    :error-messages="props.errors?.old_pps_no"
                    clearable
                    />
                  </VCol>
                  <VCol>
                    <VRadioGroup
                      v-model="ppsOngoingData.priority"
                      inline
                      label="Priority*"
                      :rules="[requiredValidator]"
                      :error-messages="props.errors?.priority"
                    >
                      <VRadio
                        label="Segera"
                        value="segera"
                        density="compact"
                      />
                      <VRadio
                        label="Tidak Segera"
                        value="tidak segera"
                        density="compact"
                      />
                    </VRadioGroup>
                  </VCol>
                  <VCol>
                    <label>Shift*</label>
                    <div class="demo-space-x">
                      <div v-for="(shift, indexShift) in allShiftData" :key="shift.shift_code">
                        <VCheckbox
                          v-model="ppsOngoingData.shift_checklist"
                          :label="shift.shift_description"
                          :value="shift.shift_code"
                          :error-messages="props.errors?.shift_checklist"
                        />
                        <VTooltip open-delay="150" location="top" activator="parent">
                          <span>At {{ shift.shift_jam }}</span>
                        </VTooltip>
                      </div>
                    </div>
                  </VCol>
                </VCol>
                <!-- Rigth Column -->
                <VCol cols="12" md="6" v-if="!isLoading">
                  <VCol>
                    <AppTextField
                      persistent-placeholder
                      label="Contact Person*"
                      v-model="ppsOngoingData.cp_name"
                      placeholder="Type here..."
                      :rules="[requiredValidator]"
                      :error-messages="props.errors?.cp_name"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppTextField
                      label="Dept-Position*"
                      v-model="ppsOngoingData.cp_dept"
                      placeholder="Type here..."
                      :rules="[requiredValidator]"
                      :error-messages="props.errors?.cp_dept"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppTextField
                      label="Ext No/Hp No*"
                      v-model="ppsOngoingData.cp_ext"
                      placeholder="Type here..."
                      :rules="[requiredValidator, integerValidator]"
                      :error-messages="props.errors?.cp_ext"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppTextField
                      :rules="[requiredValidator, emailValidator, lengthValidator(specifiedLength, 5)]"
                      label="Email*"
                      v-model="ppsOngoingData.cp_email"
                      placeholder="Type here..."
                      :error-messages="props.errors?.cp_email"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <label for="comment" class="text-body-2 text-high-emphasis mb-1">
                      Comment
                    </label>
                    <div class="border rounded px-3 py-1" id="comment">
                      <EditorContent :editor="editorComment" :error-messages="props.errors?.comment"/>
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
                  <VCol>
                    <AppDateTimePicker
                      label="Planning Time Duration*"
                      v-model="ppsOngoingData.duration"
                      placeholder="Select range date"
                      :config="{ mode: 'range' }"
                      :rules="[requiredValidator]"
                      :error-messages="props.errors?.duration"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppAutocomplete
                      placeholder="Select suggest vendor"
                      label="Suggest Vendor"
                      v-model="ppsOngoingData.suggest_vendor"
                      :items="dataMerVendor"
                      :item-title="'title'"
                      :item-value="'value'"
                      :error-messages="props.errors?.suggest_vendor"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <VFileInput
                      label="Upload File"
                      v-model="ppsOngoingData.file_attachment"
                      :error-messages="props.errors?.file_attachment"                      
                      accept="image/png, image/jpeg, application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                      placeholder="Pick a file"
                    />
                      <small>Accepted: .pdf,.docx,.doc,.jpg,.jpeg,.png,.xlsx MAX: 5MB</small>
                      <RouterLink v-if="isFileAttachment != null && isFileAttachment != ''"> 
                        <small class="" @click="openPathDialog(isFileAttachment)">
                        View data
                        </small>
                      </RouterLink>
                  </VCol>
                  <VCol>
                    <label for="target-estimate" class="text-body-2 text-high-emphasis mb-1">
                      Target Estimate
                    </label>
                    <div class="border rounded px-3 py-1" id="target-estimate">
                      <EditorContent :editor="editorTargetEstimate" :error-messages="props.errors?.con_comment_jobtarget"/>
                      <div
                        v-if="editorTargetEstimate"
                        class="d-flex justify-end flex-wrap gap-x-2"
                      >
                        <VIcon
                          icon="tabler-bold"
                          :color="editorTargetEstimate.isActive('bold') ? 'primary' : ''"
                          size="20"
                          @click="editorTargetEstimate.chain().focus().toggleBold().run()"
                        />

                        <VIcon
                          :color="editorTargetEstimate.isActive('underline') ? 'primary' : ''"
                          icon="tabler-underline"
                          size="20"
                          @click="editorTargetEstimate.commands.toggleUnderline()"
                        />

                        <VIcon
                          :color="editorTargetEstimate.isActive('italic') ? 'primary' : ''"
                          icon="tabler-italic"
                          size="20"
                          @click="editorTargetEstimate.chain().focus().toggleItalic().run()"
                        />

                        <VIcon
                          :color="editorTargetEstimate.isActive('bulletList') ? 'primary' : ''"
                          icon="tabler-list"
                          size="20"
                          @click="editorTargetEstimate.chain().focus().toggleBulletList().run()"
                        />

                        <VIcon
                          :color="editorTargetEstimate.isActive('orderedList') ? 'primary' : ''"
                          icon="tabler-list-numbers"
                          size="20"
                          @click="editorTargetEstimate.chain().focus().toggleOrderedList().run()"
                        />

                        <VIcon
                          icon="tabler-link"
                          size="20"
                          @click="setLinkTargetEstimate"
                        />
                      </div>
                    </div>
                  </VCol>
                </VCol>
              </VRow>
              <VCol cols="12">
                <VRow class="d-flex justify-end">
                  <div class="card__actions d-flex justify-end" v-if="isLoading">
                    <Skeletor width="96" height="36" class="me-4"/>
                    <Skeletor width="96" height="36" />
                  </div>
                  <div v-if="!isLoading">
                    <VBtn
                      class="me-4"
                      color="secondary"
                      variant="tonal"
                      @click="dialogModelValueUpdate"
                    >
                      Discard
                    </VBtn>
                    <VBtn 
                      type="submit"
                      :loading="loadingBtn[0]"
                      :disabled="loadingBtn[0]"
                    >
                    <span>{{ typeDialog == 'Edit' ? 'Update' : 'Save & Next Job Type' }}</span>
                      <VIcon v-if="typeDialog == 'Add'"
                        icon="tabler-arrow-right"
                        end
                        class="flip-in-rtl"
                      />
                    </VBtn>
                  </div>
                </VRow>
              </VCol>
            </VForm>
          </VWindowItem>
          <!-- Job Type Form -->
          <VWindowItem v-if="typeDialog != 'Edit' && typeDialog != 'Add'">
            <VForm ref="refJobTypeForm">
              <VRow>
                <VCol cols="12">
                  <div class="add-products-form">
                    <!-- Card-1 -->
                    <div class="mb-3">
                      <VCard
                        flat
                        border
                        class="d-flex flex-sm-row flex-column-reverse"
                      >
                        <div class="pa-6 flex-grow-1">
                          <VRow>
                            <VCol cols="12" md="4">
                              <AppAutocomplete
                                label="Job Type* (multiple select)"
                                v-model="ppsOngoingData.job_type"
                                :items="dataMerJobType"
                                placeholder="Select job type"
                                chips
                                multiple
                                closable-chips
                                :rules="[requiredValidator]"
                                :item-title="'title'"
                                :item-value="'value'"
                                :error-messages="props.errors?.job_type"
                                clearable
                              />
                            </VCol>
                            <VCol cols="12" md="4">
                              <AppTextarea
                                v-model="ppsOngoingData.job_desc"
                                label="Job Description*"
                                rows="2"
                                placeholder="Type here..."
                                :rules="[requiredValidator]"
                                :error-messages="props.errors?.job_desc"
                                auto-grow
                                clearable
                              />
                            </VCol>
                            <VCol cols="12" md="4">
                              <AppTextField
                                label="PIC*"
                                v-model="ppsOngoingData.pic"
                                placeholder="Type here..."
                                :rules="[requiredValidator]"
                                :error-messages="props.errors?.pic"
                                clearable
                              />
                            </VCol>
                            <VCol cols="12" md="4">
                              <AppAutocomplete
                                placeholder="Select payment type"
                                label="Payment Type*"
                                v-model="ppsOngoingData.payment_type"
                                :items="dataMerPaymentType"
                                :rules="[requiredValidator]"
                                :item-title="'title'"
                                :item-value="'value'"
                                :error-messages="props.errors?.payment_type"
                                clearable
                              />
                            </VCol>
                            <VCol cols="12" md="4">
                              <AppTextField
                                label="Total Job Target (Qty)*"
                                v-model="ppsOngoingData.total_job_target_qty"
                                type="number"
                                placeholder="Type here..."
                                :rules="[requiredValidator]"
                                :error-messages="props.errors?.total_job_target_qty"
                                @input="averageEvenly"
                                clearable
                              />
                            </VCol>
                            <VCol cols="12" md="4">
                              <AppAutocomplete
                                placeholder="Select UoM"
                                label="UoM*"
                                v-model="ppsOngoingData.uom"
                                :items="dataMerMeasurementUnit"
                                :rules="[requiredValidator]"
                                :item-title="'title'"
                                :item-value="'value'"
                                :error-messages="props.errors?.uom"
                                clearable
                              />
                            </VCol>
                          </VRow>
                        </div>
                      </VCard>
                    </div>
                    <!-- Card-2 -->
                    <div v-for="(date, index) in props.rangeIncrement">
                      <div v-if="props.rangeIncrement.length > 0 && ppsOngoingData.rows.length > 0 && ppsOngoingData.rows[index] && hasRequiredKeys(ppsOngoingData.rows[index])" :key="index" class="mb-5">
                        <VCard
                          flat
                          border
                          class="d-flex flex-sm-row flex-column-reverse"
                        >
                          <div class="pa-6 flex-grow-1">
                            <VRow>
                              <VCol cols="12" md="2">
                                <AppTextField
                                  :value="dayjs(date).format('YYYY-MM')"
                                  label="Year-Month"
                                  :readonly="true"
                                />
                              </VCol>
                              <VCol cols="12" md="4">
                                <AppAutocomplete
                                  placeholder="Select type"
                                  label="Type"
                                  v-model="ppsOngoingData.rows[index].cjt_type"
                                  :items="dataCJTType"
                                  :item-title="'title'"
                                  :item-value="'value'"
                                  :error-messages="props.errors?.cjt_type"
                                  :rules="[requiredValidator]"
                                  clearable
                                />
                              </VCol>
                              <VCol cols="12" md="3">
                                <AppTextField
                                  label="Increment"
                                  v-model="ppsOngoingData.rows[index].cjt_qty"
                                  type="number"
                                  placeholder="Type here..."
                                  :error-messages="props.errors?.cjt_qty"
                                  :rules="[requiredValidator]"
                                  @input="balanceCheck"
                                  clearable
                                />
                              </VCol>
                              <VCol cols="12" md="3">
                                <AppTextField
                                  label="Total"
                                  v-model="ppsOngoingData.rows[index].total"
                                  :error-messages="props.errors?.total"
                                  :readonly="true"
                                />
                              </VCol>
                            </VRow>
                          </div>
                        </VCard>
                      </div>
                    </div>
                    <!-- Card-3 Repeat form-->
                    <div
                      v-for="(labor, index) in laborData.labor"
                      :key="labor.id"
                      class="mb-4"
                    >
                      <JobType
                        :id="labor.id"
                        :dataLaborLength="Number(laborData?.labor.length)"
                        :data="labor"
                        :errors="props.errors"
                        :dataMerLaborType="dataMerLaborType"
                        @InptLabor="updateInptLabor(index, $event)"
                        @remove-labor="removeLabor"
                      />
                    </div>

                    <VBtn
                      size="small"
                      variant="outlined"
                      prepend-icon="tabler-plus"
                      @click="addItem"
                    >
                      Add Item
                    </VBtn>
                  </div>
                </VCol>
                <!-- Action Button -->
                <VCol cols="12">
                  <div class="d-flex flex-wrap gap-4 justify-end mt-8">
                    <VBtn
                      color="success"
                      type="submit"
                      :loading="loadingBtn[0]"
                      :disabled="isDisabled"
                      @click="validateJobTypeForm('Save')"
                    >
                      <VIcon v-if="typeDialog == 'Add'"
                        icon="tabler-device-floppy"
                        start
                        class="flip-in-rtl"
                      />
                      <span>{{ typeDialog == 'Edit' || typeDialog == 'Edit Job Type' ? 'Update' : 'Save' }}</span>
                    </VBtn>
                    
                    <VBtn v-if="typeDialog != 'Edit' && typeDialog != 'Edit Job Type'"
                      color="success"
                      variant="outlined"
                      type="submit"
                      :loading="loadingBtnSecond[0]"
                      :disabled="isDisabled"
                      @click="validateJobTypeForm('Continue')"
                    >
                      <VIcon v-if="typeDialog == 'Add'"
                        icon="tabler-location"
                        start
                        type="submit"
                        class="flip-in-rtl"
                      />
                      Save & New Job Type
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
  <PPSOngoingViewPathDialog
    v-model:isDialogViewPathVisible="isPPSOngoingDialogViewPathVisible"
    :path-data="pathData"
  />
</template>

<style lang="scss" scoped>
  .drop-zone {
    border: 2px dashed rgba(var(--v-theme-on-surface), 0.12);
    border-radius: 6px;
  }
</style>

<style lang="scss">
.inventory-card {
  .v-tabs.v-tabs-pill {
    .v-slide-group-item--active.v-tab--selected.text-primary {
      h6 {
        color: #fff !important;
      }
    }
  }

  .v-radio-group,
  .v-checkbox {
    .v-selection-control {
      align-items: start !important;
    }

    .v-label.custom-input {
      border: none !important;
    }
  }
}

.ProseMirror {
  p {
    margin-block-end: 0;
  }

  padding: 0.5rem;
  outline: none;

  p.is-editor-empty:first-child::before {
    block-size: 0;
    color: #adb5bd;
    content: attr(data-placeholder);
    float: inline-start;
    pointer-events: none;
  }
}
</style>
