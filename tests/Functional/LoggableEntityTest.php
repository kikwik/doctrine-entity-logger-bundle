<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Tests\Functional;


use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\CustomTestCase;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\Entity\Author;

class LoggableEntityTest extends CustomTestCase
{

    public function testPersistSimpleEntity(): void
    {
        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();

        // check entity log
        $this->assertEntityLog(Author::class, $authorId,
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

        // update article
        $author->setName('Martin Pulitzer');
        $this->getEntityManager()->flush();

        // check entity log
        $this->assertEntityLog(Author::class, $authorId,
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

        // remove the author
        $this->getEntityManager()->remove($author);
        $this->getEntityManager()->flush();

        // check entity log
        $this->assertEntityLog(Author::class, $authorId,
            Log::ACTION_REMOVE,
            ['name' => 'Joseph Pulitzer', 'id'=>$authorId, 'articles' => null],
            null
        );
    }

}