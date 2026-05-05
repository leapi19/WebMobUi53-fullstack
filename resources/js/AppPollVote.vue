<script setup>
import { ref, computed } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';
import { usePolling } from '@/composables/usePolling';

const props = defineProps({
  token: { type: String, required: true },
  isAuthenticated: { type: Boolean, default: false },
  i18n: { type: Object, default: () => ({}) },
});

const { fetchApi } = useFetchApi();

const poll = ref(null);
const totalVotes = ref(0);
const loading = ref(true);
const error = ref(null);
const selectedOptions = ref([]);
const voteError = ref(null);
const voteSuccess = ref(false);
const hasVoted = ref(false);
const isFetching = ref(false);

async function fetchResults() {
  if (isFetching.value) return;
  isFetching.value = true;
  try {
    const data = await fetchApi({ url: `polls/${props.token}/results` });
    poll.value = data.poll;
    totalVotes.value = data.total_votes;
    hasVoted.value = data.has_voted;
    loading.value = false;
  } catch (err) {
    if (!poll.value) {
      error.value = 'Sondage introuvable.';
      loading.value = false;
    }
  } finally {
    isFetching.value = false;
  }
}

fetchResults();
usePolling(fetchResults, 10000);

const isExpired = computed(() => {
  if (!poll.value?.ends_at) return false;
  return new Date(poll.value.ends_at) < new Date();
});

const canVote = computed(() => {
  return props.isAuthenticated && poll.value && !poll.value.is_draft && !isExpired.value && !hasVoted.value;
});

const canSeeResults = computed(() => {
  return props.isAuthenticated || poll.value?.results_public;
});

function toggleOption(id) {
  if (poll.value?.allow_multiple_choices) {
    if (selectedOptions.value.includes(id)) {
      selectedOptions.value = selectedOptions.value.filter(o => o !== id);
    } else {
      selectedOptions.value.push(id);
    }
  } else {
    selectedOptions.value = [id];
  }
}

async function submitVote() {
  voteError.value = null;
  try {
    await fetchApi({
      url: `polls/${props.token}/vote`,
      method: 'POST',
      data: { option_ids: selectedOptions.value },
    });
    voteSuccess.value = true;
    hasVoted.value = true;
    await fetchResults();
  } catch (err) {
    voteError.value = err?.data?.message ?? 'Une erreur est survenue.';
  }
}

function getPercentage(votesCount) {
  if (totalVotes.value === 0) return 0;
  return Math.round((votesCount / totalVotes.value) * 100);
}
</script>

<template>
  <div class="max-w-xl mx-auto py-8 px-4">
    <div v-if="loading" class="text-center dark:text-white">Chargement...</div>
    <div v-else-if="error" class="text-red-500">{{ error }}</div>

    <div v-else>
      <h1 class="text-2xl font-bold dark:text-white mb-2">{{ poll.title || poll.question }}</h1>
      <p v-if="poll.title" class="text-gray-600 dark:text-gray-300 mb-6">{{ poll.question }}</p>

      <div v-if="isExpired" class="mb-4 p-3 bg-red-100 text-red-700 rounded font-semibold">
        {{ i18n.vote.closed }}
      </div>

      <div v-else-if="poll.is_draft" class="mb-4 p-3 bg-yellow-100 text-yellow-700 rounded">
        Ce sondage n'est pas encore ouvert.
      </div>

      <div v-if="canVote && !voteSuccess" class="space-y-3 mb-6">
        <div v-for="option in poll.options" :key="option.id">
          <label class="flex items-center gap-3 cursor-pointer p-3 border rounded hover:bg-gray-50 dark:hover:bg-slate-700 dark:text-white">
            <input
              v-if="poll.allow_multiple_choices"
              type="checkbox"
              :value="option.id"
              :checked="selectedOptions.includes(option.id)"
              @change="toggleOption(option.id)"
            />
            <input
              v-else
              type="radio"
              :value="option.id"
              :checked="selectedOptions.includes(option.id)"
              @change="toggleOption(option.id)"
            />
            {{ option.label }}
          </label>
        </div>

        <div v-if="voteError" class="p-3 bg-red-100 text-red-700 rounded">{{ voteError }}</div>

        <button @click="submitVote"
          :disabled="selectedOptions.length === 0"
          class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700 disabled:opacity-50">
          {{ i18n.vote.submit }}
        </button>
      </div>

      <div v-if="voteSuccess" class="mb-4 p-3 bg-green-100 text-green-700 rounded">
        Vote enregistré !
      </div>

      <div v-if="hasVoted && !voteSuccess" class="mb-4 p-3 bg-blue-100 text-blue-700 rounded">
        Vous avez déjà voté.
      </div>

      <div v-if="!isAuthenticated && !poll.results_public" class="mb-4 p-3 bg-yellow-100 text-yellow-700 rounded">
        Connectez-vous pour voter.
      </div>

      <div v-if="canSeeResults" class="mt-6">
        <h2 class="text-lg font-semibold dark:text-white mb-3">{{ i18n.vote.results }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
          {{ i18n.vote.total_votes.replace(':count', totalVotes) }}
        </p>

        <div class="space-y-3">
          <div v-for="option in poll.options" :key="option.id">
            <div class="flex justify-between text-sm dark:text-white mb-1">
              <span>{{ option.label }}</span>
              <span>{{ getPercentage(option.votes_count) }}% ({{ option.votes_count }})</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-slate-700 rounded-full h-4">
              <div
                class="bg-teal-600 h-4 rounded-full transition-all duration-500"
                :style="{ width: getPercentage(option.votes_count) + '%' }">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
