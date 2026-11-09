<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Definition\Translator;

use Behat\Behat\Definition\Definition;
use Behat\Testwork\Suite\Suite;
/**
 * Translates definitions using translator component.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Definition_Translator
{
    /**
     * @var array<string, string>
     */
    private array $translation_cache = [];
    /**
     * Initialises definition translator.
     */
    public function __construct(private readonly Translator_Interface $translator)
    {
    }
    /**
     * Attempts to translate definition using translator and produce translated one on success.
     *
     * @param string|null $language
     */
    public function translate_definition(Suite $suite, Definition $definition, $language = null): \Behat\Behat\Definition\Translator\Translated_Definition|\Behat\Behat\Definition\Definition
    {
        $assets_id = $suite->get_name();
        $pattern = $definition->get_pattern();
        $cache_key = sprintf('%s|%s|%s', $assets_id, $pattern, $language ?? $this->get_locale());
        if (isset($this->translation_cache[$cache_key])) {
            $translated_pattern = $this->translation_cache[$cache_key];
        } else {
            $translated_pattern = $this->translator->trans($pattern, [], $assets_id, $language);
            $this->translation_cache[$cache_key] = $translated_pattern;
        }
        if ($pattern != $translated_pattern) {
            return new Translated_Definition($definition, $translated_pattern, $language);
        }
        return $definition;
    }
    public function translate_info_text(string $info_text, array $parameters): string
    {
        return $this->translator->trans($info_text, $parameters, 'output');
    }
    public function get_locale()
    {
        return $this->translator->get_locale();
    }
}