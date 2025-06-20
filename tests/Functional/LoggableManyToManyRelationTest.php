<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Tests\Functional;

use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Article;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Tag;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\CustomTestCase;

class LoggableManyToManyRelationTest extends CustomTestCase
{
    public function testPersistManyToManyRelation(): void
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create two tags
        $tag1 = $this->createTag('world');
        $this->assertRepositoriesCount(1, 0, 0, 0, 1, 0);

        $tag2 = $this->createTag('trip');
        $this->assertRepositoriesCount(2, 0, 0, 0, 2, 0);

        // create an article with tags
        $article = $this->createArticle('Around the World in Seventy-two Days',null, [$tag1, $tag2]);
        $articleId = $article->getId();
        $this->assertRepositoriesCount(3, 1, 0, 0, 2, 0);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_CREATE,
            null,
            ['title' => 'Around the World in Seventy-two Days', 'id'=>$articleId, 'author' => null, 'tags'=>[['class'=>Tag::class, 'id'=>$tag1->getId(), 'toString'=>'world'], ['class'=>Tag::class, 'id'=>$tag2->getId(), 'toString'=>'trip']]]
        );

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(3, 1, 0, 0, 2, 0);
    }

    public function testUpdateManyToManyRelation(): void
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create an article
        $article = $this->createArticle('Around the World in Seventy-two Days');
        $articleId = $article->getId();
        $this->assertRepositoriesCount(1, 1, 0, 0, 0, 0);

        // create two tags
        $tag1 = $this->createTag('world');
        $this->assertRepositoriesCount(2, 1, 0, 0, 1, 0);

        $tag2 = $this->createTag('trip');
        $this->assertRepositoriesCount(3, 1, 0, 0, 2, 0);

        // add tags to the article
        $article->addTag($tag1);
        $article->addTag($tag2);
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(4, 1, 0, 0, 2, 0);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['tags'=>null],
            ['tags'=>[['class'=>Tag::class, 'id'=>$tag1->getId(), 'toString'=>'world'], ['class'=>Tag::class, 'id'=>$tag2->getId(), 'toString'=>'trip']]]
        );

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(4, 1, 0, 0, 2, 0);
    }

    public function testRemoveManyToManyRelation(): void
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create two tags
        $tag1 = $this->createTag('world');
        $this->assertRepositoriesCount(1, 0, 0, 0, 1, 0);

        $tag2 = $this->createTag('trip');
        $this->assertRepositoriesCount(2, 0, 0, 0, 2, 0);

        // create an article with two tags
        $article = $this->createArticle('Around the World in Seventy-two Days',null, [$tag1, $tag2]);
        $articleId = $article->getId();
        $this->assertRepositoriesCount(3, 1, 0, 0, 2, 0);

        // remove tags from the article
        $article->removeTag($tag1);
        $article->removeTag($tag2);
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(4, 1, 0, 0, 2, 0);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['tags'=>[['class'=>Tag::class, 'id'=>$tag1->getId(), 'toString'=>'world'], ['class'=>Tag::class, 'id'=>$tag2->getId(), 'toString'=>'trip']]],
            ['tags'=>null]
        );

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(4, 1, 0, 0, 2, 0);
    }

    public function testRemoveManyToManyRelatedObject(): void
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create two tags
        $tag1 = $this->createTag('world');
        $tag1Id = $tag1->getId();
        $this->assertRepositoriesCount(1, 0, 0, 0, 1, 0);

        $tag2 = $this->createTag('trip');
        $this->assertRepositoriesCount(2, 0, 0, 0, 2, 0);

        // create an article with two tags
        $article = $this->createArticle('Around the World in Seventy-two Days',null, [$tag1, $tag2]);
        $articleId = $article->getId();
        $this->assertRepositoriesCount(3, 1, 0, 0, 2, 0);

        // remove one tag from database
        $this->getEntityManager()->remove($tag1);
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(4, 1, 0, 0, 1, 0);

        // check entity log
        $this->assertEntityLogExists(Tag::class, $articleId,
            Log::ACTION_REMOVE,
            ['id'=>$tag1Id, 'name'=>'world','articles'=>null],
            null
        );

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(4, 1, 0, 0, 1, 0);
    }
}