<script setup>
import { ref } from 'vue';
import PollTable from './components/PollTable.vue';
import PollForm from './components/PollForm.vue';
import { usePollStore } from '@/stores/usePollStore';

const props = defineProps({
  polls: { type: Array, default: () => [] },
  loginUrl: { type: String, default: null },
  username: { type: String, default: null },
  i18n: { type: Object, default: () => ({}) },
});

// plusieurs composants mêmes données
const { setPolls } = usePollStore();
setPolls(props.polls);

//7
const showForm = ref(false);
const pollToEdit = ref(null);

// balance PollForm PollTable
function openCreate() {
  pollToEdit.value = null;
  showForm.value = true;
}

function openEdit(poll) {
  pollToEdit.value = poll;
  showForm.value = true;
}

function onSaved() {
  showForm.value = false;
  pollToEdit.value = null;
}

function onCancel() {
  showForm.value = false;
  pollToEdit.value = null;
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold dark:text-white">{{ i18n.dashboard.title }}</h1>
      <button
        v-if="!showForm"
        @click="openCreate"
        class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">
        {{ i18n.dashboard.create }}
      </button>
    </div>
<!-- 7 v-if balance -->
    <PollForm
      v-if="showForm"
      :poll="pollToEdit"
      :i18n="i18n"
      @saved="onSaved"
      @cancel="onCancel"
    />

    <PollTable
      v-else
      :i18n="i18n"
      @edit="openEdit"
    />
  </div>
</template>
