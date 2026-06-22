import { ref } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';

const polls = ref([]); // singleton, ref -> màj auto

export function usePollStore() {
  const { fetchApi } = useFetchApi();

  function setPolls(data) {
    polls.value = data;
  }
//règles métier
  async function deletePoll(id) {
    polls.value = polls.value.filter(p => p.id != id);
    fetchApi({ url: 'polls/' + id, method: 'DELETE' });
  }

  // post, unshift -> màj auto
  async function createPoll(data) {
    const newPoll = await fetchApi({ url: 'polls', method: 'POST', data });
    polls.value.unshift(newPoll);
    return newPoll;
  }

 async function updatePoll(id, data) {
  const updated = await fetchApi({ url: 'polls/' + id, method: 'PUT', data });
  const index = polls.value.findIndex(p => p.id === id);
  if (index !== -1) polls.value[index] = updated;
  return updated;
}

  function getShareLink(token) {
    return window.location.origin + '/poll/' + token;
  }

  return { polls, setPolls, deletePoll, createPoll, updatePoll, getShareLink };
}
