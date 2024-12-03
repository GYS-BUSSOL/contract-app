<script setup>
import { computed, defineProps } from 'vue';

const props = defineProps({
  totalPriorityCount: {
    type: Number,
    required: true,
  },
  totalNotPriorityCount: {
    type: Number,
    required: true,
  },
  totalPriorityCompletedCount: {
    type: Number,
    required: true,
  },
  totalNotPriorityCompletedCount: {
    type: Number,
    required: true,
  },
  isActiveTab: {
    type: Number,
    required: true,
  },
})
    
const widgetPPS = computed(() => [
  {
    icon: 'tabler-users',
    color: 'info',
    title: 'Total Data',
    value: props.isActiveTab == 0 ? (props.totalPriorityCount + props.totalNotPriorityCount) : (props.totalPriorityCompletedCount + props.totalNotPriorityCompletedCount),
    isHover: true,
  },
  {
    icon: 'tabler-user-exclamation',
    color: 'error',
    title: 'Total Priority "Tidak Segera"',
    value: props.isActiveTab == 0 ? (props.totalNotPriorityCount) : (props.totalNotPriorityCompletedCount),
    isHover: true,
  },
  {
    icon: 'tabler-user-check',
    color: 'success',
    title: 'Total Priority "Segera"',
    value: props.isActiveTab == 0 ? (props.totalPriorityCount) : (props.totalPriorityCompletedCount),
    isHover: true,
  },
]);
</script>

<template>
  <VRow>
    <VCol
      v-for="(data, index) in widgetPPS"
      :key="index"
      cols="12"
      md="4"
    >
      <div>
        <VCard
          class="logistics-card-statistics cursor-pointer"
          :style="data.isHover ? `border-block-end-color: rgb(var(--v-theme-${data.color}))` : `border-block-end-color: rgba(var(--v-theme-${data.color}),0.38)`"
          @mouseenter="data.isHover = true"
          @mouseleave="data.isHover = false"
        >
          <VCardText>
            <div class="d-flex align-center gap-x-4 mb-1">
              <VAvatar
                variant="tonal"
                :color="data.color"
                rounded
              >
                <VIcon
                  :icon="data.icon"
                  size="28"
                />
              </VAvatar>
              <h4 class="text-h4">
                {{ data.value }}
              </h4>
            </div>
            <div class="text-body-1 mb-1">
              {{ data.title }}
            </div>
          </VCardText>
        </VCard>
      </div>
    </VCol>
  </VRow>
</template>

<style lang="scss" scoped>
@use "@core-scss/base/mixins" as mixins;

.logistics-card-statistics {
  border-block-end-style: solid;
  border-block-end-width: 2px;

  &:hover {
    border-block-end-width: 3px;
    margin-block-end: -1px;

    @include mixins.elevation(8);

    transition: all 0.1s ease-out;
  }
}

.skin--bordered {
  .logistics-card-statistics {
    border-block-end-width: 2px;

    &:hover {
      border-block-end-width: 3px;
      margin-block-end: -2px;
      transition: all 0.1s ease-out;
    }
  }
}
</style>
