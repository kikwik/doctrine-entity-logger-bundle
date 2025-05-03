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
        $this->assertEntityLogCount(1);
        $article = $this->createArticle('Around the World in Seventy-two Days', $author);
        $this->assertEntityLogCount(2);
        $articleId = $article->getId();

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_CREATE,
            null,
            ['title' => 'Around the World in Seventy-two Days', 'id'=>$articleId, 'author' => ['class'=>Author::class, 'id'=>$author->getId(), 'toString'=>'Joseph Pulitzer'], 'tags'=>[]]
        );
    }

    public function testUpdateManyToOneRelation()
    {
        // create an author and an article
        $author1 = $this->createAuthor('Joseph Pulitzer');
        $this->assertEntityLogCount(1);
        $article = $this->createArticle('Around the World in Seventy-two Days', $author1);
        $this->assertEntityLogCount(2);
        $articleId = $article->getId();

        // create an author and change article's author
        $author2 = $this->createAuthor('Joseph Ratzinger');
        $this->assertEntityLogCount(3);
        $article->setAuthor($author2);
        $this->getEntityManager()->flush();

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['author' => ['class'=>Author::class, 'id'=>$author1->getId(), 'toString'=>'Joseph Pulitzer']],
            ['author' => ['class'=>Author::class, 'id'=>$author2->getId(), 'toString'=>'Joseph Ratzinger']]
        );
    }

    public function testRemoveManyToOneRelation()
    {
        // create an author and an article
        $author = $this->createAuthor('Joseph Pulitzer');
        $this->assertEntityLogCount(1);
        $article = $this->createArticle('Around the World in Seventy-two Days', $author);
        $this->assertEntityLogCount(2);
        $articleId = $article->getId();

        // remove author from article
        $article->setAuthor(null);
        $this->getEntityManager()->flush();
        $this->assertEntityLogCount(3);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['author' => ['class'=>Author::class, 'id'=>$author->getId(), 'toString'=>'Joseph Pulitzer']],
            ['author' => null]
        );
    }

    public function testRemoveManyToOneRelatedObject()
    {
        // create an author and an article
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertEntityLogCount(1);
        $article = $this->createArticle('Around the World in Seventy-two Days', $author);
        $this->assertEntityLogCount(2);
        $articleId = $article->getId();

        // delete the author
        $article->setAuthor(null);
        $this->getEntityManager()->remove($author);
        $this->getEntityManager()->flush();
        $this->assertEntityLogCount(4);

        // check entity log
        $this->assertEntityLogExists(Author::class, $articleId,
            Log::ACTION_REMOVE,
            ['id'=>$authorId, 'name'=>'Joseph Pulitzer','articles'=>null],
            null
        );
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['author' => ['class'=>Author::class, 'id'=>$authorId, 'toString'=>'Joseph Pulitzer']],
            ['author' => null]
        );
    }
}