<?php

use CodeIgniter\Test\CIUnitTestCase;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Tatter\Firebase\Test\FirebaseUserTrait;

/**
 * @internal
 */
final class FirebaseUserTraitTest extends CIUnitTestCase
{
    use FirebaseUserTrait;

    /**
     * Instance of the Firebase SDK.
     *
     * @var Auth
     */
    protected $firebase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->firebase = service('firebase')->auth;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->firebaseUserTearDown();
    }

    public function testCreateUserReturnsUserRecord()
    {
        $user = $this->createFirebaseUser();

        $this->assertInstanceOf(UserRecord::class, $user);
    }

    public function testCreateUserCreatesUser()
    {
        $user = $this->createFirebaseUser();

        $test = $this->firebase->getUser($user->uid);

        $this->assertInstanceOf(UserRecord::class, $test);
        $this->assertSame($user->email, $test->email);
    }

    public function testRemoveUserRemovesUser()
    {
        $user = $this->createFirebaseUser();
        $this->removeFirebaseUser($user->uid);

        $this->expectException(UserNotFound::class);

        $this->firebase->getUser($user->uid);
    }

    public function testTearDownRemovesUser()
    {
        $user = $this->createFirebaseUser();
        $this->firebaseUserTearDown();

        $this->expectException(UserNotFound::class);

        $this->firebase->getUser($user->uid);
    }
}
