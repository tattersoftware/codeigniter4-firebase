<?php

use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Tatter\Firebase\Test\AuthenticationTestTrait;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class AuthenticationTestTraitTest extends TestCase
{
    use AuthenticationTestTrait;

    /**
     * Instance of the Firebase SDK.
     */
    protected Auth $auth;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auth = service('firebase')->auth;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->clearFirebaseUsers();
    }

    public function testCreateUserReturnsUserRecord()
    {
        $user = $this->createFirebaseUser();

        $this->assertInstanceOf(UserRecord::class, $user);
    }

    public function testCreateUserCreatesUser()
    {
        $user = $this->createFirebaseUser();

        $test = $this->auth->getUser($user->uid);

        $this->assertInstanceOf(UserRecord::class, $test);
        $this->assertSame($user->email, $test->email);
    }

    public function testRemoveUserRemovesUser()
    {
        $user = $this->createFirebaseUser();
        $this->removeFirebaseUser($user->uid);

        $this->expectException(UserNotFound::class);

        $this->auth->getUser($user->uid);
    }

    public function testRemoveUserIgnoresNonexistent()
    {
        $this->removeFirebaseUser('whatchamacallit');

        $this->assertSame([], $this->firebaseUserCache);
    }

    public function testClearUsers()
    {
        $user = $this->createFirebaseUser();
        $this->clearFirebaseUsers();

        $this->expectException(UserNotFound::class);

        $this->auth->getUser($user->uid);
    }
}
