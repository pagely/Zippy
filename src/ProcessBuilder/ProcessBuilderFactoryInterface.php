<?php

/*
 * This file is part of Zippy.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Zippy\ProcessBuilder;

use Alchemy\Zippy\Exception\InvalidArgumentException;
use Alchemy\Zippy\ProcessBuilder\ProcessBuilder;

interface ProcessBuilderFactoryInterface
{
    /**
     * Returns a new instance of Symfony ProcessBuilder
     *
     * @return ProcessBuilder
     *
     * @throws InvalidArgumentException
     */
    public function create();

    /**
     * Returns the binary path
     *
     * @return string
     */
    public function getBinary();

    /**
     * Sets the binary path
     *
     * @param string $binary A binary path
     *
     * @return ProcessBuilderFactoryInterface
     *
     * @throws InvalidArgumentException In case binary is not executable
     */
    public function useBinary($binary);
}
