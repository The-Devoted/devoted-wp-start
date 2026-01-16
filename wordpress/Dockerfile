# Get the WP image to use from environment variable.
ARG wpimage="wordpress:php8.2-apache"

# Get the base image.
FROM ${wpimage}

# Install Composer from Docker image and update it.
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer
RUN composer self-update

# Strip WP's default plugins and starter themes.
RUN rm -rf /usr/src/wordpress/wp-content/plugins/akismet \
  /usr/src/wordpress/wp-content/plugins/hello.php \
  /usr/src/wordpress/wp-content/themes/twentytwentytwo \
  /usr/src/wordpress/wp-content/themes/twentytwentythree \
  /usr/src/wordpress/wp-content/themes/twentytwentyfour \
  /usr/src/wordpress/wp-content/themes/twentytwentyfive
