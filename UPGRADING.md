# Upgrade Guide

## Version 1 to 2
***

Version 2 focuses on simplifying the classes and increasing test coverage.

* Minimum PHP version has been bumped to `7.4` to match the upcoming framework changes
* All properties that can be typed have been
* The `Model` and Firestore classes have been completely changed; see **Firestore** below
* The test traits have been reworked to improve performance and have less "automatic" handling; call necessary methods yourself with `setUp()` and `tearDown()`

### Firestore

Version 2 moves away from a framework-style "database layer", providing instead thin access
to Google's Firestore classes implemented with CodeIgniter Entities.

* `Entity` has moved to the new namespace `Tatter\Firebase\Firestore`
* `Model` no longer exists but is conceptually replaced by `Collection`; all class extensions will need to be replaced
