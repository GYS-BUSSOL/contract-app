<script setup>
import VendorCards from '@/views/apps/vendor-assigment/VendorCards.vue';
import VendorList from '@/views/apps/vendor-assigment/VendorList.vue';

const totalNotPriorityCount = ref(0);
const totalPriorityCount = ref(0);

const updateTotalNotPriorityCount = (count) => {
  totalNotPriorityCount.value = count;
}
const updateTotalPriorityCount = (count) => {
  totalPriorityCount.value = count;
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h4 class="text-h4 mb-1">
        Vendor Assigment
      </h4>
    </VCol>
    <VCol cols="12">
      <VendorCards :totalNotPriorityCount="totalNotPriorityCount" :totalPriorityCount="totalPriorityCount" />
    </VCol>
    <VCol cols="12">
      <VendorList @updateTotalNotPriority="updateTotalNotPriorityCount" @updateTotalPriority="updateTotalPriorityCount"/>
    </VCol>
  </VRow>
</template>
