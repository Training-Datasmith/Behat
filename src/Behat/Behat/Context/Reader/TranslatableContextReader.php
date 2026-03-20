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
use Behat\Behat\Context\Exception\Unknown_Translation_Resource_Exception;
use Behat\Behat\Context\Translatable_Context;
use ReflectionClass;
use Symfony\Component\Translation\Translator;
/**
 * Reads translation resources from translatable contexts.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Translatable_Context_Reader implements Context_Reader
{
    /**
     * Initializes loader.
     */
    public function __construct(private Translator $translator)
    {
    }
    /**
     * @see TranslatableContext
     */
    public function read_context_callees(Context_Environment $environment, $context_class): array
    {
        $refl_class = new ReflectionClass($context_class);
        if (!$refl_class->implements_interface(Translatable_Context::class)) {
            return [];
        }
        $assets_id = $environment->get_suite()->get_name();
        foreach (call_user_func([$context_class, 'getTranslationResources']) as $path) {
            $this->add_translation_resource($path, $assets_id);
        }
        return [];
    }
    /**
     * Adds translation resource.
     *
     * @param string $assetsId
     * @throws UnknownTranslationResourceException
     */
    private function add_translation_resource(string $path, $assets_id): void
    {
        match ($ext = pathinfo($path, PATHINFO_EXTENSION)) {
            'yml' => $this->add_translator_resource('yaml', $path, basename($path, '.' . $ext), $assets_id),
            'xliff' => $this->add_translator_resource('xliff', $path, basename($path, '.' . $ext), $assets_id),
            'php' => $this->add_translator_resource('php', $path, basename($path, '.' . $ext), $assets_id),
            default => throw new Unknown_Translation_Resource_Exception(sprintf('Can not read translations from `%s`. File type is not supported.', $path), $path),
        };
    }
    /**
     * Adds resource to translator instance.
     *
     * @param string $path
     * @param string $assetsId
     */
    private function add_translator_resource(string $type, $path, string $language, $assets_id): void
    {
        $this->translator->add_resource($type, $path, $language, $assets_id);
    }
}