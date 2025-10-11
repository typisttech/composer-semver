export GOFLAGS=-mod=mod

.PHONY: vendor
vendor:
	composer install --no-dev --prefer-dist
	composer reinstall --prefer-dist '*'

bin: vendor

phar: vendor
	box compile
	box verify phar/composer-semver
	box info phar/composer-semver
	php phar/composer-semver --version

test-%: %
	export PATH="$(shell pwd)/$*:$(shell echo $$PATH)" && \
	go test -count=1 ./...
