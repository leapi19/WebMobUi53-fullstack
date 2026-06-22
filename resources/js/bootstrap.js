import { setDefaultHeaders, setDefaultBaseUrl } from './composables/useFetchApi';

// URL
setDefaultBaseUrl('/api/v1');

function getXsrfToken() { // injecte header
  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : null;
}

const xsrf = getXsrfToken();
if (xsrf) setDefaultHeaders({ 'X-XSRF-TOKEN': xsrf });
// Sanctum content
