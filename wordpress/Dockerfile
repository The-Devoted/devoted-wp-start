# Get the WP image to use from environment variable.
ARG wpimage="wordpress:php8.2-apache"

# Get the base image.
FROM ${wpimage}

# Install Composer from Docker image and update it.
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer
RUN composer self-update

# Install Node.js so custom blocks (authored in TypeScript under the theme's
# `src` directory) can be compiled with @wordpress/scripts inside the container.
COPY --from=node:22-trixie-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-trixie-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
  && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

# Strip WP's default plugins and starter themes.
RUN rm -rf /usr/src/wordpress/wp-content/plugins/akismet \
  /usr/src/wordpress/wp-content/plugins/hello.php \
  /usr/src/wordpress/wp-content/themes/twentytwentytwo \
  /usr/src/wordpress/wp-content/themes/twentytwentythree \
  /usr/src/wordpress/wp-content/themes/twentytwentyfour \
  /usr/src/wordpress/wp-content/themes/twentytwentyfive
