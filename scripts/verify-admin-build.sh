#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
MANIFEST_PATH="$ROOT_DIR/assets/admin/.vite/manifest.json"

if [[ ! -f "$MANIFEST_PATH" ]]; then
  echo "[FAIL] Missing build manifest: $MANIFEST_PATH"
  echo "Run: npm install --prefix resources && npm run build --prefix resources"
  exit 1
fi

export MANIFEST_PATH
export ROOT_DIR

php <<'PHP'
<?php
$manifestPath = getenv('MANIFEST_PATH');
$rootDir = rtrim((string) getenv('ROOT_DIR'), '/');

if (!$manifestPath || !$rootDir) {
    fwrite(STDERR, "[FAIL] Missing env config for build verification.\n");
    exit(1);
}

$raw = @file_get_contents($manifestPath);
if ($raw === false) {
    fwrite(STDERR, "[FAIL] Unable to read manifest: {$manifestPath}\n");
    exit(1);
}

$manifest = json_decode($raw, true);
if (!is_array($manifest) || !$manifest) {
    fwrite(STDERR, "[FAIL] Manifest is invalid JSON or empty: {$manifestPath}\n");
    exit(1);
}

$entry = null;
foreach (['src/main.js', 'resources/src/main.js'] as $key) {
    if (!empty($manifest[$key]['file'])) {
        $entry = $manifest[$key];
        break;
    }
}

if (!$entry) {
    foreach ($manifest as $item) {
        if (!empty($item['isEntry']) && !empty($item['file'])) {
            $entry = $item;
            break;
        }
    }
}

if (!$entry || empty($entry['file'])) {
    fwrite(STDERR, "[FAIL] Could not resolve an entry file from manifest.\n");
    exit(1);
}

$missing = [];

$jsFile = $rootDir . '/assets/admin/' . ltrim((string) $entry['file'], '/');
if (!is_file($jsFile)) {
    $missing[] = $jsFile;
}

if (!empty($entry['css']) && is_array($entry['css'])) {
    foreach ($entry['css'] as $cssFile) {
        $cssPath = $rootDir . '/assets/admin/' . ltrim((string) $cssFile, '/');
        if (!is_file($cssPath)) {
            $missing[] = $cssPath;
        }
    }
}

if ($missing) {
    fwrite(STDERR, "[FAIL] Build artifacts referenced in manifest are missing:\n");
    foreach ($missing as $path) {
        fwrite(STDERR, " - {$path}\n");
    }
    exit(1);
}

echo "[OK] Admin build assets are present and valid.\n";
PHP
