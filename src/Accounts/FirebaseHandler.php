<?php

namespace Tatter\Firebase\Accounts;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\UserRecord;
use Tatter\Accounts\Entities\Account;
use Tatter\Accounts\Handlers\BaseHandler;
use Throwable;

/**
 * @deprecated 1.0.4
 */
class FirebaseHandler extends BaseHandler
{
    /**
     * Account field to use as the unique identifier.
     *
     * @var string
     */
    protected $primaryKey = 'uid';

    /**
     * Internal fields supported by this handler.
     *
     * @var array
     */
    protected $fields = [
        'uid'         => 'id',
        'email'       => 'email',
        'displayName' => 'name',
        'phoneNumber' => 'phone',
        'active'      => 'valid',
    ];

    /**
     * Toggle for debug mode - whether exceptions throw or not
     *
     * @var bool
     */
    public $debug;

    /**
     * Load or store the model
     *
     * @param Auth $firebase Instance of the Firebase SDK Auth, or null to load from the service
     */
    public function __construct(?Auth $firebase = null)
    {
        $this->source = $firebase ?? service('firebase')->auth;

        $this->debug = CI_DEBUG;
    }

    //--------------------------------------------------------------------
    // Utilities
    //--------------------------------------------------------------------

    /**
     * Wrap original source data into an Account based on $fields.
     *
     * @param UserRecord $record User record from Auth
     */
    protected function wrap($record): Account
    {
        // Create the account entity
        $account = new Account(self::class, $record->{$this->primaryKey});

        // Map each field
        foreach ($this->fields as $from => $to) {
            if (isset($record->{$from})) {
                $account->{$to} = $record->{$from};
            }
        }

        // Inject the original record
        $account->original($record);

        return $account;
    }

    /**
     * Common try..catch wrapper for SDK calls.
     *
     * @param callable $callback The method on the SDK object
     * @param mixed    $params   Parameters to pass to the callable
     *
     * @return mixed|null
     */
    protected function tryFirebaseMethod(callable $callback, ...$params)
    {
        $this->errors = [];

        // If debug mode is enabled then make the call directly
        if ($this->debug) {
            return $callback(...$params);
        }

        // Otherwise intercept errors
        try {
            $result = $callback(...$params);
        } catch (Throwable $e) {
            $this->errors[] = $e->getMessage();

            return null;
        }

        return $result;
    }

    //--------------------------------------------------------------------
    // CRUD
    //--------------------------------------------------------------------

    /**
     * Return an account by its UID
     *
     * @param mixed $uid The value of primaryKey to look for
     */
    public function get($uid): ?Account
    {
        if (! $record = $this->tryFirebaseMethod([$this->source, 'getUser'], $uid)) {
            return null;
        }

        // Wrap the result into an Account
        return $this->wrap($record);
    }

    /**
     * Create a new account and return it
     *
     * @param Account|array $data Values to use
     */
    public function add($data): ?Account
    {
        // If an Account was given then unwrap it
        if ($data instanceof Account) {
            $data = $this->unwrap($data);
        }

        // If no password was provided then generate a random one so the account is usable
        if (! isset($data['password'])) {
            $data['password'] = bin2hex(random_bytes(16));
        }

        // Try to create the user
        if (! $record = $this->tryFirebaseMethod([$this->source, 'createUser'], $data)) {
            return null;
        }

        // Return the new entity as an Account
        return $this->wrap($record);
    }

    /**
     * Update an existing account
     *
     * @param mixed $uid  The value of primaryKey to look for
     * @param mixed $data Values to use
     */
    public function update($uid, $data): bool
    {
        // If an Account was given then unwrap it
        if ($data instanceof Account) {
            $data = $this->unwrap($data);
        }

        // Try to update it
        return ! (! $result = $this->tryFirebaseMethod([$this->source, 'updateUser'], $uid, $data));
    }

    /**
     * Deletes a single account where $uid matches the primaryKey
     * The SDK returns void so the onyl way to check success is if there are no errors
     *
     * @param mixed $uid The the account's primary key
     */
    public function remove($uid): bool
    {
        $this->tryFirebaseMethod([$this->source, 'deleteUser'], $uid);

        return empty($this->errors);
    }
}
