<?php

namespace AllInOneSeeder\App\Hooks;

class AssetLoader
{
    private const HANDLE = 'aios-admin-app';

    public function enqueue(): void
    {
        $manifestPath = AIOS_PLUGIN_PATH . 'assets/admin/.vite/manifest.json';

        if (!file_exists($manifestPath)) {
            $this->enqueueFallbackNotice();
            return;
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);

        if (!is_array($manifest)) {
            $this->enqueueFallbackNotice();
            return;
        }

        $entry = $this->resolveEntry($manifest);

        if (!$entry || empty($entry['file'])) {
            $this->enqueueFallbackNotice();
            return;
        }

        $version = AIOS_PLUGIN_VERSION;

        wp_enqueue_script(
            self::HANDLE,
            AIOS_PLUGIN_URL . 'assets/admin/' . ltrim($entry['file'], '/'),
            [],
            $version,
            true
        );

        if (!empty($entry['css']) && is_array($entry['css'])) {
            foreach ($entry['css'] as $index => $cssFile) {
                wp_enqueue_style(
                    self::HANDLE . '-' . $index,
                    AIOS_PLUGIN_URL . 'assets/admin/' . ltrim($cssFile, '/'),
                    [],
                    $version
                );
            }
        }

        wp_add_inline_script(
            self::HANDLE,
            'window.AIOSSeeder = ' . wp_json_encode([
                'restUrl' => esc_url_raw(rest_url()),
                'nonce'   => wp_create_nonce('wp_rest'),
                'version' => AIOS_PLUGIN_VERSION,
            ]) . ';',
            'before'
        );
    }

    private function enqueueFallbackNotice(): void
    {
        wp_register_script(self::HANDLE, false, [], AIOS_PLUGIN_VERSION, true);
        wp_enqueue_script(self::HANDLE);
        wp_add_inline_script(
            self::HANDLE,
            "console.warn('[All-in-One Seeder] Vue assets are missing. Run: npm install --prefix resources && npm run build --prefix resources');"
        );
    }

    private function resolveEntry(array $manifest): ?array
    {
        $knownEntries = [
            'src/main.js',
            'resources/src/main.js',
        ];

        foreach ($knownEntries as $entryKey) {
            if (!empty($manifest[$entryKey]['file'])) {
                return $manifest[$entryKey];
            }
        }

        foreach ($manifest as $entry) {
            if (!empty($entry['isEntry']) && !empty($entry['file'])) {
                return $entry;
            }
        }

        return null;
    }
}
