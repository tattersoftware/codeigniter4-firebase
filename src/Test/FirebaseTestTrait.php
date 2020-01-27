<?php namespace Tatter\Firebase\Test;

use Tatter\Firebase\Accounts\FirebaseHandler;

trait FirebaseTestTrait
{
	use \Tatter\Accounts\Test\AccountsTestTrait;

	/**
	 * Creates a Firebase Authenticaion user on-the-fly.
	 *
	 * @param array $data
	 *
	 * @return $this
	 */
	protected function createFirebaseAccount(array $data = [])
	{
		// If no Firebase SDK Auth is available then get one
		if (empty($this->handler))
		{
			$this->handler = new FirebaseHandler();
		}

		$defaults = $this->generateAccount();

		// The SDK has some buggy phone number handling, so ignore for now
		$defaults->phone = null;

		foreach ($data as $field => $value)
		{
			$defaults->$field = $value;
		}

		$account = $this->handler->add($defaults);
		$this->removeCache[] = ['FirebaseAccount', $account->uid()];

		return $account;
	}

	/**
	 * Removes a Firebase Authenticaion user.
	 *
	 * @param string $uid  The UID of the user to remove
	 *
	 * @return bool
	 */
	protected function removeFirebaseAccount(string $uid): bool
	{
		// If no Firebase SDK Auth is available then get one
		if (empty($this->handler))
		{
			$this->handler = new FirebaseHandler();
		}

		return $this->handler->remove($uid);
	}
}
