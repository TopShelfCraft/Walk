# _Walk_ Plugin Changelog

The format of this file is based on ["Keep a Changelog"](http://keepachangelog.com/). This project adheres to [Semantic Versioning](http://semver.org/). Version numbers follow the pattern: `MAJOR.FEATURE.BUGFIX`


## 4.0.0 - 2022-05-15

### Improved

- Walk is ready for Craft 4!

### Removed

- Removed `Walk::$plugin` static accessor; use `getInstance()` instead.


## 3.0.0 - 2020-03-20

### Improved

- Improved documentation, including missing callables in third-party element examples.


## 3.0.0-rc.1 - 2020-01-08

### Added

- Improved documentation, including instructions for custom/third-party element queries. 


## 3.0.0-beta.3 - 2020-01-08

### Changed

- Refactored the logic for instantiating element queries. (You can now directly specify your element and query classes of choice.)      


## 3.0.0-beta.2 - 2018-10-22

### Changed

- Updated the `maxPowerCaptain` call to use the current Craft 3 `App` helper.
- Rewrote the task infrastructure to use the new Craft 3 queue/jobs system.
- Re-released under the Craft license.


## 3.0.0-beta.1 - 2018-10-19

### Changed

- Re-released under canonical package name: `topshelfcraft/walk`


## 3.0.0-beta.1 - 2018-02-01

### Added

- Initial release!
