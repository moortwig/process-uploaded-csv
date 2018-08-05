<?php

namespace App;

class Validator
{
    public function validate(array $file, array $rules)
    {
        $fileName = key($file);

        $errors = [];
        foreach ($file[$fileName] as $index => $row) {
            $line = $index + 2; // Adjust index to match actual line number in csv file (headers being on line 1 ...)

            $rowErrors = $this->validateRow($row, $rules, $fileName, $line);
            if ($rowErrors) {
                $errors = array_merge($errors, $rowErrors);
            }

        }

        return $errors;
    }


    private function validateRow($row, $rules, $fileName, $line)
    {
        $errors = [];
        // FIXME ensure rules are all lower case, and contain no whitespace except for the format rule ... ^^

        foreach ($row as $key => $item) {
            $rule = $rules[$key];
            $prefix = 'File: ' . $fileName . ' | Column: ' . $key . ' | Line: ' . $line . ' | ';
            if (in_array('timestamp', $rule)) {
                $validated = $this->validateDateTime($item);
                if (!$validated) {
                    $errors[] = $prefix . 'Not a valid datetime.';
                }
            }
            if (in_array('format=yyyy-mm-dd hh:mm:ss', $rule)) {
                $validated = $this->validateDatetimeFormat($item);
                if (!$validated) {
                    $errors[] = $prefix . 'Not a valid datetime format.';
                }
            }
            if (in_array('required', $rule)) {
                $validated = $this->validateRequired($item);
                if (!$validated) {
                    $errors[] = $prefix . 'Missing value';
                }
            }
            // NEW, UNTESTED, VALIDATIONS BELOW THIS COMMENT!!! TODO test these :)
            if (in_array('string', $rule)) {
                $validated = $this->validateString($item);
                if (!$validated) {
                    $errors[] = $prefix . 'Not a string.';
                }
            }
            if (in_array('min=1', $rule)) { // FIXME hardcoded rule
                $validated = $this->validateMinValue($item, 'min=1');
                if (!$validated) {
                    $errors[] = $prefix . 'String too short.';
                }
            }
            if (in_array('max=1', $rule)) { // FIXME hardcoded rule
                $validated = $this->validateMaxValue($item, 'max=1');
                if (!$validated) {
                    $errors[] = $prefix . 'String too long.';
                }
            }
            if (in_array('integer', $rule)) {
                $validated = $this->validateInteger($item);
                if (!$validated) {
                    $errors[] = $prefix . 'Not an integer.';
                }
            }
            if (in_array('decimal', $rule)) {
                $validated = $this->validateDecimal($item);
                if (!$validated) {
                    $errors[] = $prefix . 'Not a decimal.';
                }
            }
            if (in_array('length=3', $rule)) { // FIXME hardcoded rule
                $validated = $this->validateLength($item, 'length=3');
                if (!$validated) {
                    $errors[] = $prefix . 'String not of correct length.';
                }
            }
            if (in_array('required_if=eventValue', $rule)) { // FIXME hardcoded rule
                $validated = $this->validateRequiredIf($item, 'required_if=eventValue', $row);
                if (!$validated) {
                    $errors[] = $prefix . ' Missing related value.'; // FIXME better message string?
                }
            }
        }

        return count($errors) > 0 ? $errors : null;
    }

    /**
     * Attempts to convert string to Unix timestamp.
     *
     * @param $item
     * @return bool
     */
    private function validateDateTime($item)
    {
        $datetime = strtotime($item);

        return is_int($datetime);
    }

    /**
     * Checks if item is an integer.
     *
     * @param $item
     * @return bool
     */
    private function validateInteger($item)
    {
        return is_int($item);
    }

    /**
     * Checks if item is a decimal.
     *
     * @param $item
     * @return bool
     */
    private function validateDecimal($item)
    {
        return is_float($item);
    }

    /**
     * Checks if item is a string.
     *
     * @param $item
     * @return bool
     */
    private function validateString($item)
    {
        return is_string($item);
    }

    /**
     * Checks if item has n minimum number of character
     *
     * @param $item
     * @param $rule
     * @return bool
     */
    private function validateMinValue($item, $rule)
    {
        $min = explode('=', $rule);

        return mb_strlen($item) >= $min[1]; // FIXME array_last like function
    }

    /**
     * Checks if item has n maximum number of character
     *
     * @param $item
     * @param $rule
     * @return bool
     */
    private function validateMaxValue($item, $rule)
    {
        $max = explode('=', $rule);

        return mb_strlen($item) >= $max[1]; // FIXME array_last like function
    }

    /**
     * Checks if item is of exact length
     *
     * @param $item
     * @param $rule
     * @return bool
     */
    private function validateLength($item, $rule)
    {
        $length = explode('=', $rule);

        return mb_strlen($item) === $length[1]; // FIXME array_last like function
    }

    /**
     * Checks if item
     *
     * @param $item
     * @param $rule
     * @param $row
     * @return bool
     */
    private function validateRequiredIf($item, $rule, $row)
    {
        $required = explode('=', $rule);
        $key = $required[1]; // FIXME array_last like function
        $otherItem = $row[$key];

        // How does !== 0 work on decimals? If it's 0.00, for instance ...
        // otherItem validations can be false, and item can be true - which is fine. Should be allowed
        // However, if otherItem val is true, and item is false - error should be returned
        if (isset($otherItem) && is_float($otherItem) && $otherItem !== 0) { // TODO definitely test this one ...
            // true
            return isset($item);
        } else {
            return true; // otherItem is false, so in this case item doesn't matter.
        }

//        return (isset($otherItem) && is_float($otherItem) && $otherItem !== 0) && isset($item);
    }

    /**
     * Uses regex to match the format and further ensures a match by checking number of characters.
     *
     * @param $item
     * @return bool
     */
    private function validateDatetimeFormat($item)
    {
        $regex = '/^[12][0-9]{3}-[0-1][0-9]-[0-3][0-9]\s[0-2][0-9]\:[0-6][0-9]\:[0-6][0-9]$/';

        return preg_match($regex, $item) && mb_strlen($item) === 19;
    }

    /**
     * Looks for empty string, null or empty array
     *
     * @param $item
     * @return bool
     */
    private function validateRequired($item)
    {
        $error = '';
        switch ($item) {
            case is_null($item):
                $error = 'null';
                break;
            case mb_strlen($item) === 0:
                $error = 'empty';
                break;
            case is_array($item) && count($item) === 0:
                $error = 'empty array';
                break;
        }

        return mb_strlen($error) === 0;
    }
}


// Helper
function dd($value = null)
{
    var_dump($value);
    PHP_EOL;
    die();
}

function dump($value = null)
{
    var_dump($value);
    PHP_EOL;
    PHP_EOL;
}