# Build & Deploy Checks

## Build admin assets

```bash
npm install --prefix resources
npm run build --prefix resources
```

## Verify build artifacts (deploy gate)

```bash
./scripts/verify-admin-build.sh
```

This command exits with code `1` if:
- `assets/admin/.vite/manifest.json` is missing
- manifest JSON is invalid
- entry file is missing
- any referenced CSS file is missing

Use it in CI/deploy pipelines to fail early before release.
