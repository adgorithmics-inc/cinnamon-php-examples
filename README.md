# Cinnamon PHP User Stories

> A collection of commented workflows to document the client-side usage of Cinnamon.

## Requirements

- A working copy of Cinnamon running either locally or remotelly.
- [Composer](https://getcomposer.org/).

### Running the stories from command line

First you need to install the dependencies via composer:

```shell
composer install
```

Then, export the required environment variables (you may need to ask for a Cinnamon user first):


```shell
export CINNAMON_ENDPOINT=http://localhost:4000/_graphql
export CINNAMON_USER=your_cinnamon@user.com
export CINNAMON_PASSWORD=your_password
```

And then run the stories:

```shell
php stories/*
``` 
