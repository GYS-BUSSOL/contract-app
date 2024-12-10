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
              <VImg v-if="props.pathData != null && props.pathData != ''" :src="'/storage/' + props.pathData" />
              <div v-if="props.pathData == null || props.pathData == ''" class="text-center">
                <VImg :src="'/images/notFoundPath.png'" />
                <h2>File Not Found</h2>
              </div>
            </div>
            <div class="mt-5" v-if="props.pathData != null && props.pathData != ''">
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
            </div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VDialog>
</template>
