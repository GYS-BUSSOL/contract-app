<script setup>
import { getListMerBU } from '@db/apps/mer/db';
import { ref, watch } from 'vue';
import 'vue-skeletor/dist/vue-skeletor.css';
import { VForm } from 'vuetify/components/VForm';
const token = useCookie('accessToken').value

const emit = defineEmits([
  'update:isDialogVisible',
  'BUData',
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
  yearData: {
    type: String,
    required: true
  },
  BuId: {
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
const BuId = computed(() => props.BuId)
const yearData = computed(() => props.yearData)
const loadingBtn = ref([])
const dataBudget = reactive({
  year: null,
  number: null,
  description: null,
  bgt_bu_head: null,
  bgt_amount: null
});
const dataMerBU = ref([])

const dialogModelValueUpdate = () => {
  dataBudget.year = null;
  dataBudget.number = null;
  dataBudget.description = null;
  dataBudget.bgt_bu_head = null;
  dataBudget.bgt_amount = null;
  // Form reset
  refVForm.value?.reset();
  refVForm.value?.resetValidation();
  loadingBtn.value[0] = false;
  emit('update:isDialogVisible', false)
  emit('updateTypeDialog')
}

const fetchMerBUData = async () => {
  try {
    const response = await getListMerBU();
    if (response.status === 200) {

      const rows = response.data.rows || [];
      dataMerBU.value = rows.map((row) => ({
        title: (row.number != null && row.number != '' ? row.number + ' - ' : '') + row.description,
        value: row.id,
      }));
    } else {
      throw new Error("Failed to fetch mer business unit data");
    }
  } catch (error) {
    throw new Error("Failed to fetch mer business unit data");
  }
}

const yearOptions = () => {
  const start = 2018;
  const end = new Date().getFullYear() + 1;
  const options = [];
  for (let year = start; year <= end; year++) {
    options.push(year.toString());
  }
  return options;
}

const fetchEdit = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/budget-bu/edit/${BuId.value}/${yearData.value}`, {
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
      dataBudget.number = dataResult.number;
      dataBudget.description = dataResult.description;
      dataBudget.bgt_bu_head = dataResult.bgt_bu_head;
      dataBudget.bgt_amount = dataResult.bgt_amount;
      dataBudget.year = dataResult.bgt_year;
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

const onSubmit = async () => {
  refVForm.value?.validate().then(({ valid }) => {
    try {
      if (valid) {
        loadingBtn.value[0] = true
        let payload = {};
        const mode = props.typeDialog;
        if(mode == 'Add') {
          payload = {
            year: dataBudget.year
          }
        } else if('Edit') {
          payload = {
            year: dataBudget.year,
            description: dataBudget.description,
            bgt_bu_head: dataBudget.bgt_bu_head,
            bgt_amount: dataBudget.bgt_amount,
          }
        }
        emit("BUData", { mode, formData: payload, dialogUpdate: dialogModelValueUpdate });
      } else {
        loadingBtn.value[0] = false
      }
    } catch (err) {
      loadingBtn.value[0] = false
    }
  })
}

watch(
  [() => BuId.value, () => typeDialog.value, () => props.fetchTrigger],
    ([newId,newType]) => {
      if (newType === "Edit" && newId) {
        fetchEdit()
      } else if (newType === "Add") {
        dataBudget.year = null;
        fetchMerBUData()
      }
      isLoading.value = false;
      loadingBtn.value[0] = false;
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
                      {{ typeDialog == 'Edit' ? 'Edit' : 'Create New' }} Budget BU
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
                  </VRow>
                  <VRow v-if="typeDialog == 'Add'">
                    <VCol cols="12" md="4" v-if="!isLoading">
                      <AppAutocomplete
                        placeholder="Select year"
                        label="Years*"
                        v-model="dataBudget.year"
                        :rules="[requiredValidator]"
                        :error-messages="props.errors?.year"
                        :items="yearOptions()"
                        clearable
                      />
                    </VCol>
                    <VCol cols="12" v-if="!isLoading">
                      <VTable class="border collapse mt-3" v-if="!isLoading && dataMerBU" :height="dataMerBU.length <= 5 ? 'max-content' : '300'" fixed-header>
                        <thead>
                          <tr>
                            <th scope="col">
                              No
                            </th>
                            <th scope="col">
                              Business Unit
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          <template v-if="dataMerBU && dataMerBU.length > 0">
                            <tr v-for="(bu, buIndex) in dataMerBU">
                              <td>{{ buIndex + 1 }}</td>
                              <td>{{ bu.title }}</td>
                            </tr>
                          </template>
                          <tr v-else>
                            <td colspan="2" class="text-center">
                              Data is empty
                            </td>
                          </tr>
                        </tbody>
                      </VTable>
                    </VCol>
                  </VRow>
                  <VRow v-if="typeDialog == 'Edit'">
                    <VCol cols="12" v-if="!isLoading">
                      <VTable class="border collapse mt-3" fixed-header>
                        <thead>
                          <tr>
                            <th scope="col">
                              Business Unit
                            </th>
                            <th scope="col">
                              Business Unit Head
                            </th>
                            <th scope="col">
                              Amount
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>
                              {{ dataBudget.number + ' - ' + dataBudget.description }}
                            </td>
                            <td>
                              <AppTextField
                                v-model="dataBudget.bgt_bu_head"
                                :error-messages="props.errors?.bgt_bu_head"
                                :rules="[requiredValidator]"
                                placeholder="Type here..."
                                clearable
                              />
                            </td>
                            <td>
                              <AppTextField
                                v-model="dataBudget.bgt_amount"
                                :error-messages="props.errors?.bgt_amount"
                                :rules="[requiredValidator]"
                                placeholder="Type here..."
                                type="number"
                                clearable
                              />
                            </td>
                          </tr>
                        </tbody>
                      </VTable>
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
                          color="secondary"
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
