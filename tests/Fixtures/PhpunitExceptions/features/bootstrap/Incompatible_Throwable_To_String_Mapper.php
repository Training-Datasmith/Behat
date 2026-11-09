<?php

class Incompatible_Throwable_To_String_Mapper
{
    public static function map($thing): string
    {
        // Simulates what happens if the PHPUnit ThrowableToStringMapper class does not behave / take the types
        // that we expect
        throw new RuntimeException('Some internal problem');
    }
}
