<script setup>
const props = defineProps({
  isOpen: { type: Boolean, default: false },
  poll: { type: Object, default: null },
  i18n: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['close']);

function formatDate(date) {
  if (!date) return '-';
  return new Date(date).toLocaleString('fr-CH');
}
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-slate-800 rounded-lg p-6 max-w-sm w-full">
      <h3 class="text-lg font-bold dark:text-white mb-4">Dates du sondage</h3>
      <div class="space-y-3">
        <div>
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Début</p>
          <p class="dark:text-white">{{ formatDate(poll?.started_at) }}</p>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Fin</p>
          <p class="dark:text-white">{{ formatDate(poll?.ends_at) }}</p>
        </div>
      </div>
      <button @click="emit('close')" class="mt-4 w-full px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">
        Fermer
      </button>
    </div>
  </div>
</template>
