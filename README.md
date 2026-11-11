# Devoted WP Start

💚 [The Devoted](https://www.the-devoted.com/)'s starter kit for WordPress.

💻 This project is hosted at
[devoted-wp-start.the-devoted.dev](https://devoted-wp-start.the-devoted.dev/).

✅ View open issues and tasks on the [Project
Board](https://github.com/orgs/The-Devoted/projects/3).

📑 See the [Wiki](https://github.com/The-Devoted/devoted-wp-start/wiki) for detailed usage instructions.

----


### Custom Content Scaffolding

The starter kit comes with scaffolding in place for creating the following using WordPress's native tools:

- Custom Post Types
- Custom Taxonomies
- Meta Boxes / Custom Fields
- Options Pages
- Post Meta

The starter kit has examples for usage of each that you can use to create your
own custom content. If your project has needs beyond what is shown in demos,
consider using the ACF plugin (or the pro version, depending on your needs).

See each example file for starter configurations and instructions.

#### Extending from a child theme

A child theme can reference these files' exported constants directly. Meaning
for example, you add additional fields to an existing options page from a child
theme.

Caveat: if an option's `sanitize_callback` ever returns a fixed-shape array (as
the meta-boxes/options examples do), a child theme's extra fields need that
callback to pass unknown keys through rather than silently drop them.

### Custom blocks (TypeScript)

Custom Gutenberg blocks are authored in **TypeScript** and compiled with
[`@wordpress/scripts`](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/).
They live inside the theme:

```
wp-content/themes/devoted/
├── package.json         # block build tooling & scripts
├── tsconfig.json        # TypeScript config (used by `npm run typecheck`)
├── src/                 # block source (TypeScript / SCSS) — edit here
│   └── example/         # sample block; copy this folder to make a new one
└── build/               # compiled output (git-ignored, generated)
```

Each block is a folder under `src/` containing a `block.json` plus its
`index.ts`, `edit.tsx`, `save.tsx`, and styles. Every compiled block is
registered automatically by `devoted_register_blocks()` in `functions.php`, so
**adding a block only requires creating a new folder under `src/`** — no PHP
changes needed.

Block development runs on your host machine (requires [Node.js](https://nodejs.org/)
20+). From `wp-content/themes/devoted/`:

```sh
npm install          # first time only
npm run start        # watch mode — recompiles src/ → build/ on save
```

With the Docker stack running, compiled blocks appear in the editor at
[http://localhost:8000](http://localhost:8000) (the theme is bind-mounted).

Other scripts:

| Command | What it does |
|---|---|
| `npm run build` | One-off production build of `src/` → `build/`. |
| `npm run typecheck` | Type-check with `tsc` (the build itself does **not** fail on type errors, so run this — ideally in CI). |
| `npm run lint:js` | Lint block source. |
| `npm run format` | Auto-format block source. |

> **Note:** the build tool (`@wordpress/scripts`) *transpiles* TypeScript but
> does not *type-check* it — that's why `npm run typecheck` is a separate step.

On deploy, blocks are compiled inside the container (the WordPress image now
includes Node.js): the workflow rebuilds the image and runs
`npm ci && npm run build` against the theme. Because `build/` is git-ignored,
committing a change to a block means committing the `src/` change only.

### End-to-end testing

[Playwright](https://playwright.dev) smoke tests live in `tests/e2e/`, a
separate npm package from the theme. With the Docker stack running:

```sh
cd tests/e2e
npm install       # first time only
npm test
```

See [`tests/e2e/README.md`](tests/e2e/README.md) for scope, environment
variables, and how this runs in CI (`.github/workflows/test.yml`, on pull
requests and pushes to `main`).



## Deployment

When commits are made to the `[main]` branch, changes are automatically deployed
to
[devoted-wp-start.the-devoted.dev](https://devoted-wp-start.the-devoted.dev/).

The deployment action does the following:

1. Navigates to the repo directory.
2. Checks out the main branch and pulls.
3. Stops Docker, then rebuilds and restarts it in detached mode.
4. Runs a Composer update within the WordPress container `docker exec wordpress composer update`.
5. Rebuilds and runs the theme's npm dependencies within the WordPress container
   `docker exec wordpress sh -c "cd /var/www/html/wp-content/themes/devoted && npm ci && npm run build"`.
