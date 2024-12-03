<script setup>
import { getListMerUser } from '@db/apps/mer/db';
import { ref, watch } from 'vue';
import 'vue-skeletor/dist/vue-skeletor.css';
import { VForm } from 'vuetify/components/VForm';

const emit = defineEmits([
  'update:isDialogVisible',
  'SignatureTypeData',
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
  signatureTypeId: {
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
})

const isLoading = ref(true)
const refVForm = ref()
const typeDialog = computed(() => props.typeDialog)
const signatureTypeId = computed(() => props.signatureTypeId)
const loadingBtn = ref([])
const signatureTypeData = reactive({
  st_desc: "",
  st_user: []
});
const dataMerUser = ref([])

const dialogModelValueUpdate = () => {
  signatureTypeData.st_desc = "";
  signatureTypeData.st_user = [];
  refVForm.value?.reset()
  refVForm.value?.resetValidation()
  loadingBtn.value[0] = false;
  emit('update:isDialogVisible', false)
}

const fetchMerUserData = async () => {
  try {
    const response = await getListMerUser('approval');
    if (response.status === 200) {

      const rows = response.data.rows || [];
      dataMerUser.value = rows.map((row) => ({
        title: row.usr_display_name,
        value: row.usr_id,
      }));
    } else {
      console.error('Failed to fetch mer business units data');
    }
    
  } catch (error) {
    console.error('Error fetching mer business units data');
  }
}

const fetchSignatureTypeEdit = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/configurations/signature-type/edit/${signatureTypeId.value}`, {
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
      signatureTypeData.st_desc = dataResult.st_desc;
      signatureTypeData.st_user = dataResult.st_user.split(",")
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

watch(
  [() => signatureTypeId.value, () => typeDialog.value, () => props.fetchTrigger],
    ([newId,newType]) => {
      if (newType === "Edit" && newId) {
        fetchSignatureTypeEdit();
      } else if (newType === "Add") {
        isLoading.value = false;
      }
      fetchMerUserData()
      loadingBtn.value[0] = false;
  },
  { immediate: true }
);

const onSubmit = async () => {
  refVForm.value?.validate().then(({ valid }) => {
    try {
      if (valid) {
        loadingBtn.value[0] = true
        const mode = props.typeDialog;
        emit("SignatureTypeData", { mode, formData: { ...signatureTypeData }, dialogUpdate: dialogModelValueUpdate });
      } else {
        loadingBtn.value[0] = false
      }
    } catch (err) {
      loadingBtn.value[0] = false
    }
  })
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 1200"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate" />
    <VCard class="pa-sm-10 pa-2" v-model:loading="isLoading">
      <VCardText>
          <div>
            <div class="d-flex flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
              <div class="d-flex flex-column justify-center">
                <div class="d-flex gap-x-4 align-center">
                  <div>
                    <div class="card__actions d-flex justify-end flex-column" v-if="isLoading">
                      <Skeletor width="150" height="36" class="me-4" pill/>
                      <Skeletor width="85" height="26" class="mt-5" pill/>
                    </div>
                    <div class="text-h4 font-weight-medium" v-if="!isLoading">
                      {{ typeDialog == 'Edit' ? 'Edit' : 'Create New' }} Signature Type
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card__text mb-5" v-if="isLoading">
              <VRow>
                <VCol cols="6" v-for="i in 1" :key="i">
                  <Skeletor height="40" pill class="mt-5"/>
                </VCol>
              </VRow>
            </div>
            <VRow>
              <VCol cols="12">
                <VForm
                  ref="refVForm"
                  lazy-validation
                  @submit.prevent="onSubmit"
                >
                  <VRow>
                    <VCol cols="12" v-if="!isLoading">
                      <h5>(*) Is required</h5>
                    </VCol>
                    <VCol cols="12" v-if="!isLoading">
                      <VRow>
                        <VCol cols="12" md="6">
                          <AppTextField
                            v-model="signatureTypeData.st_desc"
                            persistent-placeholder
                            label="Type*"
                            placeholder="Type here..."
                            :rules="[requiredValidator]"
                            :error-messages="props.errors?.st_desc"
                            required
                          />
                        </VCol>
                        <VCol cols="12" md="6">
                        <AppAutocomplete
                          label="Person* (multiple select)"
                          v-model="signatureTypeData.st_user"
                          :items="dataMerUser"
                          :item-title="'title'"
                          :item-value="'value'"
                          :rules="[requiredValidator]"
                          :error-messages="props.errors?.st_user"
                          placeholder="Select multiple person"
                          chips
                          multiple
                          closable-chips
                          clearable
                        />
                      </VCol>
                      </VRow>
                    </VCol>
                  </VRow>
                  <VCol cols="12" class="mt-5">
                    <VRow class="d-flex justify-end">
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
                        >
                          <span>{{ typeDialog == 'Edit' ? 'Update' : 'Create' }}</span>
                        </VBtn>
                        <VBtn
                          color="error"
                          variant="tonal"
                          @click="dialogModelValueUpdate"
                        >
                          Discard
                        </VBtn>
                      </div>
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
