### builder image
FROM ubuntu:latest as builder
WORKDIR /app

# install workspace dependencies
RUN export DEBIAN_FRONTEND=noninteractive; \
    apt-get update && apt-get install -y --no-install-recommends gnupg unzip curl php && \
    curl -sL https://deb.nodesource.com/setup_10.x | bash - && \
    apt-get install -y nodejs npm && \
    curl https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

RUN mkdir /app/js /app/watson-conversation -p
COPY ["./composer.json", "./composer.lock", "/app/"]
COPY ["./js/package.json", "./js/package-lock.json", "/app/js/"]

# install Node libraries and build web-client
RUN cd ./js && npm install
# install PHP libraries
RUN composer install

COPY . .
RUN cd ./js && npm run build

### release image
FROM alpine:latest

WORKDIR /app

RUN mkdir /app/dist -p
COPY docker-entrypoint.sh /
COPY --from=builder /app/watson-conversation /app/watson-conversation
VOLUME /app/dist
ENTRYPOINT ["/docker-entrypoint.sh"]