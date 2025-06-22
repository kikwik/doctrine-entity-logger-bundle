<?php

namespace Kikwik\DoctrineEntityLoggerBundle\EasyAdmin;

use BitService\DominiBundle\Form\Embeddable\AdminType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class LogField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null)
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(HiddenType::class)
            ->setTemplatePath('@KikwikDoctrineEntityLogger/easy-admin/log_field.html.twig')
            ->hideOnForm()
            ;
    }
}
