<?php

namespace App;

class Validator
{

    public function validate(array $data, array $rules)
    {
        // $value is one row from one csv file
        // rules will contain what we need to check for ...

        foreach ($data as $row) {
            // TODO
        }

        $validated = true;

        return $validated;
    }
}
