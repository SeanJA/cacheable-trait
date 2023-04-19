FROM php:8.2-cli

COPY coverage.php /coverage.php

ENTRYPOINT ["php", "/coverage.php"]