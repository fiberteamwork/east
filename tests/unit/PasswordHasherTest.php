<?php

use App\Libraries\PasswordHasher;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PasswordHasherTest extends CIUnitTestCase
{
    public function testHashCanBeVerifiedWithoutStoringThePassword(): void
    {
        $credentials = PasswordHasher::hash('correct horse battery staple');

        $this->assertSame(64, strlen($credentials['hash']));
        $this->assertSame(32, strlen($credentials['salt']));
        $this->assertTrue(PasswordHasher::verify('correct horse battery staple', $credentials['salt'], $credentials['hash']));
        $this->assertFalse(PasswordHasher::verify('incorrect password', $credentials['salt'], $credentials['hash']));
    }

    public function testIdenticalPasswordsReceiveDifferentSaltsAndHashes(): void
    {
        $first = PasswordHasher::hash('correct horse battery staple');
        $second = PasswordHasher::hash('correct horse battery staple');

        $this->assertNotSame($first['salt'], $second['salt']);
        $this->assertNotSame($first['hash'], $second['hash']);
    }
}
