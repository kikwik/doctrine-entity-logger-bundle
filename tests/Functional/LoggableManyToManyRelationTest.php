<?php

namespace Functional;

use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Article;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Tag;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\CustomTestCase;

class LoggableManyToManyRelationTest extends CustomTestCase
{
    public function testPersistManyToManyRelation(): void
    {
        // create two tag
        $tag1 = $this->createTag('world');
        $tag2 = $this->createTag('trip');

        // create an article with tags
        $article = $this->createArticle('Around the World in Seventy-two Days',null, [$tag1, $tag2]);
        $articleId = $article->getId();

        // check entity log
        $this->assertEntityLog(Article::class, $articleId,
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

        // create two tag
        $tag1 = $this->createTag('world');
        $tag2 = $this->createTag('trip');

        // add tags to the article
        $article->addTag($tag1);
        $article->addTag($tag2);
        $this->getEntityManager()->flush();

        // check entity log
        $this->assertEntityLog(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['tags'=>null],
            ['tags'=>[['class'=>Tag::class, 'id'=>$tag1->getId(), 'toString'=>'world'], ['class'=>Tag::class, 'id'=>$tag2->getId(), 'toString'=>'trip']]]
        );
    }

    public function testRemoveManyToManyRelation(): void
    {
        // create two tag
        $tag1 = $this->createTag('world');
        $tag2 = $this->createTag('trip');

        // create an article with two tags
        $article = $this->createArticle('Around the World in Seventy-two Days',null, [$tag1, $tag2]);
        $articleId = $article->getId();


        // remove tags from the article
        $article->removeTag($tag1);
        $article->removeTag($tag2);
        $this->getEntityManager()->flush();

        // check entity log
        $this->assertEntityLog(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['tags'=>[['class'=>Tag::class, 'id'=>$tag1->getId(), 'toString'=>'world'], ['class'=>Tag::class, 'id'=>$tag2->getId(), 'toString'=>'trip']]],
            ['tags'=>null]
        );
    }
}