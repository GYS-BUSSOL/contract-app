<!-- eslint-disable vue/no-mutating-props -->
<script setup>
const props = defineProps({
  id: {
    type: Number,
    required: true,
  },
  data: {
    type: Object,
    required: true,
    default: () => ({
      id: Date.now(),
      type: null,
      qty: null,
    }),
  },
  dataLaborLength: {
    type: [
      String,
      Number
    ],
    required: true
  },
  dataMerLaborType: {
    type: Object,
    required: true
  },
  errors: {
    type: Object,
    required: false
  }
})

const lengthDataLabor = computed(() => props.dataLaborLength || 0)
const localData = reactive({ ...props.data });

const emit = defineEmits([
  'removeLabor',
  'InptLabor'
])

watch(localData, (newData) => {
  emit('InptLabor', { id: props.id, ...newData })
}, { deep: true });

watch(props.dataLaborLength, (newVal) => {
  lengthDataLabor.value = newVal
}, { immediate: true })

const removeLabor = () => {
  emit('removeLabor', props.id)
}

</script>

<template>
  <VCard
    flat
    border
    class="d-flex flex-sm-row flex-column-reverse"
  >
    <!-- Left Form -->
    <div class="pa-6 flex-grow-1">
      <VRow class="me-10">
        <VCol
          cols="12"
          md="6"
        >
          <h6 class="text-h6">
            Labor Type
          </h6>
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <h6 class="text-h6 ps-2">
            Labor Qty
          </h6>
        </VCol>
      </VRow>
      <VRow>
        <VCol
          cols="12"
          md="6"
        >
        <AppAutocomplete
          placeholder="Select type"
          v-model="localData.type"
          :items="props.dataMerLaborType"
          :item-title="'title'"
          :item-value="'value'"
          :error-messages="props.errors?.type"
          clearable
        />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
        <AppTextField
          v-model="localData.qty"
          type="number"
          placeholder="Qty"
          :rules="[integerValidator]"
          :error-messages="props.errors?.qty"
          clearable
        />
        </VCol>
      </VRow>
    </div>

    <!-- Item Actions -->
    <div
      class="d-flex flex-column align-end item-actions"
      :class="$vuetify.display.smAndUp ? 'border-s' : 'border-b' "
    >
      <IconBtn v-if="lengthDataLabor > 1"
        size="36"
        @click="removeLabor"
      >
        <VIcon
          :size="24"
          color="error"
          icon="tabler-trash"
        />
      </IconBtn>
    </div>
  </VCard>
</template>
