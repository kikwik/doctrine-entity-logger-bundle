<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Tests\Functional;


use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Author;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\CustomTestCase;

class LoggableEntityTest extends CustomTestCase
{

    public function testPersistSimpleEntity(): void
    {
        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        // check entity log
        $this->assertEntityLogExists(Author::class, $authorId,
            Log::ACTION_CREATE,
            null,
            ['name' => 'Joseph Pulitzer', 'id'=>$authorId, 'articles' => null, 'partner'=>null]
        );
    }

    public function testUpdateSimpleEntity(): void
    {
        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        // update author name
        $author->setName('Martin Pulitzer');
        $this->getEntityManager()->flush();
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,2);

        // check entity log
        $this->assertEntityLogExists(Author::class, $authorId,
            Log::ACTION_UPDATE,
            ['name' => 'Joseph Pulitzer'],
            ['name' => 'Martin Pulitzer']
        );

    }

    public function testRemoveSimpleEntity(): void
    {
        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        // remove the author
        $this->getEntityManager()->remove($author);
        $this->getEntityManager()->flush();
        $this->assertRepositoryCount(Author::class, 0);
        $this->assertRepositoryCount(Log::class,2);

        // check entity log
        $this->assertEntityLogExists(Author::class, $authorId,
            Log::ACTION_REMOVE,
            ['name' => 'Joseph Pulitzer', 'id'=>$authorId, 'articles' => null, 'partner'=>null],
            null
        );
    }


    public function testEntityLogNotCreatedWhenNoChange()
    {
        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        // update the createdBy field
        $author->setCreatedBy('test command');
        $this->getEntityManager()->flush();
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,1);
    }
}