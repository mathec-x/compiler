<?php

namespace Compiler\PathConverter;

/**
 * Don't convert paths.
 *
 * Please report bugs on https://github.com/Compiler/path-converter/issues
 *
 * @license MIT License
 */
class NoConverter implements ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($path)
    {
        return $path;
    }
}
