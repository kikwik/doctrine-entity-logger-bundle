<?php

namespace Functional;

use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\CustomTestCase;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\Entity\Article;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\Entity\Author;

class ManyToOneRelationTest extends CustomTestCase
{
    public function testPersistEntityWithRelation(): void
    {
        // create an author and an article
        $author = $this->createAuthor('Joseph Pulitzer');
        $article = $this->createArticle('Article 1', $author);
        $articleId = $article->getId();

        // check entity log
        $this->assertEntityLog(Article::class, $articleId,
            Log::ACTION_CREATE,
            null,
            ['title' => 'Article 1', 'id'=>$articleId, 'author' => ['class'=>Author::class, 'id'=>$author->getId(), 'toString'=>$author->getName()], 'tags'=>[]]
        );
    }

    public function testUpdateRelation()
    {
        // create an author and an article
        $author1 = $this->createAuthor('Joseph Pulitzer');
        $article = $this->createArticle('Article 1', $author1);
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
}