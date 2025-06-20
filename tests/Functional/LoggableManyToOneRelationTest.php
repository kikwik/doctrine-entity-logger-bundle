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
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $this->assertRepositoriesCount(1, 0, 1, 0, 0, 0);

        // create an article
        $article = $this->createArticle('Around the World in Seventy-two Days', $author);
        $articleId = $article->getId();
        $this->assertRepositoriesCount(2, 1, 1, 0, 0, 0);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_CREATE,
            null,
            ['title' => 'Around the World in Seventy-two Days', 'id'=>$articleId, 'author' => ['class'=>Author::class, 'id'=>$author->getId(), 'toString'=>'Joseph Pulitzer'], 'tags'=>[]]
        );

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(2, 1, 1, 0, 0, 0);
    }

    public function testUpdateManyToOneRelation()
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create an author
        $author1 = $this->createAuthor('Joseph Pulitzer');
        $author1Id = $author1->getId();
        $this->assertRepositoriesCount(1, 0, 1, 0, 0, 0);

        // create an article
        $article = $this->createArticle('Around the World in Seventy-two Days', $author1);
        $articleId = $article->getId();
        $this->assertRepositoriesCount(2, 1, 1, 0, 0, 0);

        // create another author
        $author2 = $this->createAuthor('Joseph Ratzinger');
        $author2Id = $author2->getId();
        $this->assertRepositoriesCount(3, 1, 2, 0, 0, 0);

        // change article's author
        $article->setAuthor($author2);
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(4, 1, 2, 0, 0, 0);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['author' => ['class'=>Author::class, 'id'=>$author1Id, 'toString'=>'Joseph Pulitzer']],
            ['author' => ['class'=>Author::class, 'id'=>$author2Id, 'toString'=>'Joseph Ratzinger']]
        );

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(4, 1, 2, 0, 0, 0);
    }

    public function testRemoveManyToOneRelation()
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $this->assertRepositoriesCount(1, 0, 1, 0, 0, 0);

        // create an article
        $article = $this->createArticle('Around the World in Seventy-two Days', $author);
        $articleId = $article->getId();
        $this->assertRepositoriesCount(2, 1, 1, 0, 0, 0);

        // remove author from article
        $article->setAuthor(null);
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(3, 1, 1, 0, 0, 0);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_UPDATE,
            ['author' => ['class'=>Author::class, 'id'=>$author->getId(), 'toString'=>'Joseph Pulitzer']],
            ['author' => null]
        );

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(3, 1, 1, 0, 0, 0);
    }

    public function testRemoveManyToOneRelatedObject()
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoriesCount(1, 0, 1, 0, 0, 0);

        // create an article
        $article = $this->createArticle('Around the World in Seventy-two Days', $author);
        $articleId = $article->getId();
        $this->assertRepositoriesCount(2, 1, 1, 0, 0, 0);

        // remove the author from database
        $article->setAuthor(null);
        $this->getEntityManager()->remove($author);
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(4, 1, 0, 0, 0, 0);

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

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(4, 1, 0, 0, 0, 0);
    }

    public function testRemoveOneToManyRelatedObject()
    {
        // ensure that database is empty
        $this->assertRepositoriesCount(0, 0, 0, 0, 0, 0);

        // create an author
        $author = $this->createAuthor('Joseph Pulitzer');
        $authorId = $author->getId();
        $this->assertRepositoriesCount(1, 0, 1, 0, 0, 0);

        // create an article
        $article = $this->createArticle('Around the World in Seventy-two Days', $author);
        $articleId = $article->getId();
        $this->assertRepositoriesCount(2, 1, 1, 0, 0, 0);

        // remove the article
        $this->getEntityManager()->remove($article);
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(3, 0, 1, 0, 0, 0);

        // check entity log
        $this->assertEntityLogExists(Article::class, $articleId,
            Log::ACTION_REMOVE,
            ['id'=>$articleId, 'title'=>'Around the World in Seventy-two Days','author'=>['class'=>Author::class, 'id'=>$authorId, 'toString'=>'Joseph Pulitzer'], 'tags'=>[]],
            null
        );

        // ensure that there are no residual updates
        $this->getEntityManager()->flush();
        $this->assertRepositoriesCount(3, 0, 1, 0, 0, 0);
    }
}