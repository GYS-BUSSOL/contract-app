<script setup>
import BUCards from '@/views/apps/budget-bu/BUCards.vue';
import BUList from '@/views/apps/budget-bu/BUList.vue';

const sumBudget = ref(0)
const sumExpense = ref(0)
const sumBalance = ref(0)
const activeYear = ref(0)

const updateSumBudget = (count) => {
  sumBudget.value = Number(count);
}
const updateSumExpense = (count) => {
  sumExpense.value = Number(count);
}
const updateSumBalance = (count) => {
  sumBalance.value = Number(count);
}
const updateActiveYear = (year) => {
  activeYear.value = Number(year);
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h4 class="text-h4 mb-1">
        Budget BU List
      </h4>
    </VCol>
    <VCol cols="12">
      <BUCards :sumBudget="sumBudget" :sumExpense="sumExpense" :sumBalance="sumBalance" :activeYear="activeYear"/>
    </VCol>
    <VCol cols="12">
      <BUList @sum-budget="updateSumBudget" @sum-expense="updateSumExpense" @sum-balance="updateSumBalance" @active-year="updateActiveYear"/>
    </VCol>
  </VRow>
</template>
