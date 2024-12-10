<script setup>
const props = defineProps({
  isDialogViewPathVisible: {
    type: Boolean,
    required: true,
  },
  pathData: {
    type: String,
    required: true
  }
});

const emit = defineEmits([
  'update:isDialogViewPathVisible',
]);

const dialogModelValueViewPathUpdate = () => {
  emit('update:isDialogViewPathVisible', false);
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 600"
    :model-value="props.isDialogViewPathVisible"
    @update:model-value="dialogModelValueViewPathUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueViewPathUpdate" />
    <VCard class="pa-2 pa-sm-10">
      <VCardText class="pt-6">
        <VRow>
          <VCol class="d-flex align-center flex-column" cols="12">
            <div class="w-50">
              <VImg :src="'/storage/' + props.pathData" />
            </div>
            <div class="mt-5">
              <VBtn
                icon
                variant="tonal"
                color="primary"
                class="me-3"
              >
                <VIcon icon="tabler-download" />
                <VTooltip open-delay="200" location="top" activator="parent">
                  <span>Download</span>
                </VTooltip>
              </VBtn>
              <VBtn
                icon
                variant="tonal"
                color="error"
              >
                <VIcon icon="tabler-trash" />
                <VTooltip open-delay="200" location="top" activator="parent">
                  <span>Delete</span>
                </VTooltip>
              </VBtn>
            </div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VDialog>
</template>
