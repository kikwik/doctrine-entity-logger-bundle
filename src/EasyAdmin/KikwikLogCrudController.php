<?php

namespace Kikwik\DoctrineEntityLoggerBundle\EasyAdmin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Kikwik\DoctrineEntityLoggerBundle\Entity\Log;

class KikwikLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Log::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural('Logs')
            ->setDefaultSort([
                'createdAt' => 'DESC',
            ])
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->disable('new');
        $actions->disable('edit');
        $actions->disable('delete');
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnDetail(),
            TextField::new('action'),
            Field::new('object')->setTemplatePath('@KikwikDoctrineEntityLogger/easy-admin/object.html.twig'),
            Field::new('changes')->setTemplatePath('@KikwikDoctrineEntityLogger/easy-admin/changes.html.twig'),
            DateTimeField::new('createdAt'),
            TextField::new('createdBy'),
            TextField::new('createdFromIp'),
        ];
    }
}