<script setup>
import { computed, defineProps } from 'vue';

const props = defineProps({
  totalExpiredCount: {
    type: Number,
    required: true,
    default: 0
  },
  totalNotExpiredCount: {
    type: Number,
    required: true,
    default: 0
  }
})

const widgetData = computed(() => [
  {
    title: 'Total Data',
    value: props.totalExpiredCount + props.totalNotExpiredCount,
    desc: 'Total reviewer',
    icon: 'tabler-file',
    iconColor: 'info',
  },
  {
    title: 'Expired Data',
    value: props.totalExpiredCount,
    desc: 'Total expired data',
    icon: 'tabler-file-x',
    iconColor: 'error',
  },
  {
    title: 'Not Expired Yet Data',
    value: props.totalNotExpiredCount,
    desc: 'Total not expired data',
    icon: 'tabler-file-check',
    iconColor: 'success',
  },
])
</script>
<template>
  <!-- Widgets -->
  <div class="d-flex">
      <VRow>
        <template
          v-for="(data, id) in widgetData"
          :key="id"
        >
          <VCol
            cols="12"
            md="4"
            sm="6"
          >
            <VCard>
              <VCardText>
                <div class="d-flex justify-space-between">
                  <div class="d-flex flex-column gap-y-1">
                    <div class="text-body-1 text-high-emphasis">
                      {{ data.title }}
                    </div>
                    <div class="d-flex gap-x-2 align-center">
                      <h4 class="text-h4">
                        {{ data.value }}
                      </h4>
                    </div>
                    <div class="text-sm">
                      {{ data.desc }}
                    </div>
                  </div>
                  <VAvatar
                    :color="data.iconColor"
                    variant="tonal"
                    rounded
                    size="42"
                  >
                    <VIcon
                      :icon="data.icon"
                      size="26"
                    />
                  </VAvatar>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </template>
      </VRow>
    </div>
</template>
