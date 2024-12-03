<script setup>
import AreaCCCards from '@/views/configurations/area-management/cost-center/AreaCCCards.vue';
import AreaCCList from '@/views/configurations/area-management/cost-center/AreaCCList.vue';

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
        Cost Center List
      </h4>
    </VCol>
    <VCol cols="12">
      <AreaCCCards :totalNotActiveCount="totalNotActiveCount" :totalActiveCount="totalActiveCount"/>
    </VCol>
    <VCol cols="12">
      <AreaCCList @updateTotalNotActive="updateTotalNotActiveCount" @updateTotalActive="updateTotalActiveCount"/>
    </VCol>
  </VRow>
</template>
