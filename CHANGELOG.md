# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.4.1 - 2020-06-30
### Changed
- 'localhost' is a default ignored host

## 0.4.0 - 2020-06-30
### Added
- Added the ability to handle domain based routing

## 0.3.2 - 2020-06-30
### Changed
- Factored out CachedCollector::generateRoutes into its own method

### Fixed
- Nested groups were not creating the correct routes

## 0.3.1 - 2020-03-16
### Changed
- Factory::build() accepts CachedCollector as an argument.

## 0.3.0 - 2020-03-16
### Added
- Middleware can now be parameterized, when it implements the ParameterAware interface.

## 0.2.4 - 2020-03-11
### Fixed
- Made the RouteParserInterface public in the Symfony DI

## 0.2.3 - 2020-03-11
### Added
- InvalidRoute exception when the route is not found

### Changed
- RouteCollector will now call the container when getRouteParser is called
- Changed the order of handling middleware in MiddlewareDispatcher

### Fixed
- Bug with MiddlewareDispatcher calling has() on the container without a container being set.

## 0.2.2 - 2020-02-17
### Fixed
- Bug with Routing collection removing routes when the route name changes.

### Changed
- Moved the route name watching in to the CachedCollector, since the RouteCollector should only receive the cached routes.

## 0.2.1 - 2020-01-29
### Fixed
- Bug with route / routing results attributes not using the correct attribute name on the request.

## 0.2.0 - 2020-01-28
### Fixed
- Updated groups not receiving their full pattern.

### Changed
- Switched to Laminas Diactoros, Zend Diactoros is deprecated.
- Allow middlewares/utils 3.x to be used.

## 0.1.0 - 2019-09-18

- Initial release
