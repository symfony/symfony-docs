<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfYamlInline implements a YAML parser/dumper for the YAML inline syntax.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfYamlInline.class.php 17749 2009-04-29 11:54:22Z fabien $
 */
class sfYamlInline
{
  /**
   * Load YAML into a PHP array.
   *
   * @param string $value YAML
   *
   * @return array PHP array
   */
  static public function load($value)
  {
    $value = trim($value);

    if (0 == strlen($value))
    {
      return '';
    }

    switch ($value[0])
    {
      case '[':
        return self::parseSequence($value);
      case '{':
        return self::parseMapping($value);
      default:
        return self::parseScalar($value);
    }
  }

  /**
   * Dumps PHP array to YAML.
   *
   * @param mixed $value PHP
   *
   * @return string YAML
   */
  static public function dump($value)
  {
    switch (true)
    {
      case is_resource($value):
        throw new InvalidArgumentException('Unable to dump PHP resources in a YAML file.');
      case is_object($value):
        return '!!php/object:'.serialize($value);
      case is_array($value):
        return self::dumpArray($value);
      case is_null($value):
        return 'null';
      case true === $value:
        return 'true';
      case false === $value:
        return 'false';
      case ctype_digit($value):
        return is_string($value) ? "'$value'" : (int) $value;
      case is_numeric($value):
        return is_infinite($value) ? str_ireplace('INF', '.Inf', strval($value)) : (is_string($value) ? "'$value'" : $value);
      case false !== strpos($value, "\n"):
        return sprintf('"%s"', str_replace(array('"', "\n", "\r"), array('\\"', '\n', '\r'), $value));
      case preg_match('/[ \s \' " \: \{ \} \[ \] , & \* \#]/x', $value):
        return sprintf("'%s'", str_replace('\'', '\'\'', $value));
      case '' == $value:
        return "''";
      case preg_match(self::getTimestampRegex(), $value):
        return "'$value'";
      case in_array(strtolower($value), array('true', 'on', '+', 'yes', 'y')):
        return "'$value'";
      case in_array(strtolower($value), array('false', 'off', '-', 'no', 'n')):
        return "'$value'";
      default:
        return $value;
    }
  }

  /**
   * Dumps PHP array to YAML
   *
   * @param array $value The array to dump
   *
   * @return string YAML
   */
  static protected function dumpArray($value)
  {
    // array
    $keys = array_keys($value);
    if (
      (1 == count($keys) && '0' == $keys[0])
      ||
      (count($keys) > 1 && array_reduce($keys, create_function('$v,$w', 'return (integer) $v + $w;'), 0) == count($keys) * (count($keys) - 1) / 2))
    {
      $output = array();
      foreach ($value as $val)
      {
        $output[] = self::dump($val);
      }

      return sprintf('[%s]', implode(', ', $output));
    }

    // mapping
    $output = array();
    foreach ($value as $key => $val)
    {
      $output[] = sprintf('%s: %s', self::dump($key), self::dump($val));
    }

    return sprintf('{ %s }', implode(', ', $output));
  }

  /**
   * Parses scalar to yaml
   *
   * @param scalar $scalar
   * @param string $delimiters
   * @param array  $stringDelimiter
   * @param integer $i
   * @param boolean $evaluate
   *
   * @return string YAML
   */
  static public function parseScalar($scalar, $delimiters = null, $stringDelimiters = array('"', "'"), &$i = 0, $evaluate = true)
  {
    if (in_array($scalar[$i], $stringDelimiters))
    {
      // quoted scalar
      $output = self::parseQuotedScalar($scalar, $i);

      // skip next delimiter
      ++$i;
    }
    else
    {
      // "normal" string
      if (!$delimiters)
      {
        $output = substr($scalar, $i);
        $i += strlen($output);

        // remove comments
        if (false !== $strpos = strpos($output, ' #'))
        {
          $output = rtrim(substr($output, 0, $strpos));
        }
      }
      else if (preg_match('/^(.+?)('.implode('|', $delimiters).')/', substr($scalar, $i), $match))
      {
        $output = $match[1];
        $i += strlen($output);
      }
      else
      {
        throw new InvalidArgumentException(sprintf('Malformed inline YAML string (%s).', $scalar));
      }

      $output = $evaluate ? self::evaluateScalar($output) : $output;
    }

    return $output;
  }

  /**
   * Parses quotes scalar
   *
   * @param string $scalar
   * @param integer $i
   *
   * @return string YAML
   */
  static protected function parseQuotedScalar($scalar, &$i)
  {
    $delimiter = $scalar[$i];
    ++$i;
    $buffer = '';
    $len = strlen($scalar);
    $escaped = '"' == $delimiter ? '\\"' : "''";

    while ($i < $len)
    {
      if (isset($scalar[$i + 1]) && $escaped == $scalar[$i].$scalar[$i + 1])
      {
        $buffer .= $delimiter;
        ++$i;
      }
      else if ($delimiter == $scalar[$i])
      {
        break;
      }
      else
      {
        $buffer .= $scalar[$i];
      }

      ++$i;
    }

    if ('"' == $delimiter)
    {
      // evaluate the string
      $buffer = str_replace(array('\\n', '\\r'), array("\n", "\r"), $buffer);
    }

    return $buffer;
  }

