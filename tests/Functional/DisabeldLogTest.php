<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Tests\Functional;

use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Author;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\CustomTestCase;

class DisabeldLogTest extends CustomTestCase
{
    public function testPersistSimpleEntity(): void
    {
        $loggerConfig = $this->getEntityLoggerConfig();
        $loggerConfig->setEnabled(false);

        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoriesCount(0, 0, 1, 0, 0, 0);


        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(0, 0, 1, 0, 0, 0);
    }
}