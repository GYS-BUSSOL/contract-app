<script setup>
import { integerValidator } from '@/@core/utils/validators';
import JobType from '@/views/apps/pps/onGoing/JobType.vue';
import { getListCompany } from '@db/apps/company/db';
import { getListMerBU, getListMerCC, getListMerVendor, getListMerWC } from '@db/apps/mer/db';
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
  'submit',
  'update:isDialogVisible',
  'PPSData',
  'remove'
]);

const props = defineProps({
  // userData: {
  //   type: Object,
  //   required: false,
  //   default: () => ({
  //     id: 0,
  //     fullName: '',
  //     company: '',
  //     role: '',
  //     username: '',
  //     country: '',
  //     contact: '',
  //     email: '',
  //     currentPlan: '',
  //     status: '',
  //     avatar: '',
  //     taskDone: null,
  //     projectDone: null,
  //     taxId: '',
  //     language: '',
  //   }),
  // },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
});

const invoiceData = ref({
  purchasedProducts: [{
    title: '',
    cost: 0,
    hours: 0,
    description: '',
  }]
})

const jobTypeData = ref({
  dataJobType: [{
    title: '',
  }]
})

const isSameWorkLocation = ref(false)
const RdoPriority = ref('segera')
const InptCompany = ref(null)
const dataCompany = ref([])
const dataMerBU = ref([])
const dataMerCC = ref([])
const dataMerWC = ref([])
const dataMerVendor = ref([])
const InptBusinessUnit = ref(null)
const InptCostCenter = ref([])
const InptWorkCenter = ref([])
const InptWorkLocation = ref(null)
const InptIDProject = ref(null)
const InptPPSNo = ref(null)
const InptPPSOldNo = ref(null)
const CkShift = ref([])
const InptContactPerson = ref(null)
const InptDeptPosition = ref(null)
const InptNoHP = ref(null)
const InptEmail = ref(null)
const InptComment = ref('')
const DateRangePlanningTime = ref('')
const SelctSuggestVendor = ref(null)
const fileAssigment = ref(null)
const InptTargetEstimate = ref('')
const rulesFile = [fileList => !fileList || !fileList.length || fileList[0].size < 2000000 || 'File size should be less than 1 MB!']
const refVForm = ref()
const refPPSForm = ref()
const refJobTypeForm = ref()
const currentStep = ref(0)
const isCurrentStepValid = ref(true)
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
    InptTargetEstimate.value = editor.getHTML()
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
    InptComment.value = editor.getHTML()
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

const closeDialogPPS = () => {
  emit('update:isDialogVisible', false)
  nextTick(() => {
    refVForm.value?.reset()
    refVForm.value?.resetValidation()
  })
}

const onSubmit = () => {
  refVForm.value?.validate().then(({ valid }) => {
    if (valid) {
      const formData = new FormData();

      formData.append('company', InptCompany.value);
      formData.append('bu', InptBusinessUnit.value);
      formData.append('cc', InptCostCenter.value);
      formData.append('wc', InptWorkCenter.value);
      formData.append('work_location', InptWorkLocation.value);
      formData.append('id_project', InptIDProject.value);
      formData.append('pps_no', InptPPSNo.value);
      formData.append('old_pps_no', InptPPSOldNo.value);
      formData.append('priority', RdoPriority.value);
      formData.append('shift_checklist', JSON.stringify(CkShift.value));
      formData.append('cp_name', InptContactPerson.value);
      formData.append('cp_dept', InptDeptPosition.value);
      formData.append('cp_ext', InptNoHP.value);
      formData.append('cp_email', InptEmail.value);
      formData.append('comment', InptComment.value);
      formData.append('duration', JSON.stringify(DateRangePlanningTime.value));
      formData.append('suggest_vendor', SelctSuggestVendor.value);
      formData.append('file_attachment', fileAssigment.value);
      formData.append('con_comment_jobtarget', InptTargetEstimate.value);

      emit('PPSData', formData);
      // emit('update:isDialogVisible', false)
      nextTick(() => {
        // refVForm.value?.reset()
        // refVForm.value?.resetValidation()
      })
    }
  })
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

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
};

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
};

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
};

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
};

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
      console.error('Failed to fetch vendor data');
    }
    
  } catch (error) {
    console.error('Error fetching vendor data',error);
  }
};

watch(isSameWorkLocation, (newValue) => {
  syncWorkLocation();
});

watch(InptWorkCenter, (newValue) => {
  syncWorkLocation();
});

const syncWorkLocation = () => {
  if (isSameWorkLocation.value) {
    InptWorkLocation.value = InptWorkCenter.value.length > 0 ? InptWorkCenter.value : '';
  } else {
    InptWorkLocation.value = null;
  }
};

const validatePPSForm = () => {
  refPPSForm.value?.validate().then(valid => {
    if (valid.valid) {
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
    } else {
      isCurrentStepValid.value = false
    }
  })
}

