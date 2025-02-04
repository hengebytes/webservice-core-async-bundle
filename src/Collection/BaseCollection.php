<?php

namespace WebserviceCoreAsyncBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use JsonSerializable;
use RuntimeException;
use function array_slice;
use function in_array;

/**
 * @psalm-template TKey of array-key
 * @psalm-template T
 * @template-implements Collection<TKey,T>
 * @template-implements Selectable<TKey,T>
 * @psalm-consistent-constructor
 * @psalm-consistent-templates
 * @extends ArrayCollection<TKey,T>
 */
abstract class BaseCollection extends ArrayCollection implements JsonSerializable
{
    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getSortedByCustomFunc(callable $sortFunc, ?int $limit = null, ?int $offset = null): static
    {
        $filtered = $this->toArray();

        uasort($filtered, $sortFunc);

        if ($offset || $limit) {
            $filtered = array_slice($filtered, (int)$offset, $limit);
        }

        return $this->createFrom($filtered);
    }

    /**
     * Note: this solution requires public access to property or a public getter method.
     * @param string[] $orderings ['attribute' => Criteria::ASC / Criteria::DESC, ... ]
     * @param int $limit
     * @param int $offset
     * @return static
     * @throws RuntimeException
     */
    public function getSorted(array $orderings, int $limit = null, int $offset = null)
    {
        $criteria = Criteria::create();
        if ($orderings) {
            $criteria->orderBy($orderings);
        }
        if ($limit !== null) {
            $criteria->setMaxResults($limit);
        }
        if ($offset !== null) {
            $criteria->setFirstResult($offset);
        }

        return $this->matching($criteria);
    }

    /**
     * @return T|null
     */
    public function getOne(string $attribute, string $condition, mixed $value)
    {
        return $this->getWhere($attribute, $condition, $value)->first() ?: null;
    }

    /**
     * @param string $attribute
     * @param string $condition = | != | >= | > etc.
     * @param mixed $value
     * @return static
     */
    public function getWhere(string $attribute, string $condition, mixed $value)
    {
        $items = [];

        $ucAttribute = ucfirst($attribute);
        $item = $this->first();

        if (!$item) {
            return $this->createFrom($items);
        }
        $byProperty = false;
        $method = null;
        if (property_exists($item, $attribute)) {
            $byProperty = true;
        } elseif (method_exists($item, 'get' . $ucAttribute)) {
            $method = 'get' . $ucAttribute;
        } elseif (method_exists($item, 'is' . $ucAttribute)) {
            $method = 'is' . $ucAttribute;
        } elseif (method_exists($item, 'has' . $ucAttribute)) {
            $method = 'has' . $ucAttribute;
        } else {
            throw new RuntimeException(sprintf('Neither of these methods exist %s, %s, %s', 'get' . $ucAttribute, 'is' . $ucAttribute, 'has' . $ucAttribute));
        }

        foreach ($this as $item) {
            $itemValue = $byProperty ? $item->$attribute : $item->{$method}();
            $valid = match ($condition) {
                '=' => $itemValue == $value,
                '!=' => $itemValue != $value,
                '>=' => $itemValue >= $value,
                '<=' => $itemValue <= $value,
                '>' => $itemValue > $value,
                '<' => $itemValue < $value,
                'in' => in_array($itemValue, $value, true),
                'not in' => !in_array($itemValue, $value, true),
                default => false,
            };

            if ($valid) {
                $items[] = $item;
            }
        }

        return $this->createFrom($items);
    }

    /**
     * @param BaseCollection $collection
     * @param string|null $collectionKeyProperty Property to retrieve key for collection item
     * @return self
     */
    public function merge(BaseCollection $collection, string $collectionKeyProperty = null): self
    {
        foreach ($collection as $model) {
            if ($collectionKeyProperty !== null && $model->{$collectionKeyProperty} !== null) {
                $this->set($model->{$collectionKeyProperty}, $model);
            } else {
                $this->add($model);
            }
        }

        return $this;
    }

    /**
     * @return T|null
     */
    public function getPrimary()
    {
        foreach ($this as $item) {
            if (property_exists($item, 'isPrimary') && $item->isPrimary) {
                return $item;
            }
            if (method_exists($item, 'isPrimary') && $item->isPrimary()) {
                return $item;
            }
        }

        return null;
    }

}
