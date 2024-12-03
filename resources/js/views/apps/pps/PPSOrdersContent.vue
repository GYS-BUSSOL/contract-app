<script setup>
import PPSCompletedList from '@/views/apps/pps/completed/PPSCompletedList.vue';
import PPSOnGoingList from '@/views/apps/pps/onGoing/PPSOnGoingList.vue';
const emit = defineEmits(['isTabActive','updateTotalNotPriority','updateTotalPriority','updateTotalNotPriorityCompleted','updateTotalPriorityCompleted'])

const currentTab = ref(0)

const tabsData = [
  'On Going Request',
  'Completed Request'
]
// On Going
const handleTotalNotPriorityUpdate = (count) => {
  emit('updateTotalNotPriority', count);
};

const handleTotalPriorityUpdate = (count) => {
  emit('updateTotalPriority', count);
};
// Completed
const handleTotalNotPriorityUpdateCompleted = (count) => {
  emit('updateTotalNotPriorityCompleted', count);
};

const handleTotalPriorityUpdateCompleted = (count) => {
  emit('updateTotalPriorityCompleted', count);
};

  watch([currentTab],(isActiveTab) => {
    emit('isTabActive', isActiveTab);
}, { immediate: true })
</script>

<template>
  <VCard class="country-order-card">
    <VTabs
      v-model="currentTab"
      grow
      class="disable-tab-transition"
    >
      <VTab
        v-for="(tab, index) in tabsData"
        :key="index"
      >
        {{ tab }}
      </VTab>
    </VTabs>

    <VCardText>
      <VWindow v-model="currentTab">
        <VWindowItem>
          <PPSOnGoingList @updateTotalNotPriority="handleTotalNotPriorityUpdate" @updateTotalPriority="handleTotalPriorityUpdate"/>
        </VWindowItem>

        <VWindowItem>
          <PPSCompletedList @updateTotalNotPriority="handleTotalNotPriorityUpdateCompleted" @updateTotalPriority="handleTotalPriorityUpdateCompleted"/>
        </VWindowItem>
      </VWindow>
    </VCardText>
  </VCard>
</template>

<style lang="scss">
.country-order-card {
  .v-timeline .v-timeline-divider__dot .v-timeline-divider__inner-dot {
    box-shadow: none !important;
  }
}
</style>
