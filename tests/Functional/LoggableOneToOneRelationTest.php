<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Tests\Functional;

use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Author;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Partner;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\CustomTestCase;

class LoggableOneToOneRelationTest extends CustomTestCase
{
    public function testPersistOneToOneRelation(): void
    {
        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        // create a partner
        $partner = $this->createPartner('Vaticano', $author);
        $partnerId = $partner->getId();
        $this->assertRepositoryCount(Partner::class, 1);
        $this->assertRepositoryCount(Log::class,2);

        // check entity log
        $this->assertEntityLogExists(Author::class, $authorId,
            Log::ACTION_CREATE,
            null,
            ['name' => 'Joseph Pulitzer', 'id'=>$authorId, 'articles' => null, 'partner'=>null]
        );
        $this->assertEntityLogExists(Partner::class, $partnerId,
            Log::ACTION_CREATE,
            null,
            ['name' => 'Vaticano', 'id'=>$partnerId, 'author' => ['class'=>Author::class, 'id'=>$authorId, 'toString'=>'Joseph Pulitzer']]
        );
    }

    public function testRemoveOneToOneRelation(): void
    {
        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        // create a partner
        $partner = $this->createPartner('Vaticano', $author);
        $partnerId = $partner->getId();
        $this->assertRepositoryCount(Partner::class, 1);
        $this->assertRepositoryCount(Log::class,2);

        // remove the partner
        $this->getEntityManager()->remove($partner);
        $this->getEntityManager()->flush();
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Partner::class, 0);
        $this->assertRepositoryCount(Log::class,3);

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Partner::class, 0);
        $this->assertRepositoryCount(Log::class,3);

        // check entity log
        $this->assertEntityLogExists(Partner::class, $partnerId,
            Log::ACTION_REMOVE,
            ['name' => 'Vaticano', 'id'=>$partnerId, 'author' => ['class'=>Author::class, 'id'=>$authorId, 'toString'=>'Joseph Pulitzer']],
            null
        );
    }
}