<script setup>
import { ref } from 'vue';
import { usePollStore } from '@/stores/usePollStore';
import PollDateModal from './PollDateModal.vue';

const props = defineProps({
  i18n: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['edit']);

const { polls, deletePoll, getShareLink } = usePollStore();
const showDateModal = ref(false);
const selectedPoll = ref(null);

function copyLink(token) {
  navigator.clipboard.writeText(getShareLink(token));
  alert('Lien copié !');
}

function openDateModal(poll) {
  selectedPoll.value = poll;
  showDateModal.value = true;
}

function closeDateModal() {
  showDateModal.value = false;
  selectedPoll.value = null;
}
</script>

<template>
  <p v-if="polls.length === 0">{{ i18n.dashboard.no_polls }}</p>
  <table v-else class="w-full border-collapse text-left">
    <thead>
      <tr>
        <th class="border px-3 py-2">{{ i18n.table.actions }}</th>
        <th class="border px-3 py-2">{{ i18n.table.question }}</th>
        <th class="border px-3 py-2">{{ i18n.table.draft }}</th>
        <th class="hidden sm:table-cell border px-3 py-2">{{ i18n.table.started_at }}</th>
        <th class="hidden sm:table-cell border px-3 py-2">{{ i18n.table.ends_at }}</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="poll in polls" :key="poll.id">
        <td class="border px-3 py-2 flex gap-1 flex-wrap justify-center">
          <button @click="emit('edit', poll)" class="btn-edit" :disabled="!poll.is_draft">
            {{ i18n.table.edit }}
          </button>
          <button @click="deletePoll(poll.id)" class="btn-delete">
            {{ i18n.table.delete }}
          </button>
          <button @click="copyLink(poll.secret_token)" class="btn-share">
            {{ i18n.table.share }}
          </button>
          <button @click="openDateModal(poll)" class="btn-date sm:hidden">
            📅 {{ i18n.table.date ?? 'Date' }}
          </button>
        </td>
        <td class="border px-3 py-2 text-center">
          <div class="font-bold">{{ poll.title || poll.question }}</div>
          <div class="text-sm text-gray-600 dark:text-gray-400">{{ poll.title ? poll.question : '' }}</div>
        </td>
        <td class="border px-3 py-2 text-center">{{ poll.is_draft ? i18n.table.yes : i18n.table.no }}</td>
        <td class="hidden sm:table-cell border px-3 py-2">{{ poll.started_at ? new Date(poll.started_at).toLocaleString('fr-CH') : '-' }}</td>
        <td class="hidden sm:table-cell border px-3 py-2">{{ poll.ends_at ? new Date(poll.ends_at).toLocaleString('fr-CH') : '-' }}</td>
      </tr>
    </tbody>
  </table>

  <PollDateModal
    :isOpen="showDateModal"
    :poll="selectedPoll"
    :i18n="i18n"
    @close="closeDateModal"
  />
</template>

<style scoped>
.btn-edit {
  background-color: #2563eb;
  color: white;
  padding: 0.25rem 0.5rem;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
  font-size: 0.875rem;
}
.btn-edit:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
  opacity: 0.6;
}
.btn-delete {
  background-color: #e3342f;
  color: white;
  padding: 0.25rem 0.5rem;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
  font-size: 0.875rem;
}
.btn-share {
  background-color: #6b7280;
  color: white;
  padding: 0.25rem 0.5rem;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
  font-size: 0.875rem;
}
.btn-date {
  background-color: #10b981;
  color: white;
  padding: 0.25rem 0.5rem;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
  font-size: 0.875rem;
}
</style>

