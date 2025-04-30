<?php

namespace Kikwik\DoctrineEntityLoggerBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\IpTraceable\Traits\IpTraceableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity()]
#[ORM\Table(name: 'kw_entity_log')]
class Log
{
    use TimestampableEntity;
    use BlameableEntity;
    use IpTraceableEntity;

    /**************************************/
    /* CONST                              */
    /**************************************/

    const ACTION_CREATE = 'CREATE';
    const ACTION_UPDATE = 'UPDATE';
    const ACTION_REMOVE = 'REMOVE';

    /**************************************/
    /* PROPERTIES                         */
    /**************************************/

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    protected ?string $action = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    protected ?string $objectClass = null;

    #[ORM\Column(type: Types::INTEGER)]
    protected ?int $objectId = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    protected ?array $oldValues = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    protected ?array $newValues = null;

    /**************************************/
    /* CUSTOM METHODS                     */
    /**************************************/

    public function getChangedFields(): array
    {
        return array_unique(array_merge(array_keys($this->oldValues ?? []), array_keys($this->newValues ?? [])));
    }

    /**************************************/
    /* GETTERS & SETTERS                  */
    /**************************************/

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): Log
    {
        $this->action = $action;
        return $this;
    }

    public function getObjectClass(): ?string
    {
        return $this->objectClass;
    }

    public function setObjectClass(?string $objectClass): Log
    {
        $this->objectClass = $objectClass;
        return $this;
    }

    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    public function setObjectId(?int $objectId): Log
    {
        $this->objectId = $objectId;
        return $this;
    }

    public function getOldValues(): ?array
    {
        return $this->oldValues;
    }

    public function setOldValues(?array $oldValues): Log
    {
        $this->oldValues = $oldValues;
        return $this;
    }

    public function getNewValues(): ?array
    {
        return $this->newValues;
    }

    public function setNewValues(?array $newValues): Log
    {
        $this->newValues = $newValues;
        return $this;
    }



}