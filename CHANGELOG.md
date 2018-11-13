# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 4.1.0 - TBD

### Added

- `CliCacheBust` Middleware that forces a reload of the Prismic Api data during a cache busting webhook, if, the app
 is running on the cli. This middleware is wired into the Webhook pipeline by default as is essentially a no-op in a
 regular, non-cli environment. It’s been added to improve compatibility with Swoole

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 4.0.1 - 2018-09-27

### Added

- Nothing.

### Changed

- Change the route matcher factory to retrieve the route collector instead of the application. Getting the application
 can cause cyclic dependency issues.
- Re-write RouteMatcher to inspect runtime value of routes in the collector rather than iterating once over a copy of
the available routes at the time of instantiation - This improves compatibility with CLI environments

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 4.0.0 - 2018-06-12

### Added

- Nothing.

### Changed

- Switch to a completely different [Fork of the Prismic SDK](https://github.com/netglue/prismic-php-kit).
- All middleware switched to PSR-15
- Reorganised factories into `\ExpressivePrismic\Container`
- Add marker interfaces for common, useful pipelines
- Remove the flag as to whether to render a fallback 404 template. Either we want to render CMS 404's or we don't,
 everything else is an exception
- Simplify the error response generator to work with a named pipe that takes care of all response generation
- Lots of other breaking changes…

### Deprecated

- Nothing.

### Removed

- Removed redundant NormalizeNotFound middleware.

### Fixed

- Nothing.

## 3.0.5 - 2018-03-22

### Added

- Nothing.

### Changed

- Restricting to expressive router 2.3.* to avoid deprecation notices and segfaults during testing. We'll be bringing
out a new major version for Expressive 3.0

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.0.4 - 2018-02-20

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- The JS on the CDN has renamed the object used to call startExperiment from 'prismic' to 'PrismicToolbar', it also now
loads the required JS from google analytics if it's not already been included so there's no need for it anymore

## 3.0.3 - 2018-02-06

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- The NotFound middleware now correctly sets the status code to 404.

## 3.0.2 - 2018-02-06

### Added

- Add a flag to the constructor of InjectPreviewScript middleware that defines whether the JS should be injected on
every request in order to facilitate the Edit Button functionality. This is off by default to preserve BC

### Changed

- Nothing

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Updated the preview cookie to include the domain to get around an issue with the default JS script. Also, sets the
cookie to secure if the current scheme is https and explicitly sets http only to false as the cookie is read by the JS

## 3.0.1 - 2017-12-19

### Added

- Nothing.

### Changed

- Re-Worked the webhook pipeline splitting up the Single `ApiCacheBust` middleware into several smaller middleware.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.0.0 - 2017-12-19

### Added

- Introduce pre-configured middleware pipe for webhooks.
- Introduce pre-configured middleware pipe for not found/404 errors.
- Introduce pre-configured middleware pipe for exceptions (Error handler).
- General improvements in test coverage
- Added Route matcher as part of refactoring Link Resolver

### Changed

- Minimum version of PHP is now 7.1.
- Switched to `Psr\Container`

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.0.1 - 2017-11-21

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Allow single routes to match multiple document types
- Fix missing `$api` property in `Url` view helper 

## 2.0.0 - 2017-03-29

### Added

- Nothing.

### Changed

- Pretty much everything. This release provides compatibility with Zend Expressive 2.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.0 - 2017-03-13

### Added

- Everything.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.