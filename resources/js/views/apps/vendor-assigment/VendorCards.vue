<script setup>
  import { computed, defineProps } from 'vue';
  const props = defineProps({
    totalPriorityCount: Number,
    totalNotPriorityCount: Number
  })

  const widgetData = computed(() => [
    {
      title: 'Data Total',
      value: props.totalPriorityCount + props.totalNotPriorityCount,
      desc: 'Total vendor assignment',
      icon: 'tabler-users',
      iconColor: 'secondary',
    },
    {
      title: 'Priority "Segera"',
      value: props.totalPriorityCount,
      desc: 'Total priority',
      icon: 'tabler-user-exclamation',
      iconColor: 'error',
    },
    {
      title: 'Priority "Tidak Segera"',
      value: props.totalNotPriorityCount,
      desc: 'Total non priority',
      icon: 'tabler-user-check',
      iconColor: 'success',
    },
  ]);
</script>
<template>
  <!-- Widgets -->
  <div class="d-flex mb-6">
      <VRow>
        <template
          v-for="(data, index) in widgetData"
          :key="index"
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
