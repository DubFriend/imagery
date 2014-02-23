<?php
class imagery_parser {
    function parse($value) {

        $type_map = array(
            '' => 'pixel',
            'px' => 'pixel',
            '%' => 'percent',
            'min' => 'minimum',
            'max' => 'maximum'
        );

        if(is_null($value)) {
            return array(array('type' => 'null'));
        }
        else if(is_numeric($value)) {
            return array(array(
                'type' => 'pixel',
                'value' => floatval($value)
            ));
        }
        else if(is_string($value)) {
            return array_map(function ($token) use ($type_map) {
                $pieces = array('type' => $type_map[$this->get_type($token)]);
                $val = $this->get_value($token);
                if($val) {
                    $pieces['value'] = $val;
                }
                return $pieces;
            }, explode(' ', $value));
        }
        else {
            throw new Exception("invalid parse value");
        }
    }

    private function get_type($token) {
        $matches;
        preg_match('/[^0-9\.]*$/', $token, $matches);
        return $matches[0];
    }

    private function get_value($token) {
        $matches;
        preg_match('/^[0-9\.]*/', $token, $matches);
        return is_null($matches[0]) ? null : floatval($matches[0]);
    }
}
?>
