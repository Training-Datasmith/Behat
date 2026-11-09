<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Context\Snippet\Generator;

use Behat\Behat\Context\Environment\Context_Environment;
/**
 * Decorates actual identifier and caches its answers per suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Cached_Context_Identifier implements Target_Context_Identifier
{
    private array $context_classes = [];
    /**
     * Initialise the identifier.
     */
    public function __construct(private readonly Target_Context_Identifier $decorated_identifier)
    {
    }
    public function guess_target_context_class(Context_Environment $environment)
    {
        $suite_key = $environment->get_suite()->get_name();
        if (array_key_exists($suite_key, $this->context_classes)) {
            return $this->context_classes[$suite_key];
        }
        return $this->context_classes[$suite_key] = $this->decorated_identifier->guess_target_context_class($environment);
    }
}