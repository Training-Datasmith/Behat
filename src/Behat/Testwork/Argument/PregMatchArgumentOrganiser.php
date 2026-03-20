<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Argument;

use Reflection_Function_Abstract;
/**
 * Organises arguments coming from preg_match results.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Preg_Match_Argument_Organiser implements Argument_Organiser
{
    /**
     * Initialises organiser.
     */
    public function __construct(private Argument_Organiser $base_organiser)
    {
    }
    public function organise_arguments(Reflection_Function_Abstract $function, array $arguments)
    {
        $cleaned_arguments = $this->cleanup_match_duplicates($arguments);
        return $this->base_organiser->organise_arguments($function, $cleaned_arguments);
    }
    /**
     * Cleans up provided preg_match match into a list of arguments.
     *
     * `preg_match` matches named arguments with named indexes and also
     * represents all arguments with numbered indexes. This method removes
     * duplication and also drops the first full match element from the
     * array.
     *
     * @return list<mixed>
     */
    private function cleanup_match_duplicates(array $match): array
    {
        $clean_match = array_slice($match, 1);
        $arguments = [];
        $keys = array_keys($clean_match);
        $num_keys = count($keys);
        for ($key_index = 0; $key_index < $num_keys; ++$key_index) {
            $key = $keys[$key_index];
            $arguments[$key] = $clean_match[$key];
            if ($this->is_key_a_string_and_next_one_is_an_integer($key_index, $keys)) {
                ++$key_index;
            }
        }
        return $arguments;
    }
    /**
     * Checks if key at provided index is a string and next key in the array is an integer.
     *
     * @param mixed[] $keys
     */
    private function is_key_a_string_and_next_one_is_an_integer(int $key_index, array $keys): bool
    {
        $key_is_a_string = is_string($keys[$key_index]);
        $next_key_is_an_integer = isset($keys[$key_index + 1]) && is_int($keys[$key_index + 1]);
        return $key_is_a_string && $next_key_is_an_integer;
    }
}