<script setup>
import RenewalCards from '@/views/apps/renewal/RenewalCards.vue';
import RenewalList from '@/views/apps/renewal/RenewalList.vue';
const totalNotPriorityCount = ref(0);
const totalPriorityCount = ref(0);

const updateTotalNotPriorityCount = (count) => {
  totalNotPriorityCount.value = count;
};
const updateTotalPriorityCount = (count) => {
  totalPriorityCount.value = count;
};
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h4 class="text-h4 mb-1">
        Renewal List
      </h4>
    </VCol>
    <VCol cols="12">
      <RenewalCards :totalNotPriorityCount="totalNotPriorityCount" :totalPriorityCount="totalPriorityCount" />
    </VCol>
    <VCol cols="12">
      <RenewalList @updateTotalNotPriority="updateTotalNotPriorityCount" @updateTotalPriority="updateTotalPriorityCount"/>
    </VCol>
  </VRow>
</template>
