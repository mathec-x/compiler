<?php

namespace Compiler\PathConverter;

/**
 * Convert file paths.
 *
 * @license MIT License
 */
interface ConverterInterface
{
    /**
     * Convert file paths.
     *
     * @param string $path The path to be converted
     *
     * @return string The new path
     */
    public function convert($path);
}
