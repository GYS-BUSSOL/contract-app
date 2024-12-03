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
import { ref } from 'vue';
import { VForm } from 'vuetify/components/VForm';

const emit = defineEmits([
  'update:isDialogVisible',
  'PPSData',
  'isSnackbarResponse',
  'isSnackbarResponseAlertColor',
  'errorMessages',
  'errors'
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
  ppsongoingId: {
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
});

const laborData = reactive({
  labor: [
    {id: Date.now(), type: null, qty: null},
  ]
})

const dataCompany = ref([])
const dataMerBU = ref([])
const dataMerCC = ref([])
const dataMerWC = ref([])
const dataMerVendor = ref([])
const dataMerJobType = ref([])
const dataMerMeasurementUnit = ref([])
const dataMerPaymentType = ref([])
const dataMerLaborType = ref([])
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
const isLoading = ref(true)
const refPPSForm = ref()
const refJobTypeForm = ref()
const typeDialog = computed(() => props.typeDialog)
const ppsongoingId = computed(() => props.ppsongoingId)
const loadingBtn = ref([])
const currentStep = ref(0)
const isCurrentStepValid = ref(true)
const ppsOngoingData = reactive({
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
  total: null
})
const numberedSteps = [
  {
    title: 'PPS Data',
    subtitle: 'Add PPS data',
  },
  {
    title: 'Job Type',
    subtitle: 'Add job type',
  },
]

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
  // Job Type form
  ppsOngoingData.job_type = null;
  ppsOngoingData.job_desc = null;
  ppsOngoingData.pic = null;
  ppsOngoingData.payment_type = null;
  ppsOngoingData.total_job_target_qty = null;
  ppsOngoingData.uom = null;
  ppsOngoingData.cjt_type = null;
  ppsOngoingData.cjt_qty = null;
  ppsOngoingData.total = null;
  ppsOngoingData.labor_type = null;
  ppsOngoingData.labor_qty = null;
  // Form all reset
  refPPSForm.value?.reset()
  refPPSForm.value?.resetValidation()
  refJobTypeForm.value?.reset()
  refJobTypeForm.value?.resetValidation()
  loadingBtn.value[0] = false;
  emit('update:isDialogVisible', false)
}

const updateInptLabor = (index, updatedData) => {
  laborData.labor[index] = updatedData;
}

watch(
  () => laborData.labor,
  (newLaborData) => {
    ppsOngoingData.labor_type = newLaborData.map((labor) => labor.type || null);
    ppsOngoingData.labor_qty = newLaborData.map((labor) => labor.qty || null);
  },
  { deep: true }
);


const fetchCompanyData = async () => {
  try {
    const response = await getListCompany();
    if (response.status === 200) {
      const rows = response.data.rows || [];
      dataCompany.value = rows.map((row) => ({
        title: row.company_name,
        value: row.id,
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
        value: row.id,
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
        value: row.id,
      }));
    } else {
      console.error('Failed to fetch mer cost center data');
    }
    
  } catch (error) {
    console.error('Error fetching mer cost center data',error);
  }
}

