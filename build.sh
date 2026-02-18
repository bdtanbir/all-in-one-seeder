#!/usr/bin/env bash
set -euo pipefail

# ── Config ─────────────────────────────────────────────────────────────────────
PLUGIN_SLUG="all-in-one-seeder"
PLUGIN_FILE="all-in-one-seeder.php"
RESOURCES_DIR="resources"
BUILD_DIR="builds"

# Read version from plugin header
VERSION=$(grep -i "^ \* Version:" "$PLUGIN_FILE" | awk '{print $3}')
ZIP_NAME="${PLUGIN_SLUG}-${VERSION}.zip"

echo "▶ Building ${PLUGIN_SLUG} v${VERSION}"

# ── Step 1: Install JS dependencies ────────────────────────────────────────────
echo "  [1/4] Installing JS dependencies…"
npm install --prefix "$RESOURCES_DIR" --silent

# ── Step 2: Compile assets with Vite ───────────────────────────────────────────
echo "  [2/4] Building assets…"
npm run build --prefix "$RESOURCES_DIR" --silent

# ── Step 3: Verify build artifacts ─────────────────────────────────────────────
echo "  [3/4] Verifying build…"
bash scripts/verify-admin-build.sh

# ── Step 4: Package ZIP ────────────────────────────────────────────────────────
echo "  [4/4] Creating ${ZIP_NAME}…"
mkdir -p "$BUILD_DIR"

# Remove previous ZIP for this version if it exists
rm -f "${BUILD_DIR}/${ZIP_NAME}"

# Create the ZIP, excluding dev-only paths
zip -r "${BUILD_DIR}/${ZIP_NAME}" . \
  --exclude "*.git*" \
  --exclude ".idea/*" \
  --exclude "*.DS_Store" \
  --exclude "${BUILD_DIR}/*" \
  --exclude "refs/*" \
  --exclude "resources/node_modules/*" \
  --exclude "resources/src/*" \
  --exclude "resources/package.json" \
  --exclude "resources/package-lock.json" \
  --exclude "resources/vite.config.js" \
  --exclude "resources/README.md" \
  --exclude "scripts/*" \
  --exclude "build.sh" \
  --exclude "Makefile" \
  --exclude "BUILDING.md" \
  --exclude ".gitignore"

echo ""
echo "✓ Done! Output: ${BUILD_DIR}/${ZIP_NAME}"
