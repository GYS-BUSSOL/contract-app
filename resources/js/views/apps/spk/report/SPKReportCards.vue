<script setup>
import { computed, defineProps } from 'vue';

const props = defineProps({
  totalActiveCount: {
    type: Number,
    required: true,
    default: 0
  },
  totalNotActiveCount: {
    type: Number,
    required: true,
    default: 0
  }
})

const widgetData = computed(() => [
  {
    title: 'Data Total',
    value: props.totalActiveCount + props.totalNotActiveCount,
    desc: 'Total SPK report',
    icon: 'tabler-report',
    iconColor: 'info',
  },
  {
    title: 'SPK Active',
    value: props.totalActiveCount,
    desc: 'Total active data',
    icon: 'tabler-notes',
    iconColor: 'success',
  },
  {
    title: 'SPK Not Active',
    value: props.totalNotActiveCount,
    desc: 'Total not active data',
    icon: 'tabler-notes-off',
    iconColor: 'error',
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
