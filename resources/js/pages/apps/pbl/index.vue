<script setup>
import PBLCards from '@/views/apps/pbl/PBLCards.vue';
import PBLList from '@/views/apps/pbl/PBLList.vue';

const totalNotExpiredCount = ref(0);
const totalExpiredCount = ref(0);

const updateTotalNotExpiredCount = (count) => {
  totalNotExpiredCount.value = count;
}
const updateTotalExpiredCount = (count) => {
  totalExpiredCount.value = count;
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h4 class="text-h4 mb-1">
        PBL List
      </h4>
    </VCol>
    <VCol cols="12">
      <PBLCards :totalNotExpiredCount="totalNotExpiredCount" :totalExpiredCount="totalExpiredCount"/>
    </VCol>
    <VCol cols="12">
      <PBLList @updateTotalNotExpired="updateTotalNotExpiredCount" @updateTotalExpired="updateTotalExpiredCount"/>
    </VCol>
  </VRow>
</template>
