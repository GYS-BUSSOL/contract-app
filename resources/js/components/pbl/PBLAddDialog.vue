<script setup>
import { integerValidator } from '@/@core/utils/validators';
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

// Editor Target Estimate
const editorTargetEstimate = useEditor({
  content: '',
  extensions: [
    StarterKit,
    Placeholder.configure({ placeholder: 'Enter a target estimate...' }),
    Underline,
    Link.configure({ openOnClick: true }),
  ],
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

const resetForm = () => {
  refVForm.value?.reset()
  emit('update:isDialogVisible', false)
}

const isTaxChargeToProduct = ref(false)
const inlineRadio = ref('readio-1')
const selected = ref([''])
const dateRange = ref('')
const itemsCC = ['000 - Power Plant', '1000 - Head of Commercial & Logistic', '1010 -  Sales & Marketing']
const itemsWC = ['4HM - 4 Hi Miil', '6GM - CVC 6 High Reserving Cold Mill', 'AGL -  Galvanizing DWI']
const rulesFile = [fileList => !fileList || !fileList.length || fileList[0].size < 1000000 || 'File size should be less than 1 MB!']
const refVForm = ref()
const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'submit',
  'update:isDialogVisible',
])

const userData = ref(structuredClone(toRaw(props.userData)))

watch(() => props, () => {
  userData.value = structuredClone(toRaw(props.userData))
})

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 1200"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />
    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <div>
          <div class="d-flex flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
            <div class="d-flex flex-column justify-center">
              <div class="d-flex gap-x-4 align-center">
                <!-- <IconBtn size="small" @click="$router.go(-1)">
                  <VIcon
                    :icon="'tabler-chevron-left'"
                    class="text-high-emphasis"
                  />
                </IconBtn> -->
                <div>
                  <div class="text-h4 font-weight-medium">
                    Create New SPK
                  </div>
                </div>
              </div>
            </div>
          </div>

          <VRow>
            <VCol cols="12">
              <VForm
                ref="refVForm"
                lazy-validation
                @submit.prevent
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
                        :items="['PT Nusantara Baja Profil']"
                        :rules="[requiredValidator]"
                        clearable
                      />
                    </VCol>
                    <VCol>
                      <AppAutocomplete
                        placeholder="Select business unit"
                        label="Business Units*"
                        :items="['CRM - COLD ROLLING MILL']"
                        :rules="[requiredValidator]"
                        clearable
                      />
                    </VCol>
                    <VCol>
                      <AppAutocomplete
                        label="Cost Center* (multiple select)"
                        :items="itemsCC"
                        placeholder="Select cost center"
                        chips
                        multiple
                        closable-chips
                        :rules="[requiredValidator]"
                        clearable
                      />
                    </VCol>
                    <VCol>
                      <AppAutocomplete
                        label="Work Center (multiple select)"
                        :items="itemsWC"
                        placeholder="Select work center"
                        chips
                        multiple
                        closable-chips
                        clearable
                      />
                    </VCol>
                    <VCol>
                      <AppTextField
                        label="Work Location"
                        placeholder="Type here..."
                        clearable
                      />
                      <VCheckbox
                        v-model="isTaxChargeToProduct"
                        label="Same with Work Center"
                      />
                    </VCol>
                    <VCol>
                      <AppTextField
                        label="ID Project"
                        placeholder="Type here..."
                        clearable
                      />
                    </VCol>
                    <VCol>
                      <AppTextField
                        label="PPS No*"
                        placeholder="Type here..."
                        :rules="[requiredValidator]"
                        clearable
                      />
                    </VCol>
                    <VCol>
                      <AppTextField
                      label="Old PPS No"
                      placeholder="Type here..."
                      clearable
                      />
                    </VCol>
                    <VCol>
                      <VRadioGroup
                        v-model="inlineRadio"
                        inline
                        label="Priority"
                      >
                        <VRadio
                          label="Segera"
                          value="radio-1"
                          density="compact"
                        />
                        <VRadio
                          label="Tidak Segera"
                          value="radio-2"
                          density="compact"
                        />
                      </VRadioGroup>
                    </VCol>
                    <VCol>
                      <label>Shift*</label>
                      <div class="demo-space-x">
                        <div>
                          <VCheckbox
                            v-model="selected"
                            label="Non Shift"
                            value="Non Shift"
                          />
                          <VTooltip open-delay="200" location="top" activator="parent">
                            <span>At 08.00-17.00</span>
                          </VTooltip>
                        </div>
                        <div>
                          <VCheckbox
                            v-model="selected"
                            label="Shift 1"
                            value="Shift 1"
                          />
                          <VTooltip open-delay="300" location="top" activator="parent">
                            <span>At 07.00-15.00</span>
                          </VTooltip>
                        </div>
                        <div>
                          <VCheckbox
                            v-model="selected"
                            label="Shift 2"
                            value="Shift 2"
                          />
                          <VTooltip open-delay="300" location="top" activator="parent">
                            <span>At 15.00-23.00</span>
                          </VTooltip>
                        </div>
                        <div>
                          <VCheckbox
                            v-model="selected"
                            label="Shift 3"
                            value="Shift 3"
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
                        placeholder="Type here..."
                        :rules="[requiredValidator, integerValidator]"
                        clearable
                      />
                    </VCol>
                    <VCol>
                      <AppTextField
                        label="Dept-Position*"
                        placeholder="Type here..."
                        :rules="[requiredValidator]"
                        clearable
                      />
                    </VCol>
                    <VCol>
                      <AppTextField
                        label="Ext No/Hp No*"
                        placeholder="Type here..."
                        :rules="[requiredValidator, integerValidator]"
                        clearable
                      />
                    </VCol>
                    <VCol>
                      <AppTextField
                        :rules="[requiredValidator, emailValidator, lengthValidator(specifiedLength, 5)]"
                        label="Email*"
                        placeholder="Type here..."
                        clearable
                      />
                    </VCol>
                    <VCol>
                      <label for="comment" class="text-body-2 text-high-emphasis mb-1">
                        Comment
                      </label>
                      <div class="border rounded px-3 py-1" id="comment">
                        <EditorContent :editor="editorComment" />
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
                        v-model="dateRange"
                        label="Planning Time Duration*"
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
                      :items="['PT Gunung Raja Paksi','PT TRI KARYA HARMEGI','PT JASA PRIMA MANDIRI GEMILANG']"
                      clearable
                      />
                    </VCol>
                    <VCol>
                      <VFileInput
                        :rules="rulesFile"
                        label="Upload File"
                        accept="image/png, image/jpeg, image/bmp"
                        placeholder="Pick an avatar"
                        />
                    </VCol>
                    <VCol>
                      <label for="target-estimate" class="text-body-2 text-high-emphasis mb-1">
                        Target Estimate
                      </label>
                      <div class="border rounded px-3 py-1" id="target-estimate">
                        <EditorContent :editor="editorTargetEstimate" />
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
                <VCol cols="12" class="mt-5">
                  <VRow
                  class="d-flex justify-end"
                  >
                    <VBtn
                      type="submit"
                      class="me-4"
                      @click="refVForm?.validate()"
                    >
                      Create
                    </VBtn>
                    <VBtn
                      color="error"
                      variant="tonal"
                      @click="resetForm"
                    >
                      Discard
                    </VBtn>
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
