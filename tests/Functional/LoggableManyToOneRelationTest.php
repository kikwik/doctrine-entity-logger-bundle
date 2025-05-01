<?php

namespace Functional;

use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Article;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Author;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\CustomTestCase;

class LoggableManyToOneRelationTest extends CustomTestCase
{
    public function testPersistManyToOneRelation(): void
    {
        // create an author and an article
        $author = $this->createAuthor('Joseph Pulitzer');
        $article = $this->createArticle('Around the World in Seventy-two Days', $author);
        $articleId = $article->getId();

        // check entity log
        $this->assertEntityLog(Article::class, $articleId,
            Log::ACTION_CREATE,
            null,
            ['title' => 'Around the World in Seventy-two Days', 'id'=>$articleId, 'author' => ['class'=>Author::class, 'id'=>$author->getId(), 'toString'=>'Joseph Pulitzer'], 'tags'=>[]]
        );
    }

    public function testUpdateManyToOneRelation()
    {
        // create an author and an article
        $author1 = $this->createAuthor('Joseph Pulitzer');
        $article = $this->createArticle('Around the World in Seventy-two Days', $author1);
        $articleId = $article->getId();

        // create an author and change article's author
        $author2 = $this->createAuthor('Joseph Ratzinger');
        $article->setAuthor($author2);
        $this->getEntityManager()->flush();

        // check entity log
        $this->assertEntityLog(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['author' => ['class'=>Author::class, 'id'=>$author1->getId(), 'toString'=>'Joseph Pulitzer']],
            ['author' => ['class'=>Author::class, 'id'=>$author2->getId(), 'toString'=>'Joseph Ratzinger']]
        );
    }

    public function testRemoveManyToOneRelation()
    {
        // create an author and an article
        $author = $this->createAuthor('Joseph Pulitzer');
        $article = $this->createArticle('Around the World in Seventy-two Days', $author);
        $articleId = $article->getId();

        // remove author from article
        $article->setAuthor(null);
        $this->getEntityManager()->flush();

        // check entity log
        $this->assertEntityLog(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['author' => ['class'=>Author::class, 'id'=>$author->getId(), 'toString'=>'Joseph Pulitzer']],
            ['author' => null]
        );
    }
}