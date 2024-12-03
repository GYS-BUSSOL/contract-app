<script setup>
import ApprovalCompletedList from '@/views/apps/approval/lvl1/completed/ApprovalCompletedList.vue';
import ApprovalOnGoingList from '@/views/apps/approval/lvl1/onGoing/ApprovalOnGoingList.vue';
const emit = defineEmits(['isTabActive','updateTotalNotPriority','updateTotalPriority','updateTotalNotExpired','updateTotalExpired'])

const currentTab = ref(0)

const tabsData = [
  'On Going Request',
  'Completed Request'
]

const handleTotalNotPriorityUpdate = (count) => {
  emit('updateTotalNotPriority', count);
};

const handleTotalPriorityUpdate = (count) => {
  emit('updateTotalPriority', count);
};  

const handleTotalNotExpiredUpdate = (count) => {
  emit('updateTotalNotExpired', count);
};

const handleTotalExpiredUpdate = (count) => {
  emit('updateTotalExpired', count);
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
          <ApprovalOnGoingList @updateTotalNotPriority="handleTotalNotPriorityUpdate" @updateTotalPriority="handleTotalPriorityUpdate"/>
        </VWindowItem>

        <VWindowItem>
          <ApprovalCompletedList @updateTotalNotExpired="handleTotalNotExpiredUpdate" @updateTotalExpired="handleTotalExpiredUpdate"/>
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
