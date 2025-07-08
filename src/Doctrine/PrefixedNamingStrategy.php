<?php
namespace App\Doctrine;

use Doctrine\ORM\Mapping\DefaultNamingStrategy;

class PrefixedNamingStrategy extends DefaultNamingStrategy
{
    private string $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function classToTableName(string $className): string
    {
        return $this->prefix . parent::classToTableName($className);
    }
    
}
