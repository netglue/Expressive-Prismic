# WIP: Zend Expressive / Prismic.io CMS Module

## Introduction

This module/library's purpose is to ease development of content driven websites using prismic.io's content API as a backend service.

If you haven't heard of Prismic before, you can [find out about it here](https://prismic.io).

Mostly, this library for Zend Expressive, is very 'Zendy', in that some of the really useful stuff like view helpers are for Zend\View and there's not equivalents if you're happier with Twig/Plates/Blade etc. It'd be great to have equivalents, but personally, I tend to use Zend\View so it's my first port of call… _(Edit: Zend\View is a requirement as the middleware for setting off experiments and previews etc use Zend's View Helpers to do the job)_

## Work in progress

This module is intended to provide the most basic requirements for working with Prismic, but there's another module in progress that provides more view helpers, ready to go full-text search and a bunch of other more opinionated stuff called `expressive-prismic-defaults`. You [can find it here](https://github.com/netglue/Expressive-Prismic-Defaults). It's the kind of stuff we use when [we're making customer websites](https://netglue.uk)…

## Install

Install with composer ala `composer require netglue/expressive-prismic`

This should also ask you if you want to inject the config provider too.


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

## Link Resolver

The Link Resolver is a concept introduced by Prismic to turn documents, or document link fragments into local urls and there's a concrete implementation in this package at `ExpressivePrismic\LinkResolver`.

Using the same setup for routing parameters, it tries to use the Expressive URL helper to generate local URLs. It's setup in the container as `Prismic\LinkResolver` as well as `ExpressivePrismic\LinkResolver` and throughout the package it's retrieved by the name of `Prismic\LinkResolver` so it's easy to replace with your own concrete implementation if you need one.

## Previews

There's another route that's auto-wired like the cache busting webhook for initiating previews. All you have to do is add the URL in the settings on your Prismic repository and clicks on the preview button in the writing room will put the site in preview mode. You can see how this is configured in `Factory\PipelineAndRoutesDelegator` - the URL is `/prismic-preview`

## View Helpers

### URL Helper `$this->prismicUrl()`

### Fragment Helper `$this->fragment()`

## CMS Managed Error Pages for Production

**Error handling is not wired in by default**

If you want to use Prismic to manage your 404's and server errors, there's a bunch of stuff you'll need to do.

### 1. Normalize 404's to Exceptions

First of all, you need to change the default `Zend\Expressive\Middleware\NotFoundHandler` that is usually piped at the end of your middleware pipline to the provided `ExpressivePrismic\Middleware\NormalizeNotFound` - effectively all this does is throw an exception if it is reached and you'd configure your pipeline something like this:

    use ExpressivePrismic\Middleware\NormalizeNotFound;
    
    /**
     * Setup middleware pipeline:
     */
    $app->pipe( /* Default Error Handler */);
    $app->pipe(ServerUrlMiddleware::class);
    
    // ... etc ...
    
    // Register the dispatch middleware in the middleware pipeline
    $app->pipeDispatchMiddleware();
    
    // Finally your 404 handler which should be our NormalizeNotFound middleware
    $app->pipe(NormalizeNotFound::class);

### 2. Change the Error Response Generator

By default, the `ErrorResponseGenerator` will be aliased to the default templated one shipped with Expressive, or in development, you might be using the Whoops version. To change it, configure `Zend\Expressive\Middleware\ErrorResponseGenerator` to point to `ExpressivePrismic\Middleware\Factory\ErrorHandlerFactory` in your dependency config something like this:

    use Zend\Expressive\Middleware\ErrorResponseGenerator;
    use ExpressivePrismic\Middleware\Factory\ErrorHandlerFactory;
    
    return [
        'dependencies' => [
            'factories' => [
                ErrorResponseGenerator::class => ErrorHandlerFactory::class
            ],
        ],
    ];

This factory will return an instance of `ExpressivePrismic\Middleware\ErrorHandler` which simply inspects the error to see if it specifically a 404 exception, or some other kind of error and switches the template to suit not before locating the correct bookmarked prismic document from the api to use for the content of the page. There is also a fallback mechanism to render an HTML page if something goes pear-shaped whilst this is going on.

### 3. Configure the Error Handler
    
    return [

        'prismic' => [
            'error_handler' => [
                // The names of 2 templates to render for 404's and 500 errors
                'template_404'      => 'error::404',
                'template_error'    => 'error::error',
                // The layout and template to render when an exception is thrown trying to render the error documents
                'template_fallback' => 'error::prismic-fallback',
                'layout_fallback'   => 'layout::error-fallback',
                // The bookmarks for the Prismic.io documents used to render the 404 and 500 errors
                'bookmark_404'      => null,
                'bookmark_error'    => null,
                // Used to create the middleware Pipe that the error requests goes through prior to rendering
                'middleware' => [
                    Middleware\ExperimentInitiator::class,
                    Middleware\InjectPreviewScript::class,
                ],
            ],
        ],
    ];

When an error occurs, the request is piped through a configurable middleware pipe so you can add in exception logging middleware for example prior to rendering the page. Chuck whatever middleware you want in there by providing the name of middleware that can be retrieved from the container and have a look at the source to see how it all works.


