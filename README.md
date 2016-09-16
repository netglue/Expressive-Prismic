# WIP: Zend Expressive / Prismic.io CMS Module

## Introduction

This module/library's purpose is to ease development of content driven websites using prismic.io's content API as a backend service.

If you haven't heard of Prismic before, you can [find out about it here](https://prismic.io).

Mostly, this library for Zend Expressive, is very 'Zendy', in that some of the really useful stuff like view helpers are for Zend\View and there's not equivalents if you're happier with Twig/Plates/Blade etc. It'd be great to have equivalents, but personally, I tend to use Zend\View so it's my first port of call…

## Work in progress

This module is intended to provide the most basic requirements for working with Prismic, but there's another module in progress that provides more view helpers, ready to go full-text search and a bunch of other more opinionated stuff called `expressive-prismic-defaults`. It's the kind of stuff we use when [we're making customer websites](https://netglue.uk)…



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

In order to allow you to specify properties of a document to look out for during routing, you must map the route parameter names you want to use to the prismic document/api equivallent. The defaults are:
    
    'prismic' => [
        'route_params' => [
            'id'       => 'prismic-id',
            'bookmark' => 'prismic-bookmark',
            'uid'      => 'prismic-uid',
            'type'     => 'prismic-type',
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

Normally, to save youself some effort, you'd have a template that's capable of rendering perhaps any page of a given type such as a 'case-study' type. Let's say you want the url `/case-studies/{case-study-uid}`, then you'd define a route like this:
    
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

## Link Resolver

## Previews

## View Helpers

### URL Helper `$this->prismicUrl()`

### Fragment Helper `$this->fragment()`

## CMS Managed Error Pages for Production

Comments and suggestions for the error handling strategy chosen are welcome! This is my first attempt at error handling with expressive beyond just using whatever final handler is provided by default…

An invokable middleware is provided in `ExpressivePrismic\Middleware\NormalizeNotFound` who's sole job is to throw a specific type of exception if the response meets the following criteria:

* 200 status
* Empty response body

When these conditions are met, an `ExpressivePrismic\Exception\PageNotFoundException` is thrown.

This middleware must be registered in the middleware pipeline after routing and dispatch and before any error middleware, something like this:
    
    // ...
    'error_normalizer' => [
        'middleware' => [
            ExpressivePrismic\Middleware\NormalizeNotFound::class,
        ],
        'priority' => -1000,
    ],
    // ...

Next, there's a middleware called `ExpressivePrismic\Middleware\ErrorHandler` that will retrieve the appropriate error document from the Prismic API and render it to the specified template. This handler must also be registered as error middleware in the middleware pipeline something like this:

    // ...
    'error' => [
        'middleware' => [
            ExpressivePrismic\Middleware\ErrorHandler::class,
        ],
        'error'    => true,
        'priority' => -9999,
    ],
    // ...

Configuration for the error handler requires that you provide bookmarks for the error documents and templates that can render the documents according to your design:
    
    return [

        'prismic' => [
            'error_handler' => [
                'template_404'   => 'error::404',
                'template_error' => 'error::error',
                'bookmark_404'   => 'error-404',
                'bookmark_error' => 'error-500',
            ],
        ],
    ];


