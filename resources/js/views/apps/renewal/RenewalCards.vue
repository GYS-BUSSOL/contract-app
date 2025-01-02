<script setup>
  import { computed, defineProps } from 'vue';
  
  const props = defineProps({
    totalPriorityCount: {
      type: Number,
      required: true,
      default: 0
    },
    totalNotPriorityCount: {
      type: Number,
      required: true,
      default: 0
    },
  })

  const widgetData = computed(() => [
    {
      title: 'Total Data',
      value: props.totalPriorityCount + props.totalNotPriorityCount,
      desc: 'Total data',
      icon: 'tabler-file',
      iconColor: 'info',
    },
    {
      title: 'Priority "Tidak Segera"',
      value: props.totalNotPriorityCount,
      desc: 'Total Priority "Tidak Segera"',
      icon: 'tabler-file-report',
      iconColor: 'success',
    },
    {
      title: 'Priority "Segera"',
      value: props.totalPriorityCount,
      desc: 'Total Priority "Segera"',
      icon: 'tabler-file-alert',
      iconColor: 'warning',
    },
  ])
</script>
<template>
  <!-- Widgets -->
  <div class="d-flex mb-6">
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
