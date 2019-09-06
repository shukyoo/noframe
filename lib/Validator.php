<?php namespace Lib;

class Validator
{
    /**
     * single value:
     * validate('test', 'required|email|length:10,20')
     * validate('test', [
     *     'required' => 'value is required',
     *     'email' => 'value must be a valid email'
     *     'length:10,20' => 'value length must in 10-20'
     * ], $message)
     *
     * multi values:
     * validate(['aa' => 'hello', 'bb' => 'world'], 'required | string')
     * validate(['aa' => 'hello', 'bb' => 'world'], ['aa' => 'required | string', 'bb' => 'in:1,2,3'])
     *
     * multi values with message:
     * validate(['aa' => 'hello', 'bb' => 'world'], [
     *      'aa' => ['required' => 'aa is required', 'string' => 'aa must be a string'],
     *      'bb' => ['in:1,2,3' => 'bb must in 1,2,3']
     * ], $message)
     *
     * @param mixed $value
     * @param mixed $rule
     * @param string $message errmsg receive
     * @return bool
     * @throws \Exception
     */
    public static function validate($value, $rule, &$message = '')
    {
        if (!is_array($value)) {
            $value = [$value];
            $rule = [$rule];
        } elseif (is_array($value) && !is_array($rule)) {
            $rule_str = $rule;
            $rule = [];
            foreach ($value as $k=>$v) {
                $rule[$k] = $rule_str;
            }
        }

        // get all validator methods
        $methods = get_class_methods(get_called_class());

        foreach ($rule as $k=>$item) {
            if (!is_array($item)) {
                $item = explode('|', $item);
                $item = array_combine($item, array_fill(0, count($item), ''));
            }
            $v = isset($value[$k]) ? $value[$k] : null;
            foreach ($item as $one => $msg) {
                if (strpos($one, ':')) {
                    $p = strpos($one, ':');
                    $param = substr($one, $p+1);
                    $method = trim(substr($one, 0, $p));
                } else {
                    $method = trim($one);
                    $param = null;
                }
                if (!in_array($method, $methods)) {
                    $method = 'is'. ucfirst($method);
                    if (!in_array($method, $methods)) {
                        throw new \Exception('Invalid validation method:'. $one);
                    }
                }
                if (!self::$method($v, $param)) {
                    $message = $msg;
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Not empty
     * @param mixed $value
     * @return bool
     */
    public static function required($value)
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif ((is_array($value) || $value instanceof \Countable) && count($value) < 1) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isDate($value)
    {
        if (strtotime($value) === false) {
            return false;
        }
        $date = date_parse($value);
        return checkdate($date['month'], $date['day'], $date['year']);
    }

    /**
     * @param string $value
     * @param string $format
     * @return bool
     */
    public static function isDatetime($value, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $value);
        return $d && $d->format($format) == $value;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isUrl($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param string $value
     * @param string $pattern
     * @return bool
     */
    public static function isMatch($value, $pattern)
    {
        return (bool)preg_match($pattern, $value);
    }

    /**
     * @param string $value
     * @param string|array $param
     * @return bool
     */
    public static function length($value, $param)
    {
        if (!is_array($param)) {
            $param = explode(',', $param);
        }
        $min = $param[0];
        $max = null;
        if (isset($param[1])) {
            $max = $param[1];
        }
        $len = mb_strlen($value, 'UTF-8');
        return ($len >= $min && (null === $max || $len <= $max));
    }

    /**
     * @param mixed $value
     * @param string|array $param
     * @return bool
     */
    public static function range($value, $param)
    {
        if (!is_array($param)) {
            $param = explode(',', $param);
        }
        $min = $param[0];
        $max = null;
        if (isset($param[1])) {
            $max = $param[1];
        }
        return ($value >= $min && (null === $max || $value <= $max));
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isBool($value)
    {
        return in_array($value, [true, false, 0, 1, '0', '1'], true);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isString($value)
    {
        return is_string($value);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isInt($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isNumeric($value)
    {
        return is_numeric($value);
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isArray($value)
    {
        return is_array($value);
    }

    /**
     * @param mixed $value
     * @param mixed $param
     * @return bool
     */
    public static function isEqual($value, $param)
    {
        return $value == $param;
    }

    /**
     * @param mixed $value
     * @param mixed $param
     * @return bool
     */
    public static function isSame($value, $param)
    {
        return $value === $param;
    }

    /**
     * @param mixed $value
     * @param mixed $param
     * @return bool
     */
    public static function isSameCi($value, $param)
    {
        return strtolower($value) === strtolower($param);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isIp($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * If is a json
     * @param $value
     * @return bool
     */
    public static function isJson($value)
    {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * @param mixed $value
     * @param array|string $param
     * @return bool
     */
    public static function in($value, $param)
    {
        if (!is_array($param)) {
            $param = explode(',', $param);
        }
        return in_array($value, $param);
    }

    /**
     * If not in array
     * @param mixed $value
     * @param array|string $param
     * @return bool
     */
    public static function notin($value, $param)
    {
        if (!is_array($param)) {
            $param = explode(',', $param);
        }
        return !in_array($value, $param);
    }
}