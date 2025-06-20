<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Tests\Functional;


use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Author;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\CustomTestCase;

class LoggableEntityTest extends CustomTestCase
{

    public function testPersistSimpleEntity(): void
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoriesCount(1, 0, 1, 0, 0, 0);

        // check entity log
        $this->assertEntityLogExists(Author::class, $authorId,
            Log::ACTION_CREATE,
            null,
            ['name' => 'Joseph Pulitzer', 'id'=>$authorId, 'articles' => null, 'partner'=>null]
        );

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(1, 0, 1, 0, 0, 0);
    }

    public function testUpdateSimpleEntity(): void
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoriesCount(1, 0, 1, 0, 0, 0);

        // update author name
        $author->setName('Martin Pulitzer');
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(2, 0, 1, 0, 0, 0);

        // check entity log
        $this->assertEntityLogExists(Author::class, $authorId,
            Log::ACTION_UPDATE,
            ['name' => 'Joseph Pulitzer'],
            ['name' => 'Martin Pulitzer']
        );

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(2, 0, 1, 0, 0, 0);
    }

    public function testRemoveSimpleEntity(): void
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoriesCount(1, 0, 1, 0, 0, 0);

        // remove the author
        $this->getEntityManager()->remove($author);
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(2, 0, 0, 0, 0, 0);

        // check entity log
        $this->assertEntityLogExists(Author::class, $authorId,
            Log::ACTION_REMOVE,
            ['name' => 'Joseph Pulitzer', 'id'=>$authorId, 'articles' => null, 'partner'=>null],
            null
        );

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(2, 0, 0, 0, 0, 0);
    }


    public function testEntityLogNotCreatedWhenNoChange()
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoriesCount(1, 0, 1, 0, 0, 0);

        // update the createdBy field
        $author->setCreatedBy('test command');
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(1, 0, 1, 0, 0, 0);

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(1, 0, 1, 0, 0, 0);
    }
}