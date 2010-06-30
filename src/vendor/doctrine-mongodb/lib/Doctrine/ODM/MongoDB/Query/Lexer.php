<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ODM\MongoDB\Query;

/**
 * Scans a MongoDB DQL query for tokens.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since       1.0
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author      Janne Vanhala <jpvanhal@cc.hut.fi>
 * @author      Roman Borschel <roman@code-factory.org>
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Lexer extends \Doctrine\Common\Lexer
{
    // All tokens that are not valid identifiers must be < 100
    const T_NONE                = 1;
    const T_INTEGER             = 2;
    const T_STRING              = 3;
    const T_INPUT_PARAMETER     = 4;
    const T_FLOAT               = 5;
    const T_COMMA               = 6;
    const T_DIVIDE              = 7;
    const T_DOT                 = 8;
    const T_EQUALS              = 9;
    const T_NOT_EQUALS          = 10;
    const T_GT                  = 11;
    const T_LT                  = 12;
    const T_LTE                 = 13;
    const T_GTE                 = 14;
    const T_OPEN_BRACKET        = 15;
    const T_CLOSE_BRACKET       = 16;
    const T_OPEN_CURLY_BRACE    = 17;
    const T_CLOSE_CURLY_BRACE   = 18;
    const T_OPEN_PARENTHESES    = 19;
    const T_CLOSE_PARENTHESES   = 20;

    // All tokens that are also identifiers should be >= 100
    const T_IDENTIFIER          = 100;
    const T_FIND                = 101;
    const T_UPDATE              = 102;
    const T_INSERT              = 103;
    const T_REMOVE              = 104;
    const T_GROUP               = 105;
    const T_IN                  = 106;
    const T_NOTIN               = 107;
    const T_MOD                 = 108;
    const T_ALL                 = 109;
    const T_SIZE                = 110;
    const T_EXISTS              = 111;
    const T_TYPE                = 112;
    const T_SORT                = 113;
    const T_LIMIT               = 114;
    const T_SKIP                = 115;
    const T_SELECT              = 116;
    const T_SET                 = 117;
    const T_UNSET               = 118;
    const T_INC                 = 119;
    const T_PUSH                = 120;
    const T_PUSHALL             = 121;
    const T_PULL                = 122;
    const T_PULLALL             = 123;
    const T_ADDTOSET            = 124;
    const T_ADDMANYTOSET        = 125;
    const T_POPFIRST            = 126;
    const T_POPLAST             = 127;
    const T_WHERE               = 128;
    const T_REDUCE              = 129;
    const T_MAP                 = 130;
    const T_AND                 = 131;
    const T_OR                  = 132;
    const T_TRUE                = 133;
    const T_FALSE               = 134;
    const T_ANY                 = 135;
    const T_NOT                 = 136;

    /**
     * @inheritdoc
     */
    protected function getCatchablePatterns()
    {
        return array(
            '[a-z_][a-z0-9_\:\\\]*[a-z0-9_]{1}',
            '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?',
            "'(?:[^']|'')*'",
            '\?[1-9][0-9]*|:[a-z][a-z0-9_]+'
        );
    }
    
    /**
     * @inheritdoc
     */
    protected function getNonCatchablePatterns()
    {
        return array('\s+');
    }

    /**
     * @inheritdoc
     */
    protected function _getType(&$value)
    {
        $type = self::T_NONE;

        // Recognizing numeric values
        if (is_numeric($value)) {
            $type = (strpos($value, '.') !== false || stripos($value, 'e') !== false) 
                    ? self::T_FLOAT : self::T_INTEGER;
            if ($type === self::T_INTEGER) {
                $value = (integer) $value;
            }
            return $type;
        }

        // Differentiate between quoted names, identifiers, input parameters and symbols
        if ($value[0] === "'") {
            $value = str_replace("''", "'", substr($value, 1, strlen($value) - 2));
            return self::T_STRING;
        } else if (ctype_alpha($value[0]) || $value[0] === '_') {
            $name = 'Doctrine\ODM\MongoDB\Query\Lexer::T_' . strtoupper($value);
            if (defined($name)) {
                $type = constant($name);
                if ($type > 100) {
                    return $type;
                }
            }
            return self::T_IDENTIFIER;
        } else if ($value[0] === ':' || $value[0] === '?') {
            return self::T_INPUT_PARAMETER;
        } else {
            switch ($value) {
                case '{':  return self::T_OPEN_CURLY_BRACE;
                case '}':  return self::T_CLOSE_CURLY_BRACE;
                case '[':  return self::T_OPEN_BRACKET;
                case ']':  return self::T_CLOSE_BRACKET;
                case '(':  return self::T_OPEN_PARENTHESES;
                case ')':  return self::T_CLOSE_PARENTHESES;
                case '.':  return self::T_DOT;
                case ',':  return self::T_COMMA;
                case '<=': return self::T_GTE;
                case '>=': return self::T_LTE;
                case '=':  return self::T_EQUALS;
                case '!=': return self::T_NOT_EQUALS;
                case '>':  return self::T_GT;
                case '<':  return self::T_LT;
                default:
                    // Do nothing
                    break;
            }
        }

        return $type;
    }
}