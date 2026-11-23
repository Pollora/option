# Changelog

All notable changes to this package are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Fixed

- `set()` and `update()` no longer switch an option's autoload from off back to on: the current autoload is kept unless
  one is set explicitly on the `Option` (#31).

### Changed

- `Domain\Model\Option::$autoload` is now `?bool` and defaults to `null` (no explicit choice) instead of `true`. A new
  option is still stored with autoload on; `update()` passes `null` to `update_option()`, which keeps the stored value.
