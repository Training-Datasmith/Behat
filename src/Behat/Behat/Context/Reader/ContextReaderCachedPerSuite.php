<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Context\Reader;

use Behat\Behat\Context\Environment\Context_Environment;
/**
 * Proxies call to another reader and caches callees for a length of an entire suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Context_Reader_Cached_Per_Suite implements Context_Reader
{
    /**
     * @var array[]
     */
    private array $cached_callees = [];
    /**
     * Initializes reader.
     */
    public function __construct(private readonly Context_Reader $child_reader)
    {
    }
    public function read_context_callees(Context_Environment $environment, $context_class)
    {
        $key = $this->generate_cache_key($environment, $context_class);
        return $this->cached_callees[$key] ?? $this->cached_callees[$key] = $this->child_reader->read_context_callees($environment, $context_class);
    }
    /**
     * Generates cache key.
     */
    private function generate_cache_key(Context_Environment $environment, string $context_class): string
    {
        return $environment->get_suite()->get_name() . $context_class;
    }
}