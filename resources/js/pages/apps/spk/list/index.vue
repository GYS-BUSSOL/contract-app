<script setup>
import SPKList from '@/views/apps/spk/list/SPKList.vue';
import SPKListCards from '@/views/apps/spk/list/SPKListCards.vue';

const totalNotExpiredCount = ref(0);
const totalExpiredCount = ref(0);

const updateTotalNotExpiredCount = (count) => {
  totalNotExpiredCount.value = count;
};
const updateTotalExpiredCount = (count) => {
  totalExpiredCount.value = count;
};
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h4 class="text-h4 mb-1">
        SPK List
      </h4>
    </VCol>
    <VCol cols="12">
      <SPKListCards :totalNotExpiredCount="totalNotExpiredCount" :totalExpiredCount="totalExpiredCount"/>
    </VCol>
    <VCol cols="12">
      <SPKList @updateTotalNotExpired="updateTotalNotExpiredCount" @updateTotalExpired="updateTotalExpiredCount"/>
    </VCol>
  </VRow>
</template>
