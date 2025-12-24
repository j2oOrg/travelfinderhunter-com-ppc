FROM wordpress:php8.2-apache

# Install MariaDB server alongside WordPress/PHP/Apache plus curl for wp-cli download.
RUN apt-get update \
    && apt-get install -y mariadb-server mariadb-client curl \
    && rm -rf /var/lib/apt/lists/*

# Ensure the MySQL data directory exists and is owned by mysql.
RUN mkdir -p /var/lib/mysql && chown -R mysql:mysql /var/lib/mysql

# Install wp-cli for automated site setup.
RUN curl -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

# Silence Apache ServerName warnings.
RUN echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

# Add the wrapper entrypoint that boots MariaDB then hands off to WordPress.
COPY wp-entrypoint.sh /usr/local/bin/wp-entrypoint.sh
RUN chmod +x /usr/local/bin/wp-entrypoint.sh

# Copy the custom WordPress theme and site icons into the source tree that populates /var/www/html.
COPY wp-content /usr/src/wordpress/wp-content
COPY favicon.ico apple-touch-icon.png /usr/src/wordpress/
COPY wp-setup.conf /usr/src/wordpress/wp-setup.conf
COPY wp-plugins /usr/src/wordpress/wp-plugins

ENTRYPOINT ["wp-entrypoint.sh"]
CMD ["apache2-foreground"]
