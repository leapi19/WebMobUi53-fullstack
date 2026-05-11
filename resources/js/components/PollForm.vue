<script setup>
import { ref, watch } from 'vue';
import { usePollStore } from '@/stores/usePollStore';

const props = defineProps({
  poll: { type: Object, default: null },
  i18n: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['saved', 'cancel']);

const { createPoll, updatePoll } = usePollStore();

const title = ref(props.poll?.title ?? '');
const question = ref(props.poll?.question ?? '');
const isDraft = ref(Boolean(props.poll?.is_draft ?? true));
const allowMultiple = ref(Boolean(props.poll?.allow_multiple_choices ?? false));
const resultsPublic = ref(Boolean(props.poll?.results_public ?? false));
const duration = ref(props.poll?.duration ?? '');
const options = ref(
  props.poll?.options?.map(o => ({ label: o.label })) ?? [{ label: '' }, { label: '' }]
);

const error = ref(null);

// Watch pour réinitialiser les champs quand le poll change
watch(() => props.poll, (newPoll) => {
  if (newPoll) {
    title.value = newPoll.title ?? '';
    question.value = newPoll.question ?? '';
   isDraft.value = Boolean(newPoll.is_draft ?? true);
    allowMultiple.value = Boolean(newPoll.allow_multiple_choices ?? false);
    resultsPublic.value = Boolean(newPoll.results_public ?? false);
    duration.value = newPoll.duration ?? '';
    options.value = newPoll.options?.map(o => ({ label: o.label })) ?? [{ label: '' }, { label: '' }];
  } else {
    // Réinitialiser pour un nouveau sondage
    title.value = '';
    question.value = '';
    isDraft.value = true;
    allowMultiple.value = false;
    resultsPublic.value = false;
    duration.value = '';
    options.value = [{ label: '' }, { label: '' }];
  }
  error.value = null;
}, { immediate: true });

function addOption() {
  options.value.push({ label: '' });
}

function removeOption(index) {
  if (options.value.length > 2) options.value.splice(index, 1);
}

function validateOptions() {
  // Filtrer les options vides et convertir en minuscules pour comparaison insensible à la casse
  const nonEmptyLabels = options.value
    .map(o => o.label.trim().toLowerCase())
    .filter(label => label !== '');

  // Vérifier les doublons
  const uniqueLabels = new Set(nonEmptyLabels);
  if (uniqueLabels.size !== nonEmptyLabels.length) {
    // Trouver les doublons pour le message d'erreur
    const duplicates = nonEmptyLabels.filter((item, index) => nonEmptyLabels.indexOf(item) !== index);
    const uniqueDuplicates = [...new Set(duplicates)];
    return {
      valid: false,
      message: `Erreur : l'option "${uniqueDuplicates[0]}" apparaît plusieurs fois.`
    };
  }

  return { valid: true };
}

async function submit() {
  error.value = null;

  // Valider les options
  const validation = validateOptions();
  if (!validation.valid) {
    error.value = validation.message;
    return;
  }

  const data = {
    title: title.value || null,
    question: question.value,
    is_draft: isDraft.value,
    allow_multiple_choices: allowMultiple.value,
    results_public: resultsPublic.value,
    duration: duration.value ? parseInt(duration.value) : null,
    options: options.value,
  };

  try {
    if (props.poll) {
      await updatePoll(props.poll.id, data);
    } else {
      await createPoll(data);
    }
    emit('saved');
  } catch (err) {
    console.log('erreur:', err);
    error.value = err?.data?.message ?? props.i18n?.form?.errors?.default ?? 'Une erreur est survenue.';
  }
}
</script>

<template>
  <div class="bg-white dark:bg-slate-800 rounded-lg p-6 shadow">
    <h2 class="text-xl font-bold mb-4 dark:text-white">
      {{ poll ? i18n.form.edit_title : i18n.form.create_title }}
    </h2>

    <div v-if="error" class="mb-4 p-3 bg-red-100 text-red-700 rounded">{{ error }}</div>

    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium dark:text-gray-300">
          {{ i18n.form.fields.title.label }}
        </label>
        <input v-model="title" type="text"
          class="mt-1 w-full border rounded px-3 py-2 dark:bg-slate-700 dark:text-white" />
      </div>

      <div>
        <label class="block text-sm font-medium dark:text-gray-300">
          {{ i18n.form.fields.question.label }}
        </label>
        <input v-model="question" type="text"
          class="mt-1 w-full border rounded px-3 py-2 dark:bg-slate-700 dark:text-white" />
      </div>

      <div>
        <label class="block text-sm font-medium dark:text-gray-300 mb-2">
          {{ i18n.form.fields.options.label }}
        </label>
        <div v-for="(opt, i) in options" :key="i" class="flex gap-2 mb-2">
          <input v-model="opt.label" type="text"
            :placeholder="i18n.form.fields.options.placeholder.replace(':number', i + 1)"
            class="flex-1 border rounded px-3 py-2 dark:bg-slate-700 dark:text-white" />
          <button @click="removeOption(i)"
            class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600"
            :disabled="options.length <= 2">
            {{ i18n.form.actions.remove_option }}
          </button>
        </div>
        <button @click="addOption"
          class="mt-1 px-3 py-2 bg-gray-200 dark:bg-slate-600 dark:text-white rounded hover:bg-gray-300">
          {{ i18n.form.actions.add_option }}
        </button>
      </div>

      <div class="space-y-2">
        <label class="flex items-center gap-2 dark:text-gray-300">
          <input type="checkbox" v-model="isDraft" />
          {{ i18n.form.fields.is_draft.label }}
        </label>
        <label class="flex items-center gap-2 dark:text-gray-300">
          <input type="checkbox" v-model="allowMultiple" />
          {{ i18n.form.fields.allow_multiple.label }}
        </label>
        <label class="flex items-center gap-2 dark:text-gray-300">
          <input type="checkbox" v-model="resultsPublic" />
          {{ i18n.form.fields.results_public.label }}
        </label>
      </div>

      <div>
        <label class="block text-sm font-medium dark:text-gray-300">
          {{ i18n.form.fields.duration.label }}
        </label>
        <input v-model="duration" type="number" min="1"
          class="mt-1 w-full border rounded px-3 py-2 dark:bg-slate-700 dark:text-white" />
      </div>

      <div class="flex gap-3 pt-2">
        <button @click="submit"
          class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">
          {{ poll ? i18n.form.actions.save : i18n.form.actions.create }}
        </button>
        <button @click="emit('cancel')"
          class="px-4 py-2 bg-gray-300 dark:bg-slate-600 dark:text-white rounded hover:bg-gray-400">
          {{ i18n.form.actions.cancel }}
        </button>
      </div>
    </div>
  </div>
</template>
