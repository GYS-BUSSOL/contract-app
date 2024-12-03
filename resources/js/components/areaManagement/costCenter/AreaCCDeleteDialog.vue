<script setup>
const props = defineProps({
  isDialogDeleteVisible: {
    type: Boolean,
    required: true,
  },
  areaCCId: {
    type: Number,
    required: true
  },
  fetchTrigger: {
    type: Number,
    default: 0
  }
});
const loadingBtnDelete = ref([])

const emit = defineEmits([
  'update:isDialogDeleteVisible',
  'idDeleted'
]);

watch(
  [() => props.areaCCId, () => props.fetchTrigger],
    () => {
      loadingBtnDelete.value[0] = false;
  },
  { immediate: true }
);

const deleteAreaCC = async id => {
  try {
      loadingBtnDelete.value[0] = true
      emit("idDeleted", Number(id));
    } catch (err) {
      loadingBtnDelete.value[0] = false
    }
}

const dialogModelValueDeleteUpdate = () => {
  emit('update:isDialogDeleteVisible', false);
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 600"
    :model-value="props.isDialogDeleteVisible"
    @update:model-value="dialogModelValueDeleteUpdate"
  >
    <VCard class="pa-2 pa-sm-10" v-model:loading="loadingBtnDelete[0]">
      <VCardItem class="text-center">
        <VCardTitle>
          <h4 class="text-h4 mb-2">
            Delete Area Cost Center
          </h4>
        </VCardTitle>
      </VCardItem>

      <VCardText class="pt-6">
        <VRow>
          <VCol cols="12">
            <p class="text-body-1 mb-0">
              Are you sure delete this data ? please klik <strong>Delete</strong> button to confirm
            </p>
          </VCol>
          <VCol
            cols="12"
            class="text-center"
          >
            <VBtn
              class="me-4"
              color="error"
              type="submit"
              :loading="loadingBtnDelete[0]"
              :disabled="loadingBtnDelete[0]"
              @click="deleteAreaCC(props.areaCCId)"
            >
              Delete
            </VBtn>
            <VBtn
              color="secondary"
              variant="outlined"
              @click="$emit('update:isDialogDeleteVisible', false)"
            >
              Cancel
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VDialog>
</template>
