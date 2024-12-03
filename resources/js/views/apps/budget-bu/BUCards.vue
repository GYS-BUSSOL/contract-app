<script setup>
const props = defineProps({
  sumBudget: {
    type: Number,
    required: true
  },
  sumExpense: {
    type: Number,
    required: true
  },
  sumBalance: {
    type: Number,
    required: true
  },
  activeYear: {
    type: Number,
    required: true
  },
})

const IDRFormat = (data) => {
  return 'Rp. ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(data).replace('Rp', '').trim()
}

const widgetData = computed(() => [
  {
    title: 'Total Budget',
    value: `${IDRFormat(props.sumBudget)}`,
    desc: `Total budget pada tahun ${props.activeYear || new Date().getFullYear()}`,
    icon: 'tabler-step-into',
    iconColor: 'info',
  },
  {
    title: 'Total Expense',
    value: `${IDRFormat(props.sumExpense)}`,
    desc: `Total expense pada tahun ${props.activeYear || new Date().getFullYear()}`,
    icon: 'tabler-step-out',
    iconColor: 'warning',
  },
  {
    title: 'Total Balance',
    value: `${IDRFormat(props.sumBalance)}`,
    desc: `Total balance pada tahun ${props.activeYear || new Date().getFullYear()}`,
    icon: 'tabler-arrow-autofit-height',
    iconColor: 'success',
  },
]);
</script>
<template>
  <!-- Widgets -->
  <div class="d-flex">
      <VRow>
        <template
          v-for="(data, id) in widgetData"
          :key="id"
        >
          <VCol
            cols="12"
            md="4"
          >
            <VCard>
              <VCardText>
                <div class="d-flex justify-space-between">
                  <div class="d-flex flex-column gap-y-1">
                    <div class="text-body-1 text-high-emphasis">
                      {{ data.title }}
                    </div>
                    <div class="d-flex gap-x-2 align-center">
                      <h4 class="text-h4">
                        {{ data.value }}
                      </h4>
                    </div>
                    <div class="text-sm">
                      {{ data.desc }}
                    </div>
                  </div>
                  <VAvatar
                    :color="data.iconColor"
                    variant="tonal"
                    rounded
                    size="42"
                  >
                    <VIcon
                      :icon="data.icon"
                      size="26"
                    />
                  </VAvatar>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </template>
      </VRow>
    </div>
</template>
