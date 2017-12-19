# Zend Expressive / Prismic.io CMS Module

## Introduction

This module/library's purpose is to ease development of content driven websites using prismic.io's content API as a backend service.

If you haven't heard of Prismic before, you can [find out about it here](https://prismic.io).

## Requirements

This module is only suitable for Zend Expressive ^2.0 and PHP ^7.1

## Install

Install with composer ala `composer require netglue/expressive-prismic`

This should also ask you if you want to inject the config provider too.

## Tests

    $ composer install
    $ vendor/bin/phpunit

## Basic Configuration

This library exposes the Prismic API instance in your container as `Prismic\Api`. At the very least, you'll need to configure your credentials thus:
    
    return [
        'prismic' => [
            'api' => [
                'token' => 'Permanent Access Token',
                'url' => 'https://Repo-name.prismic.io/api',
            ],
        ],
    ];

## Defining Routes

In order to allow you to specify properties of a document to look out for during routing, you must map the route parameter names you want to use to the prismic document/api equivalent. The defaults are:
    
    'prismic' => [
        'route_params' => [
            'id'       => 'prismic-id',
            'bookmark' => 'prismic-bookmark',
            'uid'      => 'prismic-uid',
            'type'     => 'prismic-type',
            'lang'     => 'prismic-lang',
        ],
    ],

So, assuming the above, to define a route to a bookmarked document, you would configure something like this:
    
    'routes' => [
        'home' => [
            'name' => 'home',
            'path' => '/',
            'allowed_methods' => ['GET'],
            'middleware' => [ /* ... */ ],
            'options' => [
                'defaults' => [
                    'template' => 'my:home-page',
                    'prismic-bookmark' => 'home',
                ],
            ],
        ],
    ],

Normally, to save yourself some effort, you'd have a template that's capable of rendering perhaps any page of a given type such as a 'case-study' type. Let's say you want the url `/case-studies/{case-study-uid}`, then you'd define a route like this _(If you are using FastRoute)_:
    
    'routes' => [
        'case-studies' => [
            'name' => 'case-studies',
            'path' => '/case-studies/{prismic-uid}',
            'allowed_methods' => ['GET'],
            'middleware' => [ /* ... */ ],
            'options' => [
                'defaults' => [
                    'template' => 'my:case-study',
                    'prismic-type' => 'case-study',
                ],
            ],
        ],
    ],



## Cache Busting Webhook

You will be able to see in `Factory\PipelineAndRoutesDelegator` that two routes are wired in by default, one of these is the webhook to bust the cache. In order to use it, you will need to set the shared secret that Prismic.io sends in it's webhook payload like this:

    return [
        'prismic' => [
            'webhook_secret' => 'SomeSuperToughSharedSecret',
        ],
    ];

The Url of the webhook will be `/prismicio-cache-webhook` - given a valid Json payload containing a matching shared secret, the pre-configured middleware will empty the cache attached to the Prismic API instance.

The webhook route points to a middleware pipe named `ExpressivePrismic\Middleware\WebhookMiddlewarePipe` so if you want to modify the pipeline to do other things, or replace it entirely, just alias that pipe to different factory or implement a delegator factory for the pipe.

## Link Resolver

The Link Resolver is a concept introduced by Prismic to turn documents, or document link fragments into local urls and there's a concrete implementation in this package at `ExpressivePrismic\LinkResolver`.

Using the same setup for routing parameters, it tries to use the Expressive URL helper to generate local URLs. It's setup in the container as `Prismic\LinkResolver` as well as `ExpressivePrismic\LinkResolver` and throughout the package it's retrieved by the name of `Prismic\LinkResolver` so it's easy to replace with your own concrete implementation if you need one.

## Previews

There's another route that's auto-wired like the cache busting webhook for initiating previews. All you have to do is add the URL in the settings on your Prismic repository and clicks on the preview button in the writing room will put the site in preview mode. You can see how this is configured in `Factory\PipelineAndRoutesDelegator` - the URL is `/prismic-preview`

## View Helpers

### URL Helper `$this->prismicUrl()`

This view helper will generate a local URL using the link resolver. It's `__invoke()` method accepts

* string - Treated as a Document ID
* \Prismic\Document
* \Prismic\Fragment\Link\LinkInterface


### Fragment Helper `$this->fragment()`

This view helper operates on the current resolved document and provides an easy way of rendering simple fragments to views. It does not require the fully qaulified fragment name, ie. `documentType.fragmentName` and instead you can pass it just `'fragmentName'`.

`$this->fragment()->get('title');` will return the fragment object.

`$this->fragment()->asText('title');` will return the text value of the fragment.

`$this->fragment()->asHtml('title');` will return the HTML value of the fragment.

## CMS Managed Error Pages for Production

**Error handling is wired in by default**

### 404 Errors

In the event of a 404, Expressive will execute the default 'not found delegate' which consists of a single `NotFoundHandler` middleware. This module replaces the `NotFoundHandler` with a custom middleware pipe that initialises previews and experiments, locates a bookmarked error document in the Prismic API and renders that document to a template.

All you have to do to take advantage of pretty CMS managed 404s is to specify the bookmark name for the error document in your repository and specify the template name to render like this:

    return [
        'prismic' => [
            'error_handler' => [
                'template_404'   => 'some::template-name',
                'bookmark_404'   => 'some-bookmark',
            ],
        ],
    ];

The pipeline is retrieved from the container using the alias `ExpressivePrismic\Middleware\NotFoundPipe` and by default, a factory is registered to return a suitable pipeline. You can override the pipeline either by changing the alias to point at your own factory which should return a `Zend\Stratigility\MiddlewarePipe` or by providing an array of middleware class names in config like this:
    
    return [
        'prismic' => [
            'error_handler' => [
                'middleware_404' => [
                    \ExpressivePrismic\Middleware\InjectPreviewScript::class,
                    \ExpressivePrismic\Middleware\ExperimentInitiator::class,
                    \ExpressivePrismic\Middleware\NotFoundSetup::class,
                    \\SomeOtherMiddleware-ToRun-Before-Template-Is-Rendered…
                    \ExpressivePrismic\Middleware\PrismicTemplate::class,
                ],
            ],
        ],
    ];

There is an additional config key for 404 errors that determines what should happen if the 404 document cannot be retrieved from the Prismic API. This boolean when false means that an exception will be thrown if the document cannot be resolved _(Default behaviour)_. Setting the value to true will fall back to the default 404 rendering provided by Zend Expressive.

    return [
        'prismic' => [
            'error_handler' => [
                'render_404_fallback' => true, // or false
            ]
        ]
    ]

### Exceptions

Exceptions are handled in much the same way. We need to know the bookmark and template, and the pipeline can be overridden in the same way but obviously, the keys are different:

    return [
        'prismic' => [
            'error_handler' => [
                'template_error'   => 'some::template-name',
                'bookmark_error'   => 'some-bookmark',
                'middleware_error' => [
                    \ExpressivePrismic\Middleware\InjectPreviewScript::class,
                    \ExpressivePrismic\Middleware\ExperimentInitiator::class,
                    \ExpressivePrismic\Middleware\PrismicTemplate::class,
                ],
            ],
        ],
    ];

The fallback _(i.e. when the error document cannot be retrieved from the api)_ for exception situations is a simple plain text message stating that an error occurred. This fallback is not currently configurable to be anything more fancy.

The pipeline for errors is retrieved from the container using `'ExpressivePrismic\Middleware\ErrorHandlerPipe'`.