const fetchMerWCData = async () => {
  try {
    const response = await getListMerWC();
    if (response.status === 200) {
      const rows = response.data.rows || [];
      dataMerWC.value = rows.map((row) => ({
        title: row.number + ' - ' + row.description,
        value: row.id,
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

watch(ppsOngoingData.ck_wl, (newValue) => {
  syncWorkLocation();
});

watch(ppsOngoingData.wc, (newValue) => {
  syncWorkLocation();
});

const syncWorkLocation = () => {
  if (ppsOngoingData.ck_wl) {
    ppsOngoingData.work_location = ppsOngoingData.wc.length > 0 ? ppsOngoingData.wc : '';
  } else {
    ppsOngoingData.work_location = null;
  }
};

const validatePPSForm = () => {
  refPPSForm.value?.validate().then(valid => {
    if (valid.valid) {
      onSubmitPPS()
      currentStep.value++
      isCurrentStepValid.value = true
    } else {
      isCurrentStepValid.value = false
    }
  })
}

const validateJobTypeForm = () => {
  refJobTypeForm.value?.validate().then(valid => {
    if (valid.valid) {
      isCurrentStepValid.value = true
      onSubmitJobType()
    } else {
      isCurrentStepValid.value = false
    }
  })
}

const addItem = () => {
  laborData.labor.push({ id: Date.now(), type: null, qty: null})
}

const removeLabor = (index) => {
  laborData.labor.splice(index, 1)
}

const fetchPPSOngodingEdit = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/pps-ongoing/edit/${userhrId.value}`, {
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
      userHRData.Username = dataResult.usr_name;
      userHRData.UserDisplay = dataResult.usr_display_name;
      userHRData.NoTlp = dataResult.usr_no_tlp;
      userHRData.Access = dataResult.usr_access;
      userHRData.BU = dataResult.bu_id.split(",")
        .map((id) => parseInt(id.trim(), 10));
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
    emit("PPSData", { mode, formData: {... formDataToObject}, dialogUpdate: dialogModelValueUpdate });
  } catch (err) {
    loadingBtn.value[0] = false
  }
}

const onSubmitJobType = () => {
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
    loadingBtn.value[0] = true
    const mode = props.typeDialog;
    emit("JobTypeData", { mode, formData: {... formDataToObject}, dialogUpdate: dialogModelValueUpdate });
  } catch (err) {
    loadingBtn.value[0] = false
  }
}

watch(
  [() => ppsongoingId.value, () => typeDialog.value, () => props.fetchTrigger],
    ([newId,newType]) => {
      if (newType === "Edit" && newId) {
        fetchPPSOngodingEdit();
      } else if (newType === "Add") {
        isLoading.value = false;
      }
      fetchCompanyData()
      fetchMerBUData()
      fetchMerCCData()
      fetchMerWCData()
      fetchMerVendorData()
      fetchMerJobTypeData()
      fetchMerMeasurementUnitData()
      fetchMerPaymentTypeData()
      fetchMerLaborTypeData()
      loadingBtn.value[0] = false;
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
          <VWindowItem>
            <VForm
              ref="refPPSForm"
              @submit.prevent="validatePPSForm"
              lazy-validation
            >
              <VRow>
                <VCol cols="12">
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
                      <div>
                        <VCheckbox
                          v-model="ppsOngoingData.shift_checklist"
                          label="Non Shift"
                          value="4"
                          :error-messages="props.errors?.shift_checklist"
                        />
                        <VTooltip open-delay="200" location="top" activator="parent">
                          <span>At 08.00-17.00</span>
                        </VTooltip>
                      </div>
                      <div>
                        <VCheckbox
                          v-model="ppsOngoingData.shift_checklist"
                          label="Shift 1"
                          value="1"
                          :error-messages="props.errors?.shift_checklist"
                        />
                        <VTooltip open-delay="300" location="top" activator="parent">
                          <span>At 07.00-15.00</span>
                        </VTooltip>
                      </div>
                      <div>
                        <VCheckbox
                          v-model="ppsOngoingData.shift_checklist"
                          label="Shift 2"
                          value="2"
                          :error-messages="props.errors?.shift_checklist"
                        />
                        <VTooltip open-delay="300" location="top" activator="parent">
                          <span>At 15.00-23.00</span>
                        </VTooltip>
                      </div>
                      <div>
                        <VCheckbox
                          v-model="ppsOngoingData.shift_checklist"
                          label="Shift 3"
                          value="3"
                          :error-messages="props.errors?.shift_checklist"
                        />
                        <VTooltip open-delay="300" location="top" activator="parent">
                          <span>At 23.00-07.00</span>
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
                    :error-messages="props.errors?.suggest_vendor"
                    clearable
                    />
                  </VCol>
                  <VCol>
                    <VFileInput
                      label="Upload File"
                      v-model="ppsOngoingData.file_attachment"
                      :error-messages="props.errors?.file_attachment"
                      accept="image/png, image/jpeg, image/bmp"
                      placeholder="Pick a file"
                      />
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
                <div class="d-flex flex-wrap gap-4 justify-end mt-8">
                  <VBtn type="submit">
                    Save & Next Job Type
                    <VIcon
                      icon="tabler-arrow-right"
                      end
                      class="flip-in-rtl"
                    />
                  </VBtn>
                </div>
              </VCol>
            </VForm>
          </VWindowItem>
          <!-- Job Type Form -->
          <VWindowItem>
            <VForm ref="refJobTypeForm" @submit.prevent="validateJobTypeForm">
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
                                :rules="[requiredValidator, integerValidator]"
                                :error-messages="props.errors?.total_job_target_qty"
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
                    <div class="mb-5">
                      <VCard
                        flat
                        border
                        class="d-flex flex-sm-row flex-column-reverse"
                      >
                        <div class="pa-6 flex-grow-1">
                          <VRow>
                            <VCol cols="12" md="2">
                              <AppTextField
                                label="Year - Month"
                                :readonly="true"
                              />
                            </VCol>
                            <VCol cols="12" md="4">
                              <AppAutocomplete
                                placeholder="Select type"
                                label="Type"
                                v-model="ppsOngoingData.cjt_type"
                                :items="dataCJTType"
                                :item-title="'title'"
                                :item-value="'value'"
                                :error-messages="props.errors?.cjt_type"
                                clearable
                              />
                            </VCol>
                            <VCol cols="12" md="3">
                              <AppTextField
                                label="Increment"
                                v-model="ppsOngoingData.cjt_qty"
                                type="number"
                                placeholder="Type here..."
                                :rules="[integerValidator]"
                                :error-messages="props.errors?.cjt_qty"
                                clearable
                              />
                            </VCol>
                            <VCol cols="12" md="3">
                              <AppTextField
                                label="Total"
                                v-model="ppsOngoingData.total"
                                :error-messages="props.errors?.total"
                                :readonly="true"
                              />
                            </VCol>
                          </VRow>
                        </div>
                      </VCard>
                    </div>
                    <!-- Card-3 Repeat form-->
                    <div
                      v-for="(labor, index) in laborData.labor"
                      :key="labor.id"
                      class="mb-4"
                    >
                      <JobType
                        :id="index"
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
                  <div class="d-flex flex-wrap gap-4 justify-sm-space-between justify-center mt-8">
                    <VBtn
                      color="secondary"
                      variant="tonal"
                      @click="currentStep--"
                    >
                      <VIcon
                        icon="tabler-arrow-left"
                        start
                        class="flip-in-rtl"
                      />
                      Previous
                    </VBtn>

                    <VBtn
                      color="success"
                      type="submit"
                    >
                      submit
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
