<script setup>
import PPSCards from '@/views/apps/pps/PPSCards.vue';
import PPSOrdersContent from '@/views/apps/pps/PPSOrdersContent.vue';
import { ref } from 'vue';

const totalNotPriorityCount = ref(0);
const totalPriorityCount = ref(0);
const totalNotPriorityCompletedCount = ref(0);
const totalPriorityCompletedCount = ref(0);
const isActiveTab = ref(0);

const updateActiveTab = (count) => {
  isActiveTab.value = count;
};
// On Going
const updateTotalNotPriorityCount = (count) => {
  totalNotPriorityCount.value = count;
};
const updateTotalPriorityCount = (count) => {
  totalPriorityCount.value = count;
};
// Completed
const updateTotalNotPriorityCompletedCount = (count) => {
  totalNotPriorityCompletedCount.value = count;
};
const updateTotalPriorityCompletedCount = (count) => {
  totalPriorityCompletedCount.value = count;
};
</script>

<template>
  <VRow class="match-height">
    <VCol cols="12">
      <h4 class="text-h4 mb-1">
        PPS List
      </h4>
    </VCol>
    <VCol cols="12">
      <PPSCards :isActiveTab="isActiveTab" :totalNotPriorityCount="totalNotPriorityCount" :totalPriorityCount="totalPriorityCount" :totalNotPriorityCompletedCount="totalNotPriorityCompletedCount" :totalPriorityCompletedCount="totalPriorityCompletedCount"/>
    </VCol>
    <VCol cols="12">
      <PPSOrdersContent @isTabActive="updateActiveTab" @updateTotalNotPriority="updateTotalNotPriorityCount" @updateTotalPriority="updateTotalPriorityCount" @updateTotalNotPriorityCompleted="updateTotalNotPriorityCompletedCount" @updateTotalPriorityCompleted="updateTotalPriorityCompletedCount"/>
    </VCol>
  </VRow>
</template>
