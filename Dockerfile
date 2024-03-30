FROM php:8.3-cli

COPY coverage.php /coverage.php

ENTRYPOINT ["php", "/coverage.php"]