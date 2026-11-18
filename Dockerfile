FROM php:8.4-cli-alpine3.22

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

USER 1000:1000

WORKDIR /app

CMD [ "vendor/bin/phpunit", "--coverage-html", "tests_coverage" ]

# Other useful options:
#   --filter
# 	--coverage-clover=tests_coverage/coverage.xml
# 	--log-junit=tests_coverage/phpunit-execution-result.xml
