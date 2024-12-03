<script setup>
import ApprovalLvl2Cards from '@/views/apps/approval/lvl2/ApprovalLvl2Cards.vue';
import ApprovalLvl2OrdersContent from '@/views/apps/approval/lvl2/ApprovalLvl2OrdersContent.vue';

const totalNotPriorityCount = ref(0);
const totalPriorityCount = ref(0);
const totalNotExpiredCount = ref(0);
const totalExpiredCount = ref(0);
const isActiveTab = ref(0);

const updateTotalNotPriorityCount = (count) => {
  totalNotPriorityCount.value = count;
};
const updateTotalPriorityCount = (count) => {
  totalPriorityCount.value = count;
};

const updateTotalNotExpiredCount = (count) => {
  totalNotExpiredCount.value = count;
};

const updateTotalExpiredCount = (count) => {
  totalExpiredCount.value = count;
};

const updateActiveTab = (count) => {
  isActiveTab.value = count;
};
</script>

<template>
  <VRow class="match-height">
    <VCol cols="12">
      <h4 class="text-h4 mb-1">
        Approval Level 2 List
      </h4>
    </VCol>
    <VCol cols="12">
      <ApprovalLvl2Cards :totalNotPriorityCount="totalNotPriorityCount" :totalPriorityCount="totalPriorityCount" :totalNotExpiredCount="totalNotExpiredCount" :totalExpiredCount="totalExpiredCount" :isActiveTab="isActiveTab"/>
    </VCol>
    <VCol cols="12">
      <ApprovalLvl2OrdersContent @updateTotalNotPriority="updateTotalNotPriorityCount" @updateTotalPriority="updateTotalPriorityCount" @updateTotalNotExpired="updateTotalNotExpiredCount" @updateTotalExpired="updateTotalExpiredCount" @isTabActive="updateActiveTab"/>
    </VCol>

  </VRow>
</template>
