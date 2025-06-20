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
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoriesCount(1, 0, 1, 0, 0, 0);

        // create a partner
        $partner = $this->createPartner('Vaticano', $author);
        $partnerId = $partner->getId();
        $this->assertRepositoriesCount(2, 0, 1, 1, 0, 0);

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

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(2, 0, 1, 1, 0, 0);
    }

    public function testRemoveOneToOneRelation(): void
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoriesCount(1, 0, 1, 0, 0, 0);

        // create a partner
        $partner = $this->createPartner('Vaticano', $author);
        $partnerId = $partner->getId();
        $this->assertRepositoriesCount(2, 0, 1, 1, 0, 0);

        // remove the partner
        $this->getEntityManager()->remove($partner);
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(3, 0, 1, 0, 0, 0);

        // check entity log
        $this->assertEntityLogExists(Partner::class, $partnerId,
            Log::ACTION_REMOVE,
            ['name' => 'Vaticano', 'id'=>$partnerId, 'author' => ['class'=>Author::class, 'id'=>$authorId, 'toString'=>'Joseph Pulitzer']],
            null
        );

//        // TODO: ensure that there are no residual updates
//        $this->getEntityManager()->flush();
//        $this->assertRepositoriesCount(3, 0, 1, 0, 0, 0);
    }
}