  /**
   * Parse sequence to yaml
   *
   * @param string $sequence
   * @param integer $i
   *
   * @return string YAML
   */
  static protected function parseSequence($sequence, &$i = 0)
  {
    $output = array();
    $len = strlen($sequence);
    $i += 1;

    // [foo, bar, ...]
    while ($i < $len)
    {
      switch ($sequence[$i])
      {
        case '[':
          // nested sequence
          $output[] = self::parseSequence($sequence, $i);
          break;
        case '{':
          // nested mapping
          $output[] = self::parseMapping($sequence, $i);
          break;
        case ']':
          return $output;
        case ',':
        case ' ':
          break;
        default:
          $isQuoted = in_array($sequence[$i], array('"', "'"));
          $value = self::parseScalar($sequence, array(',', ']'), array('"', "'"), $i);

          if (!$isQuoted && false !== strpos($value, ': '))
          {
            // embedded mapping?
            try
            {
              $value = self::parseMapping('{'.$value.'}');
            }
            catch (InvalidArgumentException $e)
            {
              // no, it's not
            }
          }

          $output[] = $value;

          --$i;
      }

      ++$i;
    }

    throw new InvalidArgumentException(sprintf('Malformed inline YAML string %s', $sequence));
  }

  /**
   * Parses mapping.
   *
   * @param string $mapping
   * @param integer $i
   *
   * @return string YAML
   */
  static protected function parseMapping($mapping, &$i = 0)
  {
    $output = array();
    $len = strlen($mapping);
    $i += 1;

    // {foo: bar, bar:foo, ...}
    while ($i < $len)
    {
      switch ($mapping[$i])
      {
        case ' ':
        case ',':
          ++$i;
          continue 2;
        case '}':
          return $output;
      }

      // key
      $key = self::parseScalar($mapping, array(':', ' '), array('"', "'"), $i, false);

      // value
      $done = false;
      while ($i < $len)
      {
        switch ($mapping[$i])
        {
          case '[':
            // nested sequence
            $output[$key] = self::parseSequence($mapping, $i);
            $done = true;
            break;
          case '{':
            // nested mapping
            $output[$key] = self::parseMapping($mapping, $i);
            $done = true;
            break;
          case ':':
          case ' ':
            break;
          default:
            $output[$key] = self::parseScalar($mapping, array(',', '}'), array('"', "'"), $i);
            $done = true;
            --$i;
        }

        ++$i;

        if ($done)
        {
          continue 2;
        }
      }
    }

    throw new InvalidArgumentException(sprintf('Malformed inline YAML string %s', $mapping));
  }

  /**
   * Evaluates scalars and replaces magic values.
   *
   * @param string $scalar
   *
   * @return string YAML
   */
  static protected function evaluateScalar($scalar)
  {
    $scalar = trim($scalar);

    switch (true)
    {
      case 'null' == strtolower($scalar):
      case '' == $scalar:
      case '~' == $scalar:
        return null;
      case 0 === strpos($scalar, '!str'):
        return (string) substr($scalar, 5);
      case 0 === strpos($scalar, '! '):
        return intval(self::parseScalar(substr($scalar, 2)));
      case 0 === strpos($scalar, '!!php/object:'):
        return unserialize(substr($scalar, 13));
      case ctype_digit($scalar):
        $raw = $scalar;
        $cast = intval($scalar);
        return '0' == $scalar[0] ? octdec($scalar) : (((string) $raw == (string) $cast) ? $cast : $raw);
      case in_array(strtolower($scalar), array('true', 'on', '+', 'yes', 'y')):
        return true;
      case in_array(strtolower($scalar), array('false', 'off', '-', 'no', 'n')):
        return false;
      case is_numeric($scalar):
        return '0x' == $scalar[0].$scalar[1] ? hexdec($scalar) : floatval($scalar);
      case 0 == strcasecmp($scalar, '.inf'):
      case 0 == strcasecmp($scalar, '.NaN'):
        return -log(0);
      case 0 == strcasecmp($scalar, '-.inf'):
        return log(0);
      case preg_match('/^(-|\+)?[0-9,]+(\.[0-9]+)?$/', $scalar):
        return floatval(str_replace(',', '', $scalar));
      case preg_match(self::getTimestampRegex(), $scalar):
        return strtotime($scalar);
      default:
        return (string) $scalar;
    }
  }

  static protected function getTimestampRegex()
  {
    return <<<EOF
    ~^
    (?P<year>[0-9][0-9][0-9][0-9])
    -(?P<month>[0-9][0-9]?)
    -(?P<day>[0-9][0-9]?)
    (?:(?:[Tt]|[ \t]+)
    (?P<hour>[0-9][0-9]?)
    :(?P<minute>[0-9][0-9])
    :(?P<second>[0-9][0-9])
    (?:\.(?P<fraction>[0-9]*))?
    (?:[ \t]*(?P<tz>Z|(?P<tz_sign>[-+])(?P<tz_hour>[0-9][0-9]?)
    (?::(?P<tz_minute>[0-9][0-9]))?))?)?
    $~x
EOF;
  }
}
