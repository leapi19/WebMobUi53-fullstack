<script setup>
import { usePollStore } from '@/stores/usePollStore';

const props = defineProps({
  i18n: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['edit']);

const { polls, deletePoll, getShareLink } = usePollStore();

function copyLink(token) {
  navigator.clipboard.writeText(getShareLink(token));
  alert('Lien copié !');
}
</script>

<template>
  <p v-if="polls.length === 0">{{ i18n.dashboard.no_polls }}</p>
  <table v-else class="w-full border-collapse text-left">
    <thead>
      <tr>
        <th class="border px-3 py-2">{{ i18n.table.actions }}</th>
        <th class="border px-3 py-2">{{ i18n.table.id }}</th>
        <th class="border px-3 py-2">{{ i18n.table.title }}</th>
        <th class="border px-3 py-2">{{ i18n.table.question }}</th>
        <th class="border px-3 py-2">{{ i18n.table.draft }}</th>
        <th class="border px-3 py-2">{{ i18n.table.started_at }}</th>
        <th class="border px-3 py-2">{{ i18n.table.ends_at }}</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="poll in polls" :key="poll.id">
        <td class="border px-3 py-2 flex gap-1">
          <button @click="emit('edit', poll)" class="btn-edit">
            {{ i18n.table.edit }}
          </button>
          <button @click="deletePoll(poll.id)" class="btn-delete">
            {{ i18n.table.delete }}
          </button>
          <button @click="copyLink(poll.secret_token)" class="btn-share">
            {{ i18n.table.share }}
          </button>
        </td>
        <td class="border px-3 py-2">{{ poll.id }}</td>
        <td class="border px-3 py-2">{{ poll.title || '-' }}</td>
        <td class="border px-3 py-2">{{ poll.question }}</td>
        <td class="border px-3 py-2">{{ poll.is_draft ? i18n.table.yes : i18n.table.no }}</td>
        <td class="border px-3 py-2">{{ poll.started_at || '-' }}</td>
        <td class="border px-3 py-2">{{ poll.ends_at || '-' }}</td>
      </tr>
    </tbody>
  </table>
</template>

<style scoped>
.btn-edit {
  background-color: #2563eb;
  color: white;
  padding: 0.25rem 0.5rem;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
}
.btn-delete {
  background-color: #e3342f;
  color: white;
  padding: 0.25rem 0.5rem;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
}
.btn-share {
  background-color: #6b7280;
  color: white;
  padding: 0.25rem 0.5rem;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
}
</style>
