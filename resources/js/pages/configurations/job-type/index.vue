<script setup>
import JobTypeCards from '@/views/configurations/job-type/JobTypeCards.vue';
import JobTypeList from '@/views/configurations/job-type/JobTypeList.vue';

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
        Job Type List
      </h4>
    </VCol>
    <VCol cols="12">
      <JobTypeCards :totalNotActiveCount="totalNotActiveCount" :totalActiveCount="totalActiveCount"/>
    </VCol>
    <VCol cols="12">
      <JobTypeList @updateTotalNotActive="updateTotalNotActiveCount" @updateTotalActive="updateTotalActiveCount"/>
    </VCol>
  </VRow>
</template>
