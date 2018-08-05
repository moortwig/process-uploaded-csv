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

        foreach ($row as $key => $item) {
            $rule = $rules[$key];
            $prefix = 'File: ' . $fileName . ' | Column: ' . $key . ' | Line: ' . $line .' | ';
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