<script setup>
import SPKReportCards from '@/views/apps/spk/report/SPKReportCards.vue';
import SPKReportList from '@/views/apps/spk/report/SPKReportList.vue';

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
        SPK Report
      </h4>
    </VCol>
    <VCol cols="12">
      <SPKReportCards :totalNotActiveCount="totalNotActiveCount" :totalActiveCount="totalActiveCount"/>
    </VCol>
    <VCol cols="12">
      <SPKReportList @updateTotalNotActive="updateTotalNotActiveCount" @updateTotalActive="updateTotalActiveCount"/>
    </VCol>
  </VRow>
</template>
