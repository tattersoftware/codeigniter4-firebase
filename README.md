# Tatter\Firebase
Firebase integration for CodeIgniter 4

[![](https://github.com/tattersoftware/codeigniter4-firebase/workflows/PHPUnit/badge.svg)](https://github.com/tattersoftware/codeigniter4-firebase/actions/workflows/phpunit.yml)
[![](https://github.com/tattersoftware/codeigniter4-firebase/workflows/PHPStan/badge.svg)](https://github.com/tattersoftware/codeigniter4-firebase/actions/workflows/phpstan.yml)
[![](https://github.com/tattersoftware/codeigniter4-firebase/workflows/Deptrac/badge.svg)](https://github.com/tattersoftware/codeigniter4-firebase/actions/workflows/deptrac.yml)
[![Coverage Status](https://coveralls.io/repos/github/tattersoftware/codeigniter4-firebase/badge.svg?branch=develop)](https://coveralls.io/github/tattersoftware/codeigniter4-firebase?branch=develop)

## Quick Start

1. Install with Composer: `> composer require tatter/firebase`
2. Edit **.env** and add the path to your Firebase credentials: `GOOGLE_APPLICATION_CREDENTIALS = ../credentials/keyfile.json`
3. Access components via the service: `$authentication = service('firebase')->auth;`
4. Use the Firestore `Collection` and `Entity` to model your data: `$widget = collection(WidgetCollection::class)->get($widgetId);`

## Description

This is a CodeIgniter 4 integration of `Kreait\Firebase`, the "Unofficial Firebase Admin
SDK for PHP":

* [Documentation](https://firebase-php.readthedocs.io/)
* [GitHub repository](https://github.com/kreait/firebase-php)

It provides a convenience service and custom Firestore classes to use your Firebase project
within CodeIgniter 4. Please pay attention to the requirements of the underlying services:

* [kreait\firebase-php](https://firebase-php.readthedocs.io/en/stable/overview.html#requirements)
* [google\cloud-firestore](https://firebase-php.readthedocs.io/en/stable/cloud-firestore.html)

Notably, you must have the [gRPC PHP extension](https://github.com/grpc/grpc/tree/master/src/php)
and the credentials file to a [Service Account](https://firebase.google.com/docs/admin/setup#add_firebase_to_your_app)
with the *Project -> Editor* or *Project -> Owner* role.

## Installation

Install easily via Composer to take advantage of CodeIgniter 4's autoloading capabilities
and always be up-to-date:
```bash
composer require tatter/firebase
```

Or, install manually by downloading the source files and adding the directory to
**app/Config/Autoload.php**.

> Note: As of February 5, 2022 this library fully supports PHP 8.1, however Google's Protobuf
> has an incompatibility (hopefully fixed soon: https://github.com/protocolbuffers/protobuf/issues/9293).

## Credentials

You must provide a key file with your application's service account credentials. The standard
way to do this is to add **keyfile.json** to your project and edit **.env** to its path
(relative to **public/**):

	GOOGLE_APPLICATION_CREDENTIALS = ../keyfile.json

> *WARNING* Make sure you exclude the key file from any repository updates!

To generate a key file from your Firebase project:

1. Firebase Project Home
2. Project Settings (gear)
3. Service Accounts
4. Firebase Admin SDK
5. Generate new private key

For more info on acquiring credentials see the
[Firestore Quick Start Guide](https://firebase.google.com/docs/firestore/quickstart)

For more information on credential specification see the
[SDK setup docs](https://firebase-php.readthedocs.io/en/stable/setup.html)

## Usage

Load the Firebase service:
```php
$firebase = service('firebase');
```

The service will handle creating and caching each component as you need them. Access
components by their name:
```php
$storage = $firebase->storage;
$bucket  = $storage->getBucket('my-bucket');
```

You can also use the service to access all the functions of `Kreait\Firebase\Factory`
directly, for example if you wanted a separate component instance:
```php
$shareClient = $firebase->auth;
$altClient   = $firebase->createAuth();
```

See the [SDK docs](https://firebase-php.readthedocs.io/en/stable/index.html) for a list of
supported components. Available at the time of this writing:
* Auth
* Database
* Firestore
* Messaging
* RemoteConfig
* Storage
* *Caller*

## Caller

While not yet officially supported by the Firebase SDK, this library includes a component
for Firebase callable functions. A simple example shows all its features:
```php
// Get the component
$caller = service('firebase')->caller;

// Set the UID of the user making the call
$caller->setUid($user->uid);

// Make the call
$data     = ['customerId' => 7, 'charge' => 3.50];
$response = $caller->call('https://us-central1-myproject.cloudfunctions.net/addCharge', $data);

if ($response === null) {
    echo implode(' ', $caller->getErrors());
}
else {
    print_r($response);
}
```

## Firestore

This library provides access to the Firestore database directly via `FirestoreClient`.
Use the helper function for direct access to a shared instance of the client:
```php
helper('firestore');

$document = firestore()->collection('users')->document('lovelace');
$document->set([
    'first' => 'Ada',
    'last' => 'Lovelace',
    'born' => 1815
]);

printf('Added data to the "lovelace" document in the users collection.');
```

Since the SDK and Google classes already represent a full database implementation there is
no need for a framework database layer. You can interact directly with the Firestore classes
according to [Google's User Guide](https://googleapis.github.io/google-cloud-php/#/docs/cloud-firestore/latest).

### Collection

The `Collection` class is inspired by the framework's `Model` and handles most of the pre-
and post-processing that developers are used to. All you need to supply is the name of the
collection and the Entity type to use:
```php
<?php

namespace App\Collections;

use App\Entities\Widget;
use Tatter\Firebase\Firestore\Collection;

final class WidgetCollection extends Collection
{
    public const NAME   = 'widgets';
    public const ENTITY = Widget::class;
}
```

#### Instantiating

You can create your collection instances just like you would a `Model`. The Firestore
Helper also contains a helper function to create and manage shared instances, just like
the framework's `model()` helper:
```
$widgets = new WidgetCollection();
// OR
helper('firestore');
$widgets = collection(WidgetCollection::class);
```

By default a new Collection will create a `CollectionReference` to the top-level collection
matching it's `NAME` constant. Alternatively you may pass in a `CollectionReference` directly
for it to use. Use the second parameter to the `collection()` function to make instant
subcollections of any `Firestore\Entity` or `DocumentReference`:
```php
$user = collection(UserCollection::class)->get($userId);

$userWidgets = collection(WidgetCollection::class, $user);
foreach ($userWidgets->list() as $widget) {
    echo "{$user->name}: {$widget->name}";
}
```

#### Methods

`Collection` supplies the following CRUD methods:
* `add(array|Entity $entity): Entity`
* `update(Entity $entity, array $data): Entity`
* `remove(Entity|string $entity): void`
* `get(DocumentReference|string $reference): ?Entity`
* `list($source = null): Traversable` *Read more below*

And support methods:
* `fromSnapshot(DocumentSnapshot $snapshot): Entity`
* `fake(array<string, mixed> array $overrides = []): Entity`
* `make(array<string, mixed> array $overrides = []): Entity` *Same as `fake()` but inserts the document

Additionally, these methods access metadata about the underlying Firestore (sub)collection:
* `collection(): CollectionReference`
* `parent(): ?DocumentReference`
* `id(): string`
* `name(): string`
* `path(): string`

And finally some familiar `Model`-inspired validation methods:
* `setValidation(ValidationInterface $validation): self`
* `skipValidation(bool $skip = true)`
* `validate(array $data, bool $cleanValidationRules = true): bool`
* `getValidationRules(array $options = []): array`
* `getErrors(): array`

#### Retrieving Documents

You may use the Firestore client directly to return snapshots and convert them to your Entity
of choice with `fromSnapshot()`, but `Collection` also allows specifying an overriding state
for `list()`. This can be an explicit `CollectionReference` or (more helpful) an instance of
`Google\Cloud\Firestore\Query`, which opens up the possibility of using filters, sorts, and
limits as well as traversing collection groups:
```php
// Using filters
$widgets = new WidgetCollection();
$query   = $widgets->collection()->where('color', '=', 'purple'); // returns Query
foreach ($widgets->list($query) as $widget) {
    echo $widget->weight;
}

// Grouped collections (traverses all collections and subcollections named "widgets")
$group = firestore()->collectionGroup('widgets');
foreach ($widgets->list($group) as $widget) {
    echo $widget->color;
}
```

To make this even easier, `Collection` will "pass through" the following method calls to
the underlying collection:
* `endAt()`, `endBefore()`, `limit()`, `limitToLast()`, `offset()`, `orderBy()`, `select()`, `startAfter()`, `startAt()`, `where()`

This allows for even easier method chaining:
```php
$result = $widgets->list($widgets->orderBy('color')->limit(5));
```

> Note that `list()` always returns a `Traversable` so documents are only retrieved and converted ot Entities as they are actually needed

### Entity

This library also comes with its own Firestore `Entity` that handles Google's timestamp
conversions and these methods to access metadata about the underlying Firestore document:
* `document(?DocumentReference $document = null): ?DocumentReference` *Gets or sets the document*
* `id(): string`
