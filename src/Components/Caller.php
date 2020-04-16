<?php namespace Tatter\Firebase\Components;

use Kreait\Firebase\Factory;

/**
 * Class Caller
 *
 * Allows authenticated use of Firebase Callable Functions
 */
class Caller
{
	/**
	 * UID of the user to make the request.
	 *
	 * @var string
	 */
	protected $uid;

	/**
	 * A Firebase ID token for the user identified by $this->uid.
	 *
	 * @var string
	 */
	protected $token;

	/**
	 * Error messages from the last call
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Get and clear any error messsages
	 *
	 * @return array  Any error messages from the last call
	 */
	public function getErrors(): array
	{
		$errors       = $this->errors;
		$this->errors = [];

		return $errors;
	}

	/**
	 * Sets the Firebase Authentication user UID used to make calls
	 *
	 * @param string $uid
	 *
	 * @return $this
	 */
	public function setUid(string $uid): self
	{
		// If this is a new user then clear an existing token
		if ($uid != $this->uid)
		{
			$this->token = null;
		}
		
		$this->uid = $uid;

		return $this;
	}

	/**
	 * Execute a Firebase callable function
	 * https://firebase.google.com/docs/functions/callable-reference
	 *
	 * @param string $url   Callable endpoint URL
	 * @param mixed  $data  Data to send to the endpoint
	 *
	 * @return mixed  Decoded response from the callable function, or null on failure
	 */
	public function call(string $url, $data)
	{
		// Reset errors
		$this->errors = [];

		// Load the client
		$client = service('curlrequest');
		
		// Check if $data is already JSON
		if (is_string($data) && json_decode($data, true))
		{
			$body = $data;
		}
		else
		{
			$body = json_encode(['data' => $data]);
		}
		unset($data);
		
		// Check for authorization
		if ($this->uid)
		{
			$client->setHeader('Authorization', 'Bearer ' . $this->getToken());
		}
		$client->setHeader('Content-Type', 'application/json; charset=utf-8')->setBody($body);
		
		// Make the call
		$response = $client->post($url, ['http_errors' => false]);

		// Verify it worked
		if (empty($response))
		{
			$this->errors[] = 'Failed to execute remote request to ' . $url;
			return null;
		}
		
		// Decode the response
		if (! $body = json_decode($response->getBody()))
		{
			$this->errors[] = 'Unable to decode response: ' . $body;
			return null;
		}
		
		// Check for errors
		if (! empty($body->error))
		{
			$this->errors[] = $body->error->message;
			$this->errors[] = $body->error->status;
			
			if (! empty($body->error->details))
			{
				foreach ($body->error->details as $key => $value)
				{
					$this->errors[] = "$key : $value";
				}
			}

			return null;
		}
		
		// Verify the data
		if (! isset($body->result))
		{
			$this->errors[] = 'Result missing from response: ' . $response->getBody();
			return null;
		}
		
		// Otherwise it was a success! Return the data
		return $body->result;
	}

	/**
	 * Fetches a Firebase ID token for the current user
	 *
	 * @param bool $forceRefresh  Whether get a new token even if one exists
	 *
	 * @return string  The token
	 */
	protected function getToken($forceRefresh = false): ?string
	{
		if ($this->token && $forceRefresh === false)
		{
			return $this->token;
		}

		if (empty($this->uid))
		{
			$this->errors[] = 'You must specify a user before using callable functions!';
			return null;
		}

		// Get the auth component
		$auth = service('firebase')->auth;
		
		// Get a user token
		if (! $response = $auth->signInAsUser($this->uid))
		{
			$this->errors[] = 'Unable to generate custom token for user ' . $this->uid;
			return null;
		}

		// Store the actual ID token
		$this->token = $response->idToken();
		return $this->token;
	}
}
