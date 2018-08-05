<?php

namespace App;

class Validator
{
    public function validate(array $file, array $rules) {
        $fileName = key($file); // eg "2018-01-02-665327.csv"

        $errors = [];
        foreach ($file[$fileName] as $index => $row) {
            $line = $index + 2; // Adjust index to match actual line number in csv file (headers being on line 1 ...)
            foreach ($row as $key => $item) {
                $rule = $rules[$key];
                if (in_array('timestamp', $rule)) {
                    $validated = $this->validateDateTime($item);
                    if (!$validated) {
                        $errors[] = 'File: ' . $fileName . ' | Item in column ' . $key . ' on line ' . $line . ' is not a valid datetime.';
                    }
                }
                if (in_array('format=yyyy-mm-dd hh:mm:ss', $rule)) {
                    $validated = $this->validateDatetimeFormat($item);
                    if (!$validated) {
                        $errors[] = 'File: ' . $fileName . ' | Item in column ' . $key . ' on line ' . $line . ' does not have a valid datetime format.';
                    }
                }
                if (in_array('required', $rule)) {
                    $validated = $this->validateRequired($item);
                    if (!$validated) {
                        $errors[] = 'File: ' . $fileName . ' | Item in column ' . $key . ' on line ' . $line . ' is missing.';
                    }
                }
            }
        }

        return $errors;
    }



//    public function validate(array $row, array $rules)
//    {
//        $validated = [];
//        foreach ($row as $key => $field) {
////            dd($key); // eventDatetime"
////            dd($field); // string(19) "2018-01-02 10:27:36"
//
//            $rule = $rules[$key];
////            dd($rule); // ['timestamp', 'format=yyyy-mm-dd hh:mm:ss', 'required']
//
//            // FIXME for better log messages, need to pass in more information to the validation methods (or from there get something ...)
//            // "Hardcoded" for now ... let's see how this can be optimised
//            if (in_array('timestamp', $rule)) {
//                // true!
//                // meaning we need to validate if field is a timestamp
//                $validated[] = $this->validateDateTime($field);
//            }
//            if (in_array('format=yyyy-mm-dd hh:mm:ss', $rule)) {
//                $validated[] = $this->validateDatetimeFormat($field);
//            }
//            if (in_array('required', $rule)) {
//                $validated[] = $this->validateRequired($field);
//            }
//        }
//
//        // if ANY of above has been validated to false, return false so we may handle the row accordingly
//        return in_array(false, $validated);
//    }

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
        if (mb_strlen($error) > 0) {
            Logger::log(LOG_ERR, $item . ' is ' . $error . '.');
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