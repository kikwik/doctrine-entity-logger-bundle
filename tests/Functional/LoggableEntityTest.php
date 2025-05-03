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

        // check entity log
        $this->assertEntityLogCount(1);
        $this->assertEntityLogExists(Author::class, $authorId,
            Log::ACTION_CREATE,
            null,
            ['name' => 'Joseph Pulitzer', 'id'=>$authorId, 'articles' => null]
        );
    }

    public function testUpdateSimpleEntity(): void
    {
        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertEntityLogCount(1);

        // update article
        $author->setName('Martin Pulitzer');
        $this->getEntityManager()->flush();

        // check entity log
        $this->assertEntityLogCount(2);
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
        $this->assertEntityLogCount(1);

        // remove the author
        $this->getEntityManager()->remove($author);
        $this->getEntityManager()->flush();

        // check entity log
        $this->assertEntityLogCount(2);
        $this->assertEntityLogExists(Author::class, $authorId,
            Log::ACTION_REMOVE,
            ['name' => 'Joseph Pulitzer', 'id'=>$authorId, 'articles' => null],
            null
        );
    }


    public function testEntityLogNotCreatedWhenNoChange()
    {
        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();

        // check entity log
        $this->assertCount(1,$this->getRepository(Log::class)->findAll());

        // update the createdBy field
        $author->setCreatedBy('test command');
        $this->getEntityManager()->flush();

        // check that no other entity log was created
        $this->assertCount(1,$this->getRepository(Log::class)->findAll());
    }
}