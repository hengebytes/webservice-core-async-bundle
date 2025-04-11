<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Model;

abstract class BaseModel
{
    protected const string STATUS_NEW = 'new';
    protected const string STATUS_DELETED = 'deleted';

    protected string $objectStatusHash = self::STATUS_NEW;

    protected function getObjectStatusHash(): string
    {
        return '';
    }

    public function initState(): void
    {
        $this->objectStatusHash = $this->getObjectStatusHash();
    }

    public function setDeleted(): void
    {
        $this->objectStatusHash = self::STATUS_DELETED;
    }

    public function isDeleted(): bool
    {
        return $this->objectStatusHash === self::STATUS_DELETED;
    }

    public function isModified(): bool
    {
        return !$this->isNew() && !$this->isDeleted()
            && $this->objectStatusHash !== $this->getObjectStatusHash();
    }

    public function isNew(): bool
    {
        return $this->objectStatusHash === self::STATUS_NEW;
    }
}
