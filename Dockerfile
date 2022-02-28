# Select Image Base
FROM laravel:php-8.0
# Set working directory
WORKDIR /var/www
ARG USER=laravel


# Copy composer.lock and composer.json
# COPY composer.lock composer.json /var/www/

# RUN composer install --no-scripts --no-autoloader

# Copy existing application directory permissions
COPY --chown="$USER":"$USER" . /var/www

COPY ./.docker/supervisor/supervisor.conf /etc/supervisor/ 
COPY ./.docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./.docker/php/php.ini-development /usr/local/etc/php/php.ini
RUN find /var/log/nginx -type d -exec chmod 775 {} \; && \
    find /var/log/nginx -type d -exec chown -R "$USER":"$USER" {} \; && \
    find /var/lib/nginx -type d -exec chmod 775 {} \; && \
    find /var/lib/nginx -type d -exec chown -R "$USER":"$USER" {} \; && \
    mkdir /var/tmp/nginx && \
    mkdir /var/tmp/nginx/client_body && \ 
    find /var/tmp/nginx/client_body -type d -exec chmod 775 {} \; && \
    find /var/tmp/nginx/client_body -type d -exec chown -R "$USER":"$USER" {} \; && \
    find /run -exec chmod 775 {} \; && \
    find /run -exec chown -R "$USER":"$USER" {} \;



RUN mv .env.example .env && \
    # composer dump-autoload --optimize && \
    # php artisan key:generate && \
    find . -type d -exec chmod 775 {} \; && \
    find . -type f -exec chmod 664 {} \; && \
    find ./.docker/run/run.sh -exec sed -i -e 's/\r$//' {} \; && \
    find ./.docker/run/run.sh -exec chmod 775 {} \; && \
    find . -exec chown -R "$USER":"$USER" {} \;
    

#Change current user to $USER
USER $USER
EXPOSE 8080
CMD ["/var/www/.docker/run/run.sh"]
