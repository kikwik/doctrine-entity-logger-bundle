<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Tests\Util;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Article;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Author;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Partner;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\Tag;
use Kikwik\DoctrineEntityLoggerBundle\Tests\Util\App\Entity\User;
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

    protected function createPartner(string $name, Author $author): Partner
    {
        $partner = new Partner();
        $partner->setName($name);
        $partner->setAuthor($author);
        $this->getEntityManager()->persist($partner);
        $this->getEntityManager()->flush();
        return $partner;
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

    protected function createUser(string $name): User
    {
        $user = new User();
        $user->setName($name);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
        return $user;
    }

    protected function assertRepositoryCount(string $objectClass, int $expectedCount)
    {
        $logRepo = $this->getRepository($objectClass);
        $logs = $logRepo->findAll();
        $this->assertCount($expectedCount, $logs);
    }

    protected function assertEntityLogExists(string $objectClass, int $objectId, string $action, ?array $expectedOldValues, ?array $expectedNewValues)
    {
        $logRepo = $this->getRepository(Log::class);
        $log = $logRepo->findOneBy(['objectClass' => $objectClass, 'objectId' => $objectId, 'action' => $action]);
        $this->assertNotNull($log);
        $this->assertEquals($expectedOldValues, $log->getOldValues());
        $this->assertEquals($expectedNewValues, $log->getNewValues());
    }


}