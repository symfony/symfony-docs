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

use Doctrine\ODM\MongoDB\Query,
    Doctrine\ODM\MongoDB\DocumentManager;

/**
 * A simple parser for MongoDB Document Query Language
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Parser
{
    /**
     * The DocumentManager instance for this query
     *
     * @var Doctrine\ODM\MongoDB\DocumentManager
     */
    private $_dm;

    /**
     * The lexer.
     *
     * @var Doctrine\ODM\MongoDB\Query\Lexer
     */
    private $_lexer;

    public function __construct(DocumentManager $dm)
    {
        $this->_dm = $dm;
        $this->_lexer = new Lexer;
    }

    public function parse($query, $parameters = array())
    {
        if (strpos($query, '?') !== false && strpos($query, ':') !== false) {
            throw new \InvalidArgumentException('Cannot mixed named and regular placeholders.');
        }

        $this->_lexer->reset();
        $this->_lexer->setInput($query);

        $query = $this->QueryLanguage($parameters);

        return $query;
    }

    /**
     * Attempts to match the given token with the current lookahead token.
     * If they match, updates the lookahead token; otherwise raises a syntax error.
     *
     * @param int|string token type or value
     * @return bool True if tokens match; false otherwise.
     */
    public function match($token)
    {
        if ( ! ($this->_lexer->lookahead['type'] === $token)) {
            $this->syntaxError($this->_lexer->getLiteral($token));
        }
        $this->_lexer->moveNext();
    }

    /**
     * Generates a new syntax error.
     *
     * @param string $expected Expected string.
     * @param array $token Optional token.
     * @throws AnnotationException
     */
    public function syntaxError($expected, $token = null)
    {
        if ($token === null) {
            $token = $this->_lexer->lookahead;
        }

        $message =  "Expected {$expected}, got ";

        if ($this->_lexer->lookahead === null) {
            $message .= 'end of string';
        } else {
            $message .= "'{$token['value']}' at position {$token['position']}";
        }

        $message .= '.';

        throw new \Doctrine\ODM\MongoDB\MongoDBException($message);
    }

    /**
     * QueryLanguage ::= FindQuery | InsertQuery | UpdateQuery | RemoveQuery
     */
    public function QueryLanguage(array &$parameters)
    {
        $this->_lexer->moveNext();

        $query = new Query($this->_dm);

        switch ($this->_lexer->lookahead['type']) {
            case Lexer::T_FIND:
                $this->FindQuery($query, $parameters);
                break;
            case Lexer::T_INSERT:
                $this->InsertQuery($query, $parameters);
                break;
            case Lexer::T_UPDATE:
                $this->UpdateQuery($query, $parameters);
                break;
            case Lexer::T_REMOVE:
                $this->RemoveQuery($query, $parameters);
                break;
            default:
                $this->syntaxError('FIND, INSERT, UPDATE or REMOVE');
                break;
        }
        return $query;
    }

    /**
     * FindQuery ::= FindClause [WhereClause] [MapClause] [ReduceClause] [SortClause] [LimitClause] [SkipClause]
     */
    public function FindQuery(Query $query, array &$parameters)
    {
        $this->FindClause($query);

        if ($this->_lexer->isNextToken(Lexer::T_WHERE)) {
            $this->WhereClause($query, $parameters);
        }

        if ($this->_lexer->isNextToken(Lexer::T_MAP)) {
            $this->MapClause($query, $parameters);
        }

        if ($this->_lexer->isNextToken(Lexer::T_REDUCE)) {
            $this->ReduceClause($query, $parameters);
        }

        while ($this->_lexer->isNextToken($this->_lexer->lookahead['type'])) {
            $this->match($this->_lexer->lookahead['type']);
            switch ($this->_lexer->token['type']) {
                case Lexer::T_SORT:
                    $this->SortClause($query, $parameters);
                    break;
                case Lexer::T_SKIP;
                    $this->SkipClause($query, $parameters);
                    break;
                case Lexer::T_LIMIT:
                    $this->LimitClause($query, $parameters);
                    break;
                default:
                    break(2);
            }
        }
    }

    /**
     * FindClause ::= "FIND" all | SelectField {"," SelectField}
     */
    public function FindClause(Query $query)
    {
        $this->match(Lexer::T_FIND);

        if ($this->_lexer->isNextToken(Lexer::T_ALL)) {
            $this->match(Lexer::T_ALL);
        } else {
            $this->SelectField($query);
            while ($this->_lexer->isNextToken(Lexer::T_COMMA)) {
                $this->match(Lexer::T_COMMA);
                $this->SelectField($query);
            }
        }

        $this->match(Lexer::T_IDENTIFIER);
        $query->find($this->_lexer->token['value']);
    }

    /**
     * SelectField ::= DocumentFieldName
     */
    public function SelectField(Query $query)
    {
        $fieldName = $this->DocumentFieldName();

        $limit = null;
        $skip = null;
        while ($this->_lexer->isNextToken($this->_lexer->lookahead['type'])) {
            switch ($this->_lexer->lookahead['type']) {
                case Lexer::T_SKIP;
                    $this->match(Lexer::T_SKIP);
                    $this->match(Lexer::T_INTEGER);
                    $skip = $this->_lexer->token['value'];
                    break;
                case Lexer::T_LIMIT:
                    $this->match(Lexer::T_LIMIT);
                    $this->match(Lexer::T_INTEGER);
                    $limit = $this->_lexer->token['value'];
                    break;
                default:
                    break(2);
            }
        }

        if ($skip || $limit) {
            $skip = $skip !== null ? $skip : 0;
            $query->selectSlice($fieldName, $skip, $limit);
        } else {
            $query->select($fieldName);
        }
    }

    /**
     * UpdateQuery ::= UpdateClause [WhereClause]
     */
    public function UpdateQuery(Query $query, array &$parameters)
    {
        $this->match(Lexer::T_UPDATE);
        $this->match(Lexer::T_IDENTIFIER);
        $query->update($this->_lexer->token['value']);

        $this->UpdateClause($query, $parameters);
        while ($this->_lexer->isNextToken(Lexer::T_COMMA)) {
            $this->match(Lexer::T_COMMA);
            $this->UpdateClause($query, $parameters);
        }

        if ($this->_lexer->isNextToken(Lexer::T_WHERE)) {
            $this->WhereClause($query, $parameters);
        }
    }

    /**
     * UpdateClause ::= [SetExpression], [UnsetExpression], [IncrementExpression],
     *                  [PushExpression], [PushAllExpression], [PullExpression],
     *                  [PullAllExpression], [AddToSetExpression], [AddManyToSetExpression],
     *                  [PopFirstExpression], [PopLastExpression]
     */
    public function UpdateClause(Query $query, array &$parameters)
    {
        $this->match($this->_lexer->lookahead['type']);
        switch ($this->_lexer->token['type']) {
            case Lexer::T_SET:
                $this->SetExpression($query, $parameters);
                break;
            case Lexer::T_UNSET:
                $this->UnsetExpression($query, $parameters);
                break;
            case Lexer::T_INC:
                $this->IncrementExpression($query, $parameters);
                break;
            case Lexer::T_PUSH:
                $this->PushExpression($query, $parameters);
                break;
            case Lexer::T_PUSHALL:
                $this->PushAllExpression($query, $parameters);
                break;
            case Lexer::T_PULL:
                $this->PullExpression($query, $parameters);
                break;
            case Lexer::T_PULLALL:
                $this->PullAllExpression($query, $parameters);
                break;
            case Lexer::T_ADDTOSET:
                $this->AddToSetExpression($query, $parameters);
                break;
            case Lexer::T_ADDMANYTOSET:
                $this->AddManyToSetExpression($query, $parameters);
                break;
            case Lexer::T_POPFIRST:
                $this->PopFirstExpression($query, $parameters);
                break;
            case Lexer::T_POPLAST:
                $this->PopLastExpression($query, $parameters);
                break;
        }
    }

    /**
     * InsertQuery ::= InsertClause InsertSetClause {"," InsertSetClause}
     */
    public function InsertQuery(Query $query, array &$parameters)
    {
        $this->match(Lexer::T_INSERT);
        $this->match(Lexer::T_IDENTIFIER);
        $query->insert($this->_lexer->token['value']);

        $this->match(Lexer::T_SET);
        $this->InsertSetClause($query, $parameters);
        while ($this->_lexer->isNextToken(Lexer::T_COMMA)) {
            $this->match(Lexer::T_COMMA);
            $this->InsertSetClause($query, $parameters);
        }
    }

    /**
     * InsertSetClause ::= DocumentFieldName "=" Value
     */
    public function InsertSetClause(Query $query, array &$parameters)
    {
        $fieldName = $this->DocumentFieldName();
        $value = $this->Value($parameters);
        $query->set($fieldName, $value, false);
    }

    /**
     * RemoveQuery ::= RemoveClause [WhereClause]
     * RemoveClause ::= "REMOVE" DocumentClassName
     */
    public function RemoveQuery(Query $query, array &$parameters)
    {
        $this->match(Lexer::T_REMOVE);
        $this->match(Lexer::T_IDENTIFIER);
        $query->remove($this->_lexer->token['value']);

        if ($this->_lexer->isNextToken(Lexer::T_WHERE)) {
            $this->WhereClause($query, $parameters);
        }
    }

    /**
     * SortClause ::= SortClauseField {"," SortClauseField}
     */
    public function SortClause(Query $query)
    {
        $this->SortClauseField($query);
        while ($this->_lexer->isNextToken(Lexer::T_COMMA)) {
            $this->match(Lexer::T_COMMA);
            $this->SortClauseField($query);
        }
    }

    /**
     * SortClauseField ::= DocumentFieldName "ASC | DESC"
     */
    public function SortClauseField(Query $query)
    {
        $fieldName = $this->DocumentFieldName();
        $this->match(Lexer::T_IDENTIFIER);
        $order = $this->_lexer->token['value'];
        $query->sort($fieldName, $order);
    }

    /**
     * LimitClause ::= "LIMIT" LimitInteger
     */
    public function LimitClause(Query $query)
    {
        $this->match($this->_lexer->lookahead['type']);
        $query->limit($this->_lexer->token['value']);
    }

    /**
     * SkipClause ::= "SKIP" SkipInteger
     */
    public function SkipClause(Query $query)
    {
        $this->match($this->_lexer->lookahead['type']);
        $query->skip($this->_lexer->token['value']);
    }

    /**
     * MapClause ::= "MAP" MapFunction
     */
    public function MapClause(Query $query, array &$parameters)
    {
        $this->match(Lexer::T_MAP);
        $this->match(Lexer::T_STRING);
        $query->map($this->_lexer->token['value']);
    }

    /**
     * ReduceClause ::= "REDUCE" ReduceFunction
     */
    public function ReduceClause(Query $query, array &$parameters)
    {
        $this->match(Lexer::T_REDUCE);
        $this->match(Lexer::T_STRING);
        $query->reduce($this->_lexer->token['value']);
    }

    /**
     * DocumentFieldName ::= DocumentFieldName | EmbeddedDocument "." {"." DocumentFieldName}
     */
    public function DocumentFieldName()
    {
        $this->match(Lexer::T_IDENTIFIER);
        $fieldName = $this->_lexer->token['value'];
        while ($this->_lexer->isNextToken(Lexer::T_DOT)) {
            $this->match(Lexer::T_DOT);
            $this->match(Lexer::T_IDENTIFIER);
            $fieldName .= '.' . $this->_lexer->token['value'];
        }
        return $fieldName;
    }

    /**
     * WhereClause ::= "WHERE" WhereClausePart {"AND" WhereClausePart}
     */
    public function WhereClause(Query $query, array &$parameters)
    {
        $this->match(Lexer::T_WHERE);
        $this->WhereClauseExpression($query, $parameters);
        while ($this->_lexer->isNextToken(Lexer::T_AND)) {
            $this->match(Lexer::T_AND);
            $this->WhereClauseExpression($query, $parameters);
        }
    }

    /**
     * WhereClausePart ::= ["all", "not"] DocumentFieldName WhereClauseExpression Value
     * WhereClauseExpression ::= "=" | "!=" | ">=" | "<=" | ">" | "<" | "in"
     *                         "notIn" | "all" | "size" | "exists" | "type"
     */
    public function WhereClauseExpression(Query $query, array &$parameters)
    {
        $options = array();
        switch ($this->_lexer->lookahead['type']) {
            case Lexer::T_ALL:
                $this->match(Lexer::T_ALL);
                $options['elemMatch'] = true;
                break;
            case Lexer::T_NOT:
                $this->match(Lexer::T_NOT);
                $options['not'] = true;
                break;
        }

        $fieldName = $this->DocumentFieldName();

        $operator = $this->_lexer->lookahead['value'];

        $value = $this->Value($parameters);

        switch ($operator) {
            case '=':
                $query->where($fieldName, $value, $options);
                break;
            case '!=':
                $query->whereNotEqual($fieldName, $value, $options);
                break;
            case '>=':
                $query->whereGte($fieldName, $value, $options);
                break;
            case '<=':
                $query->whereLte($fieldName, $value, $options);
                break;
            case '>':
                $query->whereGt($fieldName, $value, $options);
                break;
            case '<':
                $query->whereLt($fieldName, $value, $options);
                break;
            case 'in':
                $query->whereIn($fieldName, $value, $options);
                break;
            case 'notIn':
                $query->whereNotIn($fieldName, $value, $options);
                break;
            case 'all':
                $query->whereAll($fieldName, $value, $options);
                break;
            case 'size':
                $query->whereSize($fieldName, $value, $options);
                break;
            case 'exists':
                $query->whereExists($fieldName, $value, $options);
                break;
            case 'type':
                $query->whereType($fieldName, $value, $options);
                break;
            case 'mod':
                $query->whereMod($fieldName, $value, $options);
                break;
            default:
                $this->syntaxError('Invalid atomic update operator.');
        }
    }

    /**
     * Value ::= LiteralValue | JsonObject | JsonArray
     */
    public function Value(array &$parameters)
    {
        $this->match($this->_lexer->lookahead['type']);
        $this->match($this->_lexer->lookahead['type']);
        $value = $this->_lexer->token['value'];
        if (isset($parameters[$value])) {
            $value = $parameters[$value];
        }
        if ($value === '?') {
            $value = array_shift($parameters);
        }
        // detect and decode json values
        if ($value[0] === '[' || $value[0] === '{') {
            return json_decode($value);
        }
        if ($value === 'true') {
            $value = true;
        }
        if ($value === 'false') {
            $value = false;
        }
        return $value;
    }

    /**
     * SetExpression ::= "SET" DocumentFieldName "=" Value {"," SetExpression}
     */
    public function SetExpression(Query $query, array &$parameters)
    {
        $fieldName = $this->DocumentFieldName();
        $value = $this->Value($parameters);
        $query->set($fieldName, $value);
    }

    /**
     * UnsetExpression ::= "UNSET" DocumentFieldName {"," UnsetExpression}
     */
    public function UnsetExpression(Query $query, array &$parameters)
    {
        $this->match(Lexer::T_IDENTIFIER);
        $query->unsetField($this->_lexer->token['value']);
    }

    /**
     * PushExpression ::= "PUSH" DocumentFieldName Value {"," PushExpression}
     */
    public function PushExpression(Query $query, array &$parameters)
    {
        $fieldName = $this->DocumentFieldName();
        $value = $this->Value($parameters);
        $query->push($fieldName, $value);
    }

    /**
     * PushAllExpression ::= "PUSHALL" DocumentFieldName Value {"," PushAllExpression}
     */
    public function PushAllExpression(Query $query, array &$parameters)
    {
        $fieldName = $this->DocumentFieldName();
        $value = $this->Value($parameters);
        $query->pushAll($fieldName, $value);
    }

    /**
     * PullExpression ::= "PULL" DocumentFieldName Value {"," PullExpression}
     */
    public function PullExpression(Query $query, array &$parameters)
    {
        $fieldName = $this->DocumentFieldName();
        $value = $this->Value($parameters);
        $query->pull($fieldName, $value);
    }

    /**
     * PullAllExpression ::= "PULLALL" DocumentFieldName Value {"," PullAllExpression}
     */
    public function PullAllExpression(Query $query, array &$parameters)
    {
        $fieldName = $this->DocumentFieldName();
        $value = $this->Value($parameters);
        $query->pullAll($fieldName, $value);
    }

    /**
     * PopFirstExpression ::= "POPFIRST" DocumentFieldName {"," PopFirstExpression}
     */
    public function PopFirstExpression(Query $query, array &$parameters)
    {
        $this->match(Lexer::T_IDENTIFIER);
        $query->popFirst($this->_lexer->token['value']);
    }

    /**
     * PopLastExpression ::= "POPLAST" DocumentFieldName {"," PopLastExpression}
     */
    public function PopLastExpression(Query $query, array &$parameters)
    {
        $this->match(Lexer::T_IDENTIFIER);
        $query->popLast($this->_lexer->token['value']);
    }

    /**
     * AddToSetExpression ::= "ADDTOSET" DocumentFieldName Value {"," AddToSetExpression}
     */
    public function AddToSetExpression(Query $query, array &$parameters)
    {
        $fieldName = $this->DocumentFieldName();
        $value = $this->Value($parameters);
        $query->addToSet($fieldName, $value);
    }

    /**
     * AddManyToSetExpression ::= "ADDMANYTOSET" DocumentFieldName Value {"," AddManyToSetExpression}
     */
    public function AddManyToSetExpression(Query $query, array &$parameters)
    {
        $fieldName = $this->DocumentFieldName();
        $value = $this->Value($parameters);
        $query->addManyToSet($fieldName, $value);
    }

    /**
     * IncrementExpression ::= "INC" DocumentFieldName "=" IncrementInteger {"," IncrementExpression}
     */
    public function IncrementExpression(Query $query, array &$parameters)
    {
        $fieldName = $this->DocumentFieldName();
        $value = $this->Value($parameters);
        $query->inc($fieldName, $value);
    }
}