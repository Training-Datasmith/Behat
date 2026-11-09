<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Service_Container;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides additional service finding functionality.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
final class Service_Processor
{
    /**
     * Finds and sorts (by priority) service references by provided tag.
     *
     * @param string           $tag
     *
     * @return list<Reference>
     */
    public function find_and_sort_tagged_services(ContainerBuilder $container, $tag): array
    {
        $service_tags = [];
        foreach ($container->findTaggedServiceIds($tag) as $id => $tags) {
            $first_tags = current($tags);
            $service_tags[] = array_merge(['priority' => 0], $first_tags, ['id' => $id]);
        }
        usort($service_tags, fn (array $tag1, array $tag2): int|float => $tag2['priority'] - $tag1['priority']);

        return array_map(fn (array $tag): Reference => new Reference($tag['id']), $service_tags);
    }

    /**
     * Processes wrappers of a service, found by provided tag.
     *
     * The wrappers are applied by descending priority.
     * The first argument of the wrapper service receives the inner service.
     *
     * @param string           $target     The id of the service being decorated
     */
    public function process_wrapper_services(ContainerBuilder $container, $target, $wrapper_tag): void
    {
        $references = $this->find_and_sort_tagged_services($container, $wrapper_tag);
        foreach ($references as $reference) {
            $id = (string) $reference;
            $renamed_id = $id . '.inner';
            // This logic is based on Symfony\Component\DependencyInjection\Compiler\DecoratorServicePass
            // we create a new alias/service for the service we are replacing
            // to be able to reference it in the new one
            if ($container->hasAlias($target)) {
                $alias = $container->getAlias($target);
                $public = $alias->isPublic();
                $container->setAlias($renamed_id, new Alias((string) $alias, false));
            } else {
                $definition = $container->getDefinition($target);
                $public = $definition->isPublic();
                $definition->setPublic(false);
                $container->setDefinition($renamed_id, $definition);
            }
            $container->setAlias($target, new Alias($id, $public));
            // Replace the reference so that users don't need to bother about the way the inner service is referenced
            $wrapping_service = $container->getDefinition($id);
            $wrapping_service->replaceArgument(0, new Reference($renamed_id));
        }
    }
}
