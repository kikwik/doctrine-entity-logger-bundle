<?php

namespace Functional;

use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\User;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\CustomTestCase;

class NotLoggableEntityTest extends CustomTestCase
{
    public function testPersistNotLoggableEntity(): void
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create a user
        $this->createUser('Pinco Pallino');
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 1);

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 1);
    }

    public function testUpdateNotLoggableEntity(): void
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create a user
        $user = $this->createUser('Pinco Pallino');
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 1);

        // update user
        $user->setName('Marco Paolino');
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 1);

        // check user
        $users = $this->getRepository(User::class)->findAll();
        $this->assertCount(1, $users);
        $this->assertEquals('Marco Paolino', $users[0]->getName());

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 1);
    }

    public function testRemoveNotLoggableEntity(): void
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create a user
        $user = $this->createUser('Pinco Pallino');
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 1);

        // remove the user
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);
    }
}