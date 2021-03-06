<?php

namespace Subscribo\Omnipay\Shared\Helpers;

/**
 * Class AddressParser
 *
 * @package Subscribo\OmnipaySubscriboShared
 */
class AddressParser
{
    /**
     * Parse first line of address into an array, containing street name and a house number (or null, if nothing like house number was found)
     * @param string $firstLine
     * @return array
     */
    public static function parseFirstLine($firstLine)
    {
        $parts = static::splitFirstLine($firstLine);
        $street = array_shift($parts);
        $houseNumber = null;
        if (static::couldBePartOfHouseNumber(end($parts))) {
            $houseNumber = array_pop($parts);
        }
        while ($parts) {
            $first = reset($parts);
            if (preg_match('/\\d/', $first)) {
                break;
            }
            $street .= ' '.array_shift($parts);
        }
        while ($parts) {
            if (static::couldBePartOfHouseNumber(end($parts))) {
                $houseNumber = array_pop($parts).' '.$houseNumber;
            } else {
                break;
            }
        }
        while ($parts) {
            $street .= ' '.array_shift($parts);
        }
        $street = trim($street);
        if ($houseNumber) {
            $houseNumber = trim($houseNumber);
        }
        return [$street, $houseNumber];
    }

    /**
     * Tries to split first line into parts
     *
     * @param string $firstLine
     * @return array
     */
    protected static function splitFirstLine($firstLine)
    {
        $parts = preg_split('/\\s/', $firstLine, null, PREG_SPLIT_NO_EMPTY);
        if (count($parts) > 1) {
            return $parts;
        }
        $parts = explode(',', $firstLine);
        if (count($parts) > 1) {
            return $parts;
        }

        return preg_split('/(\\d+\\D*)/', $firstLine, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    }

    /**
     * @param string $toCheck
     * @return bool
     */
    protected static function couldBePartOfHouseNumber($toCheck)
    {
        if (preg_match('/\\d/', $toCheck)) {
            return true;
        }
        $simplified = ltrim(trim($toCheck, '-_~\\/|#()[]{}<>,'), '.');
        if (strlen($simplified) < 2) {
            return true;
        }
        return static::containOnlyRomanLiterals($toCheck);
    }

    /**
     * @param string $toCheck
     * @return bool
     */
    protected static function containOnlyRomanLiterals($toCheck)
    {
        $parts = preg_split('/[[:^alpha:]]/', $toCheck, null, PREG_SPLIT_NO_EMPTY);
        foreach ($parts as $part) {
            if (( ! static::isRomanLiteral($part))) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $toCheck
     * @return bool
     */
    protected static function isRomanLiteral($toCheck)
    {
        if (($toCheck !== strtolower($toCheck)) and ($toCheck !== strtoupper($toCheck))) {
            return false;
        }
        $upperCased = strtoupper($toCheck);
        $pattern = '/^C?M*C?D?X?C*X?L?X*I?V?I*$/';
        if (preg_match($pattern, $upperCased)) {
            return true;
        }
        return false;
    }
}
