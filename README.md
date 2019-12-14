# Tatter\Firebase
Firebase integration for CodeIgniter 4

## Quick Start

1. Install with Composer: `> composer require tatter/firebase`
2. Set the environment to locate your app credentials: **.env** > FIREBASE_CREDENTIALS
3. Load the service: `$firebase = service('firebase');`

## Description

This is a CodeIgniter 4 integration of `Kreait\Firebase`, the "Unofficial Firebase Admin
SDK for PHP":

* [Documentation](https://firebase-php.readthedocs.io/)
* [GitHub repository](https://github.com/kreait/firebase-php)

It provides a convenience service and custom framework layers to use your Firebase project
within CodeIgniter 4. Please pay attention to the requirements of the underlying services:

* [kreait\firebase-php](https://firebase-php.readthedocs.io/en/stable/overview.html#requirements)
* [google\cloud-firestore](https://firebase-php.readthedocs.io/en/stable/cloud-firestore.html)

Notably, you must have the [gRPC PHP extension](https://github.com/grpc/grpc/tree/master/src/php)
and the credentials file to a [Service Account](https://firebase.google.com/docs/admin/setup#add_firebase_to_your_app)
with the *Project -> Editor* or *Project -> Owner* role.

## Installation

Install easily via Composer to take advantage of CodeIgniter 4's autoloading capabilities
and always be up-to-date:
* `> composer require tatter/firebase`

Or, install manually by downloading the source files and adding the directory to
`app/Config/Autoload.php`.

## Credentials

You must provide a key file with your application's service account credentials. The standard
way to do this is to add **keyfile.json** to your project and edit **.env** to its path
(relative to **public/**):

	FIREBASE_CREDENTIALS = ../keyfile.json

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

	$firebase = service('firebase');

The service will handle creating and caching each component as you need them. Access
components by their name:
```
$storage = $firebase->storage;
$bucket  = $storage->getBucket('my-bucket');
```

You can also use the service to access all the functions of `Kreait\Firebase\Factory`
directly, for example if you wanted a separate component instance:
```
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
* Caller

## Caller

While not yet officially supported by the Firebase SDK, this module includes a component
for Firebase callable functions. A simple example shows all its features:
```
// Get the component
$caller = service('firebase')->caller;

// Set the UID of the user making the call
$caller->setUid($user->uid);

// Make the call
$data = ['customerId' => 7, 'charge' => 3.50];
$response = $caller->call('https://us-central1-myproject.cloudfunctions.net/addCharge', $data);

if ($response === null)
{
	echo implode(' ', $caller->getErrors());
}
else
{
	print_r($response);
}
```

## Firestore

This module provides access to the Firestore database directly via `FirestoreClient`.
The eventual hope is to have a full-fledged CodeIgniter 4 database driver, but in the
meantime additional shim classes will be provided to imitate framework behavior.

### Progression

- [x] Service wrapper for Cloud Firestone Client
- [ ] Faux model for extending Firestone methods *(in progress)*
- [ ] Full database driver wrapping the Cloud Firestone Client
- [ ] Full database driver written natively for CodeIgniter 4

### FirestoreClient

Get an instance of `Google\Cloud\Firestore\FirestoreClient` from the service:

```
$db = service('firebase')->firestore->database();

$docRef = $db->collection('users')->document('lovelace');
$docRef->set([
    'first' => 'Ada',
    'last' => 'Lovelace',
    'born' => 1815
]);
printf('Added data to the lovelace document in the users collection.' . PHP_EOL);
```

### Model

Until a full database driver is available this module supplies a shim Model,
`Tatter\Firebase\Model` that stands in for the framework model by providing basic
equivalent methods. Please review [src/Model.php](src/Model.php) before using it to be
sure you understand its possibilities and limitations.

### Entity

This module also comes with a super-`Entity` that has convenience mapping for common
`Firestore` properties. This entity is used be default with `Tatter\Firebase\Model` but
you can also use it directly or reference its methods to enhance your own classes.
