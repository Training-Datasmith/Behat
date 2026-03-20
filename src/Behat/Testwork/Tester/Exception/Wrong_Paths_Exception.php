<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Tester\Exception;

use Symfony\Component\Dependency_Injection\Exception\RuntimeException;
/**
 * Represents exception caused by a wrong paths argument.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Wrong_Paths_Exception extends RuntimeException implements Tester_Exception
{
    /**
     * @var list<string>
     */
    private readonly array $paths;
    /**
     * Initializes exception.
     *
     * @param string $message
     * @param string|list<string> $paths
     */
    public function __construct($message, string|array $paths)
    {
        parent::__construct($message);
        $this->paths = (array) $paths;
    }
    /**
     * Returns path that caused exception.
     *
     * @return list<string>
     */
    public function get_paths(): array
    {
        return $this->paths;
    }
    /**
     * Returns path that caused exception.
     *
     * @deprecated
     */
    public function get_path(): string
    {
        return implode(', ', $this->paths);
    }
}