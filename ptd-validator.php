<?php namespace jsonptd;

function verify($value, $typeName, $typeLib) {
    if (!array_key_exists($typeName, $typeLib)) return false;
    $type = $typeLib[$typeName];
    return verifyWithType($value, $type, $typeLib);
}

function verifyWithType($value, $type, $typeLib) {
    if ($value === null) return false;
    if (array_key_exists('ov.ptd_rec', $type)) {
        $fieldHash = $type['ov.ptd_rec'];
        $fieldNames = array_keys($fieldHash);
        $valueKeys = array_keys($value);

        if (count($fieldNames) !== count($valueKeys)) return false;
        foreach ($fieldNames as $r) {
            if (!in_array($r, $valueKeys)) return false;
        }
        foreach ($fieldNames as $tk) {
            if (!verifyWithType($value[$tk], $fieldHash[$tk], $typeLib)) return false;
        }
        return true;
  
    } elseif (array_key_exists('ov.ptd_arr', $type)) {
        $elemType = $type['ov.ptd_arr'];
        if (!is_array($value) || array_keys($value) !== range(0, count($value) - 1)) return false;
        foreach ($value as $elem) {
            if (!verifyWithType($elem, $elemType, $typeLib)) return false;
        }
        return true;
   
    } elseif (array_key_exists('ov.ptd_hash', $type)) {
        $valueKeys = array_keys($value);
        $elemType = $type['ov.ptd_hash'];

        for ($i = 0; $i < count($valueKeys); $i++) {
            $keyValue = $value[$valueKeys[$i]];
            if (!verifyWithType($keyValue, $elemType, $typeLib)) return false;
        }
        return true;
    
    } elseif (array_key_exists('ov.ptd_var', $type)) {
        $fieldHash = $type['ov.ptd_var'];
        $variantName = array_keys($value)[0];
        if (!substr($variantName, 0, 3) === 'ov.' || !array_key_exists(substr($variantName, 3), $fieldHash)) return false;

        $variantType = $fieldHash[substr($variantName, 3)];
        $hasParam = array_keys($variantType)[0] === 'ov.with_param';

        return $hasParam ? verifyWithType($value[$variantName], $variantType['ov.with_param'], $typeLib) : $value[$variantName] === null;
    
    } elseif (array_key_exists('ov.ptd_ref', $type)) {
        $refName = $type['ov.ptd_ref'];
        return verifyWithType($value, $typeLib[$refName], $typeLib);

    } elseif (array_key_exists('ov.ptd_utf8', $type)) {
        return is_string($value);

    } elseif (array_key_exists('ov.ptd_bytearray', $type)) {
        if (!is_string($value)) return false;
        
        for ($i = 0; $i < mb_strlen($value); $i++) {
            if (mb_ord(mb_substr($value, $i, 1)) < 0 || mb_ord(mb_substr($value, $i, 1)) > 255) return false;
        }
        return true;

    } elseif (array_key_exists('ov.ptd_int', $type)) {
        return is_int($value);

    } elseif (array_key_exists('ov.ptd_double', $type)) {
        return is_float($value);

    } elseif (array_key_exists('ov.ptd_bool', $type)) {
        return is_bool($value);

    } elseif (array_key_exists('ov.ptd_date', $type)) {
        if (!is_string($value)) return false;
        return preg_match('/^[0-9]{4}(-[0-9]{2}){2}( [0-9]{2}(:[0-9]{2}){2})?$/', $value) === 1;

    } elseif (array_key_exists('ov.ptd_decimal', $type)) {
        $fieldHash = $type['ov.ptd_decimal'];
        return round($value, $fieldHash['scale']) === $value &&
            ((int)$value === 0 || strlen(strval((int)$value)) <= $fieldHash['size'] - $fieldHash['scale']);
    }
}
?>