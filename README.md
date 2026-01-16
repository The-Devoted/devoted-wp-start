# Devoted WP Start

[The Devoted](https://www.the-devoted.com/)'s starter kit for WordPress.

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

<i>*Specific versions or tags for Docker images are configured via environment variables.</i>

## Deployment

When commits are made to the `[main]` branch, changes are automatically deployed to a Digital Ocean Droplet at ADD_DROPLET_URL_AFTER_SETUP.

The deployment action does the following:

1. Navigates to the repo directory.
2. Checks out the main branch and pulls.
3. Stops Docker, then restarts it in detached mode.
4. Runs a Composer update within the WordPress container `docker exec wordpress composer update`.

### Container Changes on Remote

The deployment workflow does **not** currently check for changes to `Dockerfile`s, nor will it automatically rebuild the containers on the remote if necessary.

If your update makes changes to container config, you must rebuild on the remote manually:

1. SSH to the remote: `ssh devoted-wp-start@host_goes_here`
2. Stop the containers: `docker compose down`
3. Rebuild containers from scratch: `docker compose build --no-cache`
4. Restart the containers in detatched mode with no cache: `docker compose up -d --force-recreate`
5. Check for Composer/dependency changes `docker exec wordpress composer update`

## Local Development

tk_local_notes
