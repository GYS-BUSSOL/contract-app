<script setup>
import HRCards from '@/views/configurations/hr/HRCards.vue';
import HRList from '@/views/configurations/hr/HRList.vue';

const totalNotActiveCount = ref(0);
const totalActiveCount = ref(0);

const updateTotalNotActiveCount = (count) => {
  totalNotActiveCount.value = count;
};
const updateTotalActiveCount = (count) => {
  totalActiveCount.value = count;
};
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h4 class="text-h4 mb-1">
        User Management List
      </h4>
    </VCol>
    <VCol cols="12">
      <HRCards :totalNotActiveCount="totalNotActiveCount" :totalActiveCount="totalActiveCount"/>
    </VCol>
    <VCol cols="12">
      <HRList @updateTotalNotActive="updateTotalNotActiveCount" @updateTotalActive="updateTotalActiveCount"/>
    </VCol>
  </VRow>
</template>
