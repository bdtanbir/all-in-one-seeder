/**
 * Thin wrapper around fetch() pre-configured with the WordPress REST nonce
 * injected by AssetLoader via window.AIOSSeeder.
 */
export function useApi() {
  const cfg    = window.AIOSSeeder ?? {};
  const base   = (cfg.restUrl ?? '').replace(/\/$/, '') + '/aio-seeder/v1';
  const headers = {
    'Content-Type': 'application/json',
    'X-WP-Nonce':   cfg.nonce ?? '',
  };

  async function get(endpoint) {
    const res = await fetch(base + endpoint, { headers });
    if (!res.ok) throw new Error(`Server error ${res.status}`);
    return res.json();
  }

  async function post(endpoint, body = {}) {
    const res = await fetch(base + endpoint, {
      method:  'POST',
      headers,
      body:    JSON.stringify(body),
    });
    // Return the parsed body even on non-2xx so the caller can read errors[]
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.message ?? `Server error ${res.status}`);
    return data;
  }

  async function del(endpoint) {
    const res = await fetch(base + endpoint, { method: 'DELETE', headers });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.message ?? `Server error ${res.status}`);
    return data;
  }

  return { get, post, del };
}
