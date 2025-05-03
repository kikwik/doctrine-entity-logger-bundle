<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Tests\Functional;

use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Article;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Author;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\CustomTestCase;

class LoggableManyToOneRelationTest extends CustomTestCase
{
    public function testPersistManyToOneRelation(): void
    {
        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        // create an article
        $article = $this->createArticle('Around the World in Seventy-two Days', $author);
        $articleId = $article->getId();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Log::class,2);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_CREATE,
            null,
            ['title' => 'Around the World in Seventy-two Days', 'id'=>$articleId, 'author' => ['class'=>Author::class, 'id'=>$author->getId(), 'toString'=>'Joseph Pulitzer'], 'tags'=>[]]
        );
    }

    public function testUpdateManyToOneRelation()
    {
        // create an author
        $author1 = $this->createAuthor('Joseph Pulitzer');
        $author1Id = $author1->getId();
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        // create an article
        $article = $this->createArticle('Around the World in Seventy-two Days', $author1);
        $articleId = $article->getId();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Log::class,2);

        // create another author
        $author2 = $this->createAuthor('Joseph Ratzinger');
        $author2Id = $author2->getId();
        $this->assertRepositoryCount(Author::class, 2);
        $this->assertRepositoryCount(Log::class,3);

        // change article's author
        $article->setAuthor($author2);
        $this->getEntityManager()->flush();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Author::class, 2);
        $this->assertRepositoryCount(Log::class,4);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['author' => ['class'=>Author::class, 'id'=>$author1Id, 'toString'=>'Joseph Pulitzer']],
            ['author' => ['class'=>Author::class, 'id'=>$author2Id, 'toString'=>'Joseph Ratzinger']]
        );
    }

    public function testRemoveManyToOneRelation()
    {
        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        // create an article
        $article = $this->createArticle('Around the World in Seventy-two Days', $author);
        $articleId = $article->getId();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Log::class,2);


        // remove author from article
        $article->setAuthor(null);
        $this->getEntityManager()->flush();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,3);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['author' => ['class'=>Author::class, 'id'=>$author->getId(), 'toString'=>'Joseph Pulitzer']],
            ['author' => null]
        );
    }

    public function testRemoveManyToOneRelatedObject()
    {
        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        // create an article
        $article = $this->createArticle('Around the World in Seventy-two Days', $author);
        $articleId = $article->getId();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Log::class,2);

        // remove the author from database
        $article->setAuthor(null);
        $this->getEntityManager()->remove($author);
        $this->getEntityManager()->flush();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Author::class, 0);
        $this->assertRepositoryCount(Log::class,4);

        // check entity log
        $this->assertEntityLogExists(Author::class, $authorId,
            Log::ACTION_REMOVE,
            ['id'=>$authorId, 'name'=>'Joseph Pulitzer','articles'=>null, 'partner'=>null],
            null
        );
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['author' => ['class'=>Author::class, 'id'=>$authorId, 'toString'=>'Joseph Pulitzer']],
            ['author' => null]
        );
    }

    public function testRemoveOneToManyRelatedObject()
    {
        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,1);

        // create an article
        $article = $this->createArticle('Around the World in Seventy-two Days', $author);
        $articleId = $article->getId();
        $this->assertRepositoryCount(Article::class, 1);
        $this->assertRepositoryCount(Log::class,2);

        // remove the article
        $this->getEntityManager()->remove($article);
        $this->getEntityManager()->flush();
        $this->assertRepositoryCount(Article::class, 0);
        $this->assertRepositoryCount(Author::class, 1);
        $this->assertRepositoryCount(Log::class,3);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_REMOVE,
            ['id'=>$articleId, 'title'=>'Around the World in Seventy-two Days','author'=>['class'=>Author::class, 'id'=>$authorId, 'toString'=>'Joseph Pulitzer'], 'tags'=>[]],
            null
        );
    }
}