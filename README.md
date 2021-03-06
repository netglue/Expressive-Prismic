# Zend Expressive / Prismic.io CMS Module

[![Build Status](https://api.travis-ci.org/netglue/Expressive-Prismic.svg)](https://travis-ci.org/netglue/Expressive-Prismic)
[![Test Coverage](https://api.codeclimate.com/v1/badges/d05cb66580d760d6d45e/test_coverage)](https://codeclimate.com/github/netglue/Expressive-Prismic/test_coverage)

## Introduction

This module/library's purpose is to ease development of content driven websites using prismic.io's content API as a
 backend service.

If you haven't heard of Prismic before, you can [find out about it here](https://prismic.io).

## Requirements

This module is only suitable for Zend Expressive ^3.0 and PHP ^7.1

Furthermore, it uses a fork of the official Prismic.io php library which you can see here:
[netglue/prismic-php-kit](https://github.com/netglue/prismic-php-kit). This fork is quite different to the official
kit and I recommend looking through the docs/code to make yourself aware of the differences if you are already
familiar with the official lib.

## Install

Install with composer ala `composer require netglue/expressive-prismic`

This should also ask you if you want to inject the config provider too.

## Tests

    $ composer install
    $ vendor/bin/phpunit

## Basic Configuration

This library exposes the Prismic API instance in your container as `Prismic\Api`. At the very least, you'll need to
configure your credentials thus:

```php
    return [
        'prismic' => [
            'api' => [
                'token' => 'Permanent Access Token',
                'url' => 'https://Repo-name.prismic.io/api',
            ],
        ],
    ];
```

## Defining Routes

In order to allow you to specify properties of a document to look out for during routing, you must map the route
parameter names you want to use to the prismic document/api equivalent. The defaults are:

```php
    'prismic' => [
        'route_params' => [
            'id'       => 'prismic-id',
            'bookmark' => 'prismic-bookmark',
            'uid'      => 'prismic-uid',
            'type'     => 'prismic-type',
            'lang'     => 'prismic-lang',
        ],
    ],
```

So, assuming the above, to define a route to a bookmarked document, you would configure something like this:

```php
    /**
     * @var \Zend\Expressive\Application $app
     * @var \Zend\Stratigility\MiddlewarePipeInterface $middlewarePipe
     */
    $app->route('/', [$middlewarePipe], ['GET'], 'home')
        ->setOptions([
            'defaults' => [
                'template' => 'page::default',
                'prismic-bookmark' => 'home',
            ],
        ]);
```
Normally, to save yourself some effort, you'd have a template that's capable of rendering perhaps any page of a given
type such as a 'case-study' type. Let's say you want the url `/case-studies/{case-study-uid}`, then you'd define a
route like this _(If you are using FastRoute)_:

```php
    $app->route('/case-studies/{prismic-uid}', [$middlewarePipe], ['GET'], 'case-studies')
            ->setOptions([
                'defaults' => [
                    'template' => 'my:case-study',
                    'prismic-type' => 'case-study',
                ],
            ]);
```

## Cache Busting Webhook

You will see in `Factory\PipelineAndRoutesDelegator` that two routes are wired in by default, one of these is the
webhook to bust the cache. In order to use it, you **should** provide a shared secret that Prismic.io sends in
it's webhook payload to a local configuration file or Config Provider like this:

```php
    return [
        'prismic' => [
            'webhook_secret' => 'SomeSuperToughSharedSecret',
        ],
    ];
```

You would also need to enter this secret into the relevant place in your Prismic repository settings.

> By default the secret is `null`, which means that the secret will not be validated even if it's sent by Prismic.
> If you decide not to setup a secret, I **strongly** recommend that you configure an obscure URL otherwise anyone
> will be able to POST to your webhook endpoint and bust the cache on demand, slowing down your site.

By default the url of the webhook will be `/prismicio-cache-webhook`. You should change this to something sufficiently
random in the following way:

```php
    return [
        'prismic' => [
            'webhook_url' => '/send-prismic-webhooks-here',
        ],
    ];
```

If the webhook url is hit with a POST request and valid JSON payload, the pre-configured middleware will empty the 
cache attached to the Prismic API instance.

The webhook route points to a middleware pipe named `ExpressivePrismic\Middleware\WebhookPipe` so if you want to modify
the pipeline to do other things, or replace it entirely, just alias that pipe to different factory or implement a
delegator factory for the pipe.

## Link Resolver

The Link Resolver is a concept introduced by Prismic to turn documents, or document link fragments into local urls and
there's a concrete implementation in this package at `ExpressivePrismic\LinkResolver`.

Using the same setup for routing parameters, it tries to use the Expressive URL helper to generate local URLs. It's
setup in the container as `Prismic\LinkResolver` as well as `ExpressivePrismic\LinkResolver` and throughout the package
it's retrieved by the name of `Prismic\LinkResolver` so it's easy to replace with your own concrete implementation if
you need one.

## Previews

There's another route that's auto-wired like the cache busting webhook for initiating previews. All you have to do is
add the URL in the settings on your Prismic repository and clicks on the preview button in the writing room will put
the site in preview mode. You can see how this is configured in `Factory\PipelineAndRoutesDelegator` - the URL is
`/prismic-preview` by default, but you can change the url by setting the following configuration value:

```php
    return [
        'prismic' => [
            'preview_url' => '/some-other-endpoint',
        ],
    ];
```

> In 4.2.0, the middleware responsible for initiating a preview session was altered to catch an exception that is
> thrown when the preview token has expired and subsequently return a simple html response with a descriptive error.
> Previously, in this situation, an uncaught exception would have occurred.  

## View Helpers

### URL Helper `$this->prismicUrl()`

This view helper will generate a local URL using the link resolver. It's `__invoke()` method accepts

* string - Treated as a Document ID
* \Prismic\Document
* \Prismic\Document\Fragment\LinkInterface


### Fragment Helper `$this->fragment()`

This view helper operates on the current resolved document and provides an easy way of rendering simple fragments to
views. It does not require the fully qualified fragment name, ie. `documentType.fragmentName` and instead you can pass
it just `'fragmentName'`.

`$this->fragment()->get('title');` will return the fragment object.

`$this->fragment()->asText('title');` will return the text value of the fragment.

`$this->fragment()->asHtml('title');` will return the HTML value of the fragment.

## CMS Managed Error Pages for Production

**Error handling is wired in by default**… If you are using the Whoops error handler in development, you'll need to
disable development mode `composer development-disable` to experience custom errors and 404's

### 404 Errors

In the event of a 404, by default, Expressive will execute the `\Zend\Expressive\Handler\NotFoundHandler`. This module
provides a pipeline in `\ExpressivePrismic\Middleware\NotFoundPipe` that initialises previews and experiments, locates
a bookmarked error document in the Prismic API and renders that document to a template.

To take advantage of pretty CMS managed 404's, first you will have to specify in your configuration the bookmark name
for the error document in your repository and the template name to render like this:

```php
    return [
        'prismic' => [
            'error_handler' => [
                'template_404'   => 'some::template-name',
                'bookmark_404'   => 'some-bookmark',
            ],
        ],
    ];
```

Note that in development mode, 404's experienced after a route has matched, i.e. the Prismic Document can't be found,
will cause a `DocumentNotFound` exception, thereby delegating to the Whoops error handler.

### Exceptions

Presenting a pretty error page during errors and exceptions are handled in much the same way as 404's. Again, you'll
need to configure a bookmark and a template name used to render the content.

```php
    return [
        'prismic' => [
            'error_handler' => [
                'template_error'   => 'some::template-name',
                'bookmark_error'   => 'some-bookmark',
            ],
        ],
    ];
```

The fallback _(i.e. when the error document cannot be retrieved from the api)_ for exception situations is a simple
plain text message stating that an error occurred. This fallback is not currently configurable to be anything more
fancy than that.
