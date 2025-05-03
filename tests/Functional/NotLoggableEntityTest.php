<?php

namespace Functional;

use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\User;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\CustomTestCase;

class NotLoggableEntityTest extends CustomTestCase
{
    public function testPersistNotLoggableEntity(): void
    {
        // create a user
        $this->createUser('Pinco Pallino');

        // check user
        $users = $this->getRepository(User::class)->findAll();
        $this->assertCount(1, $users);

        // check that entity log is empty
        $this->assertEntityLogCount(0);
    }

    public function testUpdateNotLoggableEntity(): void
    {
        // create a user
        $user = $this->createUser('Pinco Pallino');

        // update user
        $user->setName('Marco Paolino');
        $this->getEntityManager()->flush();

        // check user
        $users = $this->getRepository(User::class)->findAll();
        $this->assertCount(1, $users);
        $this->assertEquals('Marco Paolino', $users[0]->getName());

        // check that entity log is empty
        $this->assertEntityLogCount(0);
    }

    public function testRemoveNotLoggableEntity(): void
    {
        // create a user
        $user = $this->createUser('Pinco Pallino');

        // check user
        $users = $this->getRepository(User::class)->findAll();
        $this->assertCount(1, $users);

        // remove the user
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();

        // check user
        $users = $this->getRepository(User::class)->findAll();
        $this->assertCount(0, $users);

        // check that entity log is empty
        $this->assertEntityLogCount(0);
    }
}