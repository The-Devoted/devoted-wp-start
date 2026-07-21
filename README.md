# Devoted WP Start

💚 [The Devoted](https://www.the-devoted.com/)'s starter kit for WordPress.

💻 This project is hosted at
[devoted-wp-start.the-devoted.dev](https://devoted-wp-start.the-devoted.dev/).

✅ View open issues and tasks on the [Project
Board](https://github.com/orgs/The-Devoted/projects/3).

📑 See the [Wiki](https://github.com/The-Devoted/devoted-wp-start/wiki) for detailed usage instructions.

----

## Development Environment and Technologies

This project uses a [Docker](https://www.docker.com) development environment.
Using `docker compose` we create the following technology stack:

| Component | Software | Image or Source* |
|---|---|---|
| CMS | [WordPress](https://wordpress.org) | [Official WP Docker Images](https://hub.docker.com/_/wordpress) |
| Database | [MariaDB](https://mariadb.org) | [Official MariaDB Docker Images](https://hub.docker.com/_/mariadb) |
| Database GUI | [phpMyAdmin](https://www.phpmyadmin.net) | [Official phpMyAdmin Docker Images](https://hub.docker.com/_/phpmyadmin) |
| Package Management | [Composer](https://getcomposer.org/) | [Composer Docker Images](https://hub.docker.com/r/composer/composer): same as official but with faster releases |
| Testing Environtment | [Tugboat](https://www.tugboatqa.com/) | *N/A* |


*Specific versions or tags for Docker images are configured via environment
variables.*

## Getting Started

### Prerequisites

- [Docker](https://www.docker.com) and Docker Compose (Docker Desktop on
  macOS/Windows, or Docker Engine on Linux).
- [Git](https://git-scm.com/).
- Credentials for the [ACF Pro](https://www.advancedcustomfields.com/pro/) and
  [WP Engine](https://wpengine.com/) Composer repositories if you need to install
  the premium plugins (Advanced Custom Fields Pro).

### Setup

1. **Clone the repository.**

   ```sh
   git clone https://github.com/The-Devoted/devoted-wp-start.git
   cd devoted-wp-start
   ```

2. **Create your environment file.** Copy the template and edit the values.

   ```sh
   cp .env.template .env
   ```

   At a minimum, set the following in `.env`:

   | Variable | Description |
   |---|---|
   | `PROJECT_NAME` | A name for your project. |
   | `WORDPRESS_DB_NAME` | Database name (e.g. `wordpress`). |
   | `WORDPRESS_DB_HOST` | Database host — use `db` to match the Compose service. |
   | `WORDPRESS_DB_USER` | Database username. |
   | `WORDPRESS_DB_PASSWORD` | Database password. |
   | `WORDPRESS_THEME_NAME` | Theme to activate (defaults to `devoted`). |

   The `DOCKER_*` variables pin the WordPress, MariaDB, and phpMyAdmin image
   versions. The `DO_DROPLET_*` and `DOMAIN_NAME` variables are only needed for
   deployment and Tugboat previews.

3. **Build and start the stack.**

   ```sh
   docker compose up -d --build
   ```

4. **Complete the WordPress install.** Browse to
   [http://localhost:8000](http://localhost:8000) and follow the WordPress setup
   wizard the first time you run the stack.

Once running, the following services are available:

| Service | URL |
|---|---|
| WordPress site | [http://localhost:8000](http://localhost:8000) |
| phpMyAdmin | [http://localhost:8080](http://localhost:8080) |

## Development

The `wp-content` directory is bind-mounted into the container, so edits to the
theme are reflected immediately — no rebuild required. The Compose file also sets
`WP_ENVIRONMENT_TYPE=development` and `WP_DEVELOPMENT_MODE=theme`, which disables
theme caching so template and style changes appear on refresh.

Theme source lives in `wp-content/themes/devoted/`:

| Path | Purpose |
|---|---|
| `theme.json` | Global styles and settings (colors, typography, spacing). |
| `templates/` | Block templates (`index`, `single`, `page`, `archive`, `404`, etc.). |
| `parts/` | Reusable template parts (header, footer, page header, post meta). |
| `assets/` | Block stylesheets and fonts. |
| `functions.php` | Theme setup, allowed blocks, block styles, enqueues. |

### Common tasks

- **Edit the theme:** change files in `wp-content/themes/devoted/` and refresh
  the browser.
- **View logs:** `docker compose logs -f wordpress`
- **Open a shell in the container:** `docker exec -it wordpress bash`
- **Stop the stack:** `docker compose down` (add `-v` to also remove the
  database and WordPress volumes for a clean slate).

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

### Managing plugins and dependencies

Plugins are managed with [Composer](https://getcomposer.org/) via
`composer.json` rather than committed to the repo. To add or update a plugin:

1. Add the package to the `require` section of `composer.json` (WordPress
   plugins come from [wpackagist](https://wpackagist.org/)).
2. Update dependencies inside the running container:

   ```sh
   docker exec wordpress composer update
   ```

Installing the ACF Pro package requires a valid license/auth token (via
`auth.json`, which is gitignored).

### Rebuilding the containers

Changes to `docker-compose.yml`, the `wordpress/Dockerfile`, or the
`DOCKER_*` image variables require a rebuild:

```sh
docker compose down
docker compose build --no-cache
docker compose up -d --force-recreate
```

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
