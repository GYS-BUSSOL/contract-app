<script setup>
import ManDaysCards from '@/views/configurations/manDays-rate/ManDaysCards.vue';
import ManDaysList from '@/views/configurations/manDays-rate/ManDaysList.vue';

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
        Man Days List
      </h4>
    </VCol>
    <VCol cols="12">
      <ManDaysCards :totalNotActiveCount="totalNotActiveCount" :totalActiveCount="totalActiveCount"/>
    </VCol>
    <VCol cols="12">
      <ManDaysList @updateTotalNotActive="updateTotalNotActiveCount" @updateTotalActive="updateTotalActiveCount"/>
    </VCol>
  </VRow>
</template>
