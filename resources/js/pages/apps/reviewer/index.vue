<script setup>
import ReviewerCards from '@/views/apps/reviewer/ReviewerCards.vue';
import ReviewerList from '@/views/apps/reviewer/ReviewerList.vue';

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
        Reviewer List
      </h4>
    </VCol>
    <VCol cols="12">
      <ReviewerCards :totalNotExpiredCount="totalNotExpiredCount" :totalExpiredCount="totalExpiredCount"/>
    </VCol>
    <VCol cols="12">
      <ReviewerList @updateTotalNotExpired="updateTotalNotExpiredCount" @updateTotalExpired="updateTotalExpiredCount"/>
    </VCol>
  </VRow>
</template>
