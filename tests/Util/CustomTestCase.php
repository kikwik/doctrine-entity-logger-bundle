<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Tests\Util;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\Entity\Article;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\Entity\Author;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;

class CustomTestCase extends KernelTestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        // boot the Symfony kernel
        self::bootKernel();

        // use static::getContainer() to access the service container
        $this->container = static::getContainer();

        //clear database
        $filesystem = new Filesystem();
        $filesystem->remove('var/database.db3');

        //updating a schema in sqlite database
        $entityManager = $this->getEntityManager();
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metaData);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        restore_exception_handler();
    }


    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get('doctrine')->getManager();
    }

    protected function getRepository(string $entityClass): EntityRepository
    {
        return $this->getEntityManager()->getRepository($entityClass);
    }

    protected function createAuthor(string $name): Author
    {
        $author = new Author();
        $author->setName($name);
        $this->getEntityManager()->persist($author);
        $this->getEntityManager()->flush();
        return $author;
    }

    protected function createArticle(string $title, ?Author $author = null, ?array $tags = null): Article
    {
        $article = new Article();
        $article->setTitle($title);
        $article->setAuthor($author);
        if($tags)
        {
            foreach($tags as $tag)
            {
                $article->addTag($tag);
            }
        }
        $this->getEntityManager()->persist($article);
        $this->getEntityManager()->flush();
        return $article;
    }

    protected function createTag(string $name): Tag
    {
        $tag = new Tag();
        $tag->setName($name);
        $this->getEntityManager()->persist($tag);
        $this->getEntityManager()->flush();
        return $tag;
    }


    protected function assertEntityLog(string $objectClass, int $objectId, string $action, ?array $oldValues, ?array $newValues)
    {
        $logRepo = $this->getRepository(Log::class);
        $log = $logRepo->findOneBy(['objectClass' => $objectClass, 'objectId' => $objectId, 'action' => $action]);
        $this->assertNotNull($log);
        $this->assertEquals($oldValues, $log->getOldValues());
        $this->assertEquals($newValues, $log->getNewValues());
    }


}