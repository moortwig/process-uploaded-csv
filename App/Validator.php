<?php

namespace App;

class Validator
{
    public function validate(array $row, array $rules)
    {
        $validated = [];
        foreach ($row as $key => $field) {
//            dd($key); // eventDatetime"
//            dd($field); // string(19) "2018-01-02 10:27:36"

            $rule = $rules[$key];
//            dd($rule); // ['timestamp', 'format=yyyy-mm-dd hh:mm:ss', 'required']

            // FIXME for better log messages, need to pass in more information to the validation methods (or from there get something ...)
            // "Hardcoded" for now ... let's see how this can be optimised
            if (in_array('timestamp', $rule)) {
                // true!
                // meaning we need to validate if field is a timestamp
                $validated[] = $this->validateDateTime($field);
            }
            if (in_array('format=yyyy-mm-dd hh:mm:ss', $rule)) {
                $validated[] = $this->validateDatetimeFormat($field);
            }
            if (in_array('required', $rule)) {
                $validated[] = $this->validateRequired($field);
            }
        }

        // if ANY of above has been validated to false, return false so we may handle the row accordingly
        return in_array(false, $validated);
    }

    /**
     * Attempts to convert string to Unix timestamp.
     *
     * @param $field
     * @return bool
     */
    private function validateDateTime($field)
    {
        $datetime = strtotime($field);

        return is_int($datetime);
    }

    /**
     * Uses regex to match the format and further ensures a match by checking number of characters.
     *
     * @param $field
     * @return bool
     */
    private function validateDatetimeFormat($field)
    {
        $regex = '/^[12][0-9]{3}-[0-1][0-9]-[0-3][0-9]\s[0-2][0-9]\:[0-6][0-9]\:[0-6][0-9]$/';

        return preg_match($regex, $field) && mb_strlen($field) === 19;
    }

    /**
     * Looks for empty string, null or empty array
     *
     * @param $field
     * @return bool
     */
    private function validateRequired($field)
    {
        $error = '';
        switch ($field) {
            case is_null($field):
                $error = 'null';
                break;
            case mb_strlen($field) === 0:
                $error = 'empty';
                break;
            case is_array($field) && count($field) === 0:
                $error = 'empty array';
                break;
        }
        if (mb_strlen($error) > 0) {
            Logger::log(LOG_ERR, $field . ' is ' . $error . '.');
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