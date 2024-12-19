<script setup>
import { useConfigStore } from '@core/stores/config';

const configStore = useConfigStore()
</script>

<template>
  <div
    class="d-flex align-center cursor-pointer"
    v-bind="$attrs"
    style="user-select: none; width: 100%;"
  >
    <span 
      style="width: 100%;"
      v-if="configStore.appContentLayoutNav === 'vertical'"
      class="d-none d-md-flex align-center text-disabled ms-2"
    >
      <marquee behavior="scroll" direction="right" scrollamount="12">Selamat Merayakan Natal & Tahun Baru 🎉 PT. Garuda Yamato Steel</marquee>
    </span>
  </div>
</template>

<style lang="scss">
@use "@styles/variables/vuetify.scss";

.meta-key {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  block-size: 1.5625rem;
  font-size: 0.8125rem;
  line-height: 1.3125rem;
  padding-block: 0.125rem;
  padding-inline: 0.25rem;
}

.app-bar-search-dialog {
  .custom-letter-spacing {
    letter-spacing: 0.8px;
  }

  .card-list {
    --v-card-list-gap: 8px;
  }
}
</style>
