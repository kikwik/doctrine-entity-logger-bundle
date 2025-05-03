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
        // create two tags
        $tag1 = $this->createTag('world');
        $this->assertRepositoryCount(Tag::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        $tag2 = $this->createTag('trip');
        $this->assertRepositoryCount(Tag::class, 2);
        $this->assertRepositoryCount(Log::class,2);

        // create an article with tags
        $article = $this->createArticle('Around the World in Seventy-two Days',null, [$tag1, $tag2]);
        $articleId = $article->getId();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Tag::class, 2);
        $this->assertRepositoryCount(Log::class,3);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_CREATE,
            null,
            ['title' => 'Around the World in Seventy-two Days', 'id'=>$articleId, 'author' => null, 'tags'=>[['class'=>Tag::class, 'id'=>$tag1->getId(), 'toString'=>'world'], ['class'=>Tag::class, 'id'=>$tag2->getId(), 'toString'=>'trip']]]
        );
    }

    public function testUpdateManyToManyRelation(): void
    {
        // create an article
        $article = $this->createArticle('Around the World in Seventy-two Days');
        $articleId = $article->getId();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        // create two tags
        $tag1 = $this->createTag('world');
        $this->assertRepositoryCount(Tag::class, 1);
        $this->assertRepositoryCount(Log::class,2);

        $tag2 = $this->createTag('trip');
        $this->assertRepositoryCount(Tag::class, 2);
        $this->assertRepositoryCount(Log::class,3);

        // add tags to the article
        $article->addTag($tag1);
        $article->addTag($tag2);
        $this->getEntityManager()->flush();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Tag::class, 2);
        $this->assertRepositoryCount(Log::class,4);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['tags'=>null],
            ['tags'=>[['class'=>Tag::class, 'id'=>$tag1->getId(), 'toString'=>'world'], ['class'=>Tag::class, 'id'=>$tag2->getId(), 'toString'=>'trip']]]
        );
    }

    public function testRemoveManyToManyRelation(): void
    {
        // create two tags
        $tag1 = $this->createTag('world');
        $this->assertRepositoryCount(Tag::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        $tag2 = $this->createTag('trip');
        $this->assertRepositoryCount(Tag::class, 2);
        $this->assertRepositoryCount(Log::class,2);

        // create an article with two tags
        $article = $this->createArticle('Around the World in Seventy-two Days',null, [$tag1, $tag2]);
        $articleId = $article->getId();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Tag::class, 2);
        $this->assertRepositoryCount(Log::class,3);

        // remove tags from the article
        $article->removeTag($tag1);
        $article->removeTag($tag2);
        $this->getEntityManager()->flush();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Tag::class, 2);
        $this->assertRepositoryCount(Log::class,4);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['tags'=>[['class'=>Tag::class, 'id'=>$tag1->getId(), 'toString'=>'world'], ['class'=>Tag::class, 'id'=>$tag2->getId(), 'toString'=>'trip']]],
            ['tags'=>null]
        );
    }

    public function testRemoveManyToManyRelatedObject(): void
    {
        // create two tags
        $tag1 = $this->createTag('world');
        $tag1Id = $tag1->getId();
        $this->assertRepositoryCount(Tag::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        $tag2 = $this->createTag('trip');
        $this->assertRepositoryCount(Tag::class, 2);
        $this->assertRepositoryCount(Log::class,2);

        // create an article with two tags
        $article = $this->createArticle('Around the World in Seventy-two Days',null, [$tag1, $tag2]);
        $articleId = $article->getId();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Tag::class, 2);
        $this->assertRepositoryCount(Log::class,3);

        // remove one tag from database
        $this->getEntityManager()->remove($tag1);
        $this->getEntityManager()->flush();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Tag::class, 1);
        $this->assertRepositoryCount(Log::class,4);

        // check entity log
        $this->assertEntityLogExists(Tag::class, $articleId,
            Log::ACTION_REMOVE,
            ['id'=>$tag1Id, 'name'=>'world','articles'=>null],
            null
        );
    }
}