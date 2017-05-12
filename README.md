# web-rsync

[![Build Status](https://travis-ci.org/nipil/web-rsync.svg?branch=master)](https://travis-ci.org/nipil/web-rsync)
[![Coverage Status](https://coveralls.io/repos/github/nipil/web-rsync/badge.svg?branch=master)](https://coveralls.io/github/nipil/web-rsync?branch=master)

rsync-like tool, working over http

# Install PHP 7.0

This version is available directly on Ubuntu 16.04 LTS and Debian Stretch

    sudo apt-get install php7.0-cli

# Get composer

Either install via your distribution :

    sudo apt-get install composer

Or get composer directly :

- go to https://getcomposer.org/download/
- follow directions and obtain a file `composer.phar`

# Extensions required for developpement only

- phpunit requires ext-dom and ext-mbstring
- coveralls requires ext-curl

And if you want to get test-coverage locally, ext-xdebug is required too.