const addItem = () => {
  invoiceData.value?.purchasedProducts.push(
    {
      title: 'App Design',
      cost: 24,
      hours: 1,
      description: 'Designed UI kit & app pages.',
    }
  )
}

const addJobType = () => {
  jobTypeData.value?.dataJobType.push(
    {
      title: 'App Design',
    }
  )
}

const removeProduct = id => {
  invoiceData.value?.purchasedProducts.splice(id, 1)
}

onMounted(() => {
  fetchCompanyData();
  fetchMerBUData();
  fetchMerCCData();
  fetchMerWCData();
  fetchMerVendorData();
});
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 1200"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />
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
                <VCol cols="12" md="6">
                  <VCol>
                    <AppAutocomplete
                      placeholder="Select company"
                      label="Company*"
                      v-model="InptCompany"
                      :items="dataCompany"
                      :rules="[requiredValidator]"
                      :item-title="'title'"
                      :item-value="'value'"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppAutocomplete
                      placeholder="Select business unit"
                      label="Business Units*"
                      v-model="InptBusinessUnit"
                      :items="dataMerBU"
                      :rules="[requiredValidator]"
                      :item-title="'title'"
                      :item-value="'value'"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppAutocomplete
                      label="Cost Center* (multiple select)"
                      v-model="InptCostCenter"
                      :items="dataMerCC"
                      placeholder="Select cost center"
                      chips
                      multiple
                      closable-chips
                      :rules="[requiredValidator]"
                      :item-title="'title'"
                      :item-value="'value'"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppAutocomplete
                      label="Work Center (multiple select)"
                      v-model="InptWorkCenter"
                      :items="dataMerWC"
                      :item-title="'title'"
                      :item-value="'value'"
                      placeholder="Select work center"
                      chips
                      multiple
                      closable-chips
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppAutocomplete
                      v-if="isSameWorkLocation"
                      label="Work Location"
                      v-model="InptWorkLocation"
                      :items="dataMerWC"
                      :item-title="'title'"
                      :item-value="'value'"
                      :readonly="isSameWorkLocation"
                      placeholder="Select work location"
                      chips
                      multiple
                      :closable-chips="false"
                    />
                    
                    <AppTextField
                      v-else
                      label="Work Location"
                      v-model="InptWorkLocation"
                      placeholder="Type here..."
                      clearable
                    />
                    <VCheckbox
                      v-model="isSameWorkLocation"
                      label="Same with Work Center"
                    />
                  </VCol>
                  <VCol>
                    <AppTextField
                      label="ID Project"
                      v-model="InptIDProject"
                      placeholder="Type here..."
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppTextField
                      label="PPS No*"
                      v-model="InptPPSNo"
                      placeholder="Type here..."
                      :rules="[requiredValidator]"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppTextField
                    label="Old PPS No"
                    v-model="InptPPSOldNo"
                    placeholder="Type here..."
                    clearable
                    />
                  </VCol>
                  <VCol>
                    <VRadioGroup
                      v-model="RdoPriority"
                      inline
                      label="Priority*"
                      :rules="[requiredValidator]"
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
                          v-model="CkShift"
                          label="Non Shift"
                          value="4"
                        />
                        <VTooltip open-delay="200" location="top" activator="parent">
                          <span>At 08.00-17.00</span>
                        </VTooltip>
                      </div>
                      <div>
                        <VCheckbox
                          v-model="CkShift"
                          label="Shift 1"
                          value="1"
                        />
                        <VTooltip open-delay="300" location="top" activator="parent">
                          <span>At 07.00-15.00</span>
                        </VTooltip>
                      </div>
                      <div>
                        <VCheckbox
                          v-model="CkShift"
                          label="Shift 2"
                          value="2"
                        />
                        <VTooltip open-delay="300" location="top" activator="parent">
                          <span>At 15.00-23.00</span>
                        </VTooltip>
                      </div>
                      <div>
                        <VCheckbox
                          v-model="CkShift"
                          label="Shift 3"
                          value="3"
                        />
                        <VTooltip open-delay="300" location="top" activator="parent">
                          <span>At 23.00-07.00</span>
                        </VTooltip>
                      </div>
                    </div>
                  </VCol>
                </VCol>

                <!-- Rigth Column -->
                <VCol cols="12" md="6">
                  <VCol>
                    <AppTextField
                      persistent-placeholder
                      label="Contact Person*"
                      v-model="InptContactPerson"
                      placeholder="Type here..."
                      :rules="[requiredValidator]"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppTextField
                      label="Dept-Position*"
                      v-model="InptDeptPosition"
                      placeholder="Type here..."
                      :rules="[requiredValidator]"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppTextField
                      label="Ext No/Hp No*"
                      v-model="InptNoHP"
                      placeholder="Type here..."
                      :rules="[requiredValidator, integerValidator]"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppTextField
                      :rules="[requiredValidator, emailValidator, lengthValidator(specifiedLength, 5)]"
                      label="Email*"
                      v-model="InptEmail"
                      placeholder="Type here..."
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <label for="comment" class="text-body-2 text-high-emphasis mb-1">
                      Comment
                    </label>
                    <div class="border rounded px-3 py-1" id="comment">
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
                  <VCol>
                    <AppDateTimePicker
                      label="Planning Time Duration*"
                      v-model="DateRangePlanningTime"
                      placeholder="Select date"
                      :config="{ mode: 'range' }"
                      :rules="[requiredValidator]"
                      clearable
                    />
                  </VCol>
                  <VCol>
                    <AppAutocomplete
                    placeholder="Select suggest vendor"
                    label="Suggest Vendor"
                    v-model="SelctSuggestVendor"
                    :items="dataMerVendor"
                    clearable
                    />
                  </VCol>
                  <VCol>
                    <VFileInput
                      :rules="rulesFile"
                      label="Upload File"
                      v-model="fileAssigment"
                      accept="image/png, image/jpeg, image/bmp"
                      placeholder="Pick a file"
                      />
                  </VCol>
                  <VCol>
                    <label for="target-estimate" class="text-body-2 text-high-emphasis mb-1">
                      Target Estimate
                    </label>
                    <div class="border rounded px-3 py-1" id="target-estimate">
                      <EditorContent :editor="editorTargetEstimate"/>
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
                    Next
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
          <VWindowItem>
            <VForm ref="refJobTypeForm" @submit.prevent="validateJobTypeForm">
              <VRow>
                <div
                  v-for="(type, index) in jobTypeData.dataJobType"
                  :key="type.title"
                  class="mb-4"
                >
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
                                  v-model="InptCostCenter"
                                  :items="dataMerCC"
                                  placeholder="Select cost center"
                                  chips
                                  multiple
                                  closable-chips
                                  :rules="[requiredValidator]"
                                  :item-title="'title'"
                                  :item-value="'value'"
                                  clearable
                                />
                              </VCol>
                              <VCol cols="12" md="4">
                                <label for="comment" class="text-body-2 text-high-emphasis mb-1">
                                  Job Description*
                                </label>
                                <div class="border rounded px-3 py-1" id="comment">
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
                              <VCol cols="12" md="4">
                                <AppTextField
                                  label="PIC*"
                                  v-model="InptIDProject"
                                  placeholder="Type here..."
                                  :rules="[requiredValidator]"
                                  clearable
                                />
                              </VCol>
                              <VCol cols="12" md="4">
                                <AppAutocomplete
                                  placeholder="Select payment type"
                                  label="Payment Type*"
                                  v-model="InptCompany"
                                  :items="dataCompany"
                                  :rules="[requiredValidator]"
                                  :item-title="'title'"
                                  :item-value="'value'"
                                  clearable
                                />
                              </VCol>
                              <VCol cols="12" md="4">
                                <AppTextField
                                  label="Job Target (Qty)*"
                                  v-model="InptNoHP"
                                  type="number"
                                  placeholder="Type here..."
                                  :rules="[requiredValidator, integerValidator]"
                                  clearable
                                />
                              </VCol>
                              <VCol cols="12" md="4">
                                <AppAutocomplete
                                  placeholder="Select UOM"
                                  label="UOM*"
                                  v-model="InptCompany"
                                  :items="dataCompany"
                                  :rules="[requiredValidator]"
                                  :item-title="'title'"
                                  :item-value="'value'"
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
                              <VCol cols="12" md="4">
                                <AppTextField
                                  label="Year - Month"
                                  :items="'2024-11'"
                                  placeholder="Type here..."
                                  :readonly="true"
                                />
                              </VCol>
                              <VCol cols="12" md="4">
                                <AppAutocomplete
                                  placeholder="Select type"
                                  label="Type"
                                  v-model="InptCompany"
                                  :items="dataCompany"
                                  :item-title="'title'"
                                  :item-value="'value'"
                                  clearable
                                />
                              </VCol>
                              <VCol cols="12" md="4">
                                <AppTextField
                                  label="Increment"
                                  v-model="InptNoHP"
                                  type="number"
                                  placeholder="Type here..."
                                  :rules="[integerValidator]"
                                  clearable
                                />
                              </VCol>
                            </VRow>
                          </div>
                        </VCard>
                      </div>
                      <!-- Card-3 -->
                      <div
                        v-for="(product, index) in invoiceData.purchasedProducts"
                        :key="product.title"
                        class="mb-4"
                      >
                        <JobType
                          :id="index"
                          :data="product"
                          @remove-product="removeProduct"
                        />
                      </div>

                      <VBtn
                        size="small"
                        prepend-icon="tabler-plus"
                        @click="addItem"
                      >
                        Add Item
                      </VBtn>
                    </div>
                  </VCol>
                </div>
                <VBtn
                  size="small"
                  prepend-icon="tabler-plus"
                  @click="addJobType"
                >
                  Add New Job Type
                </VBtn>
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
