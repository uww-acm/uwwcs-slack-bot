FROM php:5.6-cli
COPY . /bot
WORKDIR /bot
CMD [ "bin/bot" ]
