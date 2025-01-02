<script setup>
import { computed, defineProps } from 'vue';

const props = defineProps({
  totalNotPriorityCount: {
    type: Number,
    required: true,
    default: 0
  },
  totalPriorityCount: {
    type: Number,
    required: true,
    default: 0
  },
  totalExpiredCount: {
    type: Number,
    required: true,
    default: 0
  },
  totalNotExpiredCount: {
    type: Number,
    required: true,
    default: 0
  },
  isActiveTab: {
    type: Number,
    required: true,
  },
})
    
const widgetApprovalOnGoing = computed(() => [
{
    icon: 'tabler-file',
    color: 'info',
    title: 'Total Data',
    value: props.totalPriorityCount + props.totalNotPriorityCount,
    isHover: true,
  },
  {
    icon: 'tabler-file-report',
    color: 'success',
    title: 'Total Priority "Tidak Segera"',
    value: props.totalNotPriorityCount,
    isHover: true,
  },
  {
    icon: 'tabler-file-alert',
    color: 'warning',
    title: 'Total Priority "Segera"',
    value: props.totalPriorityCount,
    isHover: true,
  },
]);

const widgetApprovalCompleted = computed(() => [
  {
    icon: 'tabler-file',
    color: 'info',
    title: 'Total Data',
    value: props.totalExpiredCount + props.totalNotExpiredCount,
    isHover: true,
  },
  {
    icon: 'tabler-file-x',
    color: 'error',
    title: 'Total Expired Data',
    value: props.totalExpiredCount,
    isHover: true,
  },
  {
    icon: 'tabler-file-check',
    color: 'success',
    title: 'Total Not Expired Data',
    value: props.totalNotExpiredCount,
    isHover: true,
  },
]);

</script>

<template>
  <VRow>
    <VCol
      v-for="(data, index) in (props.isActiveTab == 0 ? widgetApprovalOnGoing : widgetApprovalCompleted)"
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
