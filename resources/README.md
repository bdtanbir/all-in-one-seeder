# Admin App (Vue 3)

This plugin uses Vue 3 from the `resources` directory and builds assets into `assets/admin`.
SCSS is supported via `sass` (use `.scss` files in `src/` or `<style lang="scss">` in `.vue` files).

## Install

```bash
npm install --prefix resources
```

## Build

```bash
npm run build --prefix resources
```

## Dev server

```bash
npm run dev --prefix resources
```

After building, WordPress loads files using `assets/admin/.vite/manifest.json`.
