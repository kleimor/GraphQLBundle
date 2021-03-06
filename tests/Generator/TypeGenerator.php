<?php

namespace Overblog\GraphQLBundle\Tests\Generator;

use Overblog\GraphQLBundle\Generator\TypeGenerator as BaseTypeGenerator;

final class TypeGenerator extends BaseTypeGenerator
{
    /**
     * @return int
     */
    public function getCacheDirMask()
    {
        return $this->cacheDirMask;
    }
}
