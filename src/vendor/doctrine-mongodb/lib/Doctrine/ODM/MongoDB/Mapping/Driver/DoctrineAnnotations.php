<?php
/*
 *  $Id$
 *
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

namespace Doctrine\ODM\MongoDB\Mapping;

use Doctrine\Common\Annotations\Annotation;

final class Document extends Annotation
{
    public $db;
    public $collection;
    public $repositoryClass;
    public $indexes = array();
}
final class EmbeddedDocument extends Annotation {}
final class MappedSuperclass extends Annotation {}

final class Inheritance extends Annotation
{
    public $type = 'NONE';
    public $discriminatorMap = array();
    public $discriminatorField;
}
final class InheritanceType extends Annotation {}
final class DiscriminatorField extends Annotation
{
    public $name;
    public $fieldName;
}
final class DiscriminatorMap extends Annotation {}
final class DiscriminatorValue extends Annotation {}

final class Indexes extends Annotation {}
class Index extends Annotation
{
    public $keys = array();
    public $name;
    public $dropDups;
    public $background;
    public $safe;
    public $order;
    public $unique = false;
    public $options = array();
}
final class UniqueIndex extends Index
{
    public $unique = true;
}

class Field extends Annotation
{
    public $name;
    public $type = 'string';
    public $nullable = false;
}
final class Id extends Field
{
    public $id = true;
    public $type = 'id';
    public $custom = false;
}
final class Hash extends Field
{
    public $type = 'hash';
}
final class Boolean extends Field
{
    public $type = 'boolean';
}
final class Int extends Field
{
    public $type = 'int';
}
final class Float extends Field
{
    public $type = 'float';
}
final class String extends Field
{
    public $type = 'string';
}
final class Date extends Field
{
    public $type = 'date';
}
final class Key extends Field
{
    public $type = 'key';
}
final class Timestamp extends Field
{
    public $type = 'timestamp';
}
final class Bin extends Field
{
    public $type = 'bin';
}
final class BinFunc extends Field
{
    public $type = 'bin_func';
}
final class BinUUID extends Field
{
    public $type = 'bin_uuid';
}
final class BinMD5 extends Field
{
    public $type = 'bin_md5';
}
final class BinCustom extends Field
{
    public $type = 'bin_custom';
}
final class File extends Field
{
    public $type = 'file';
    public $file = true;
}
final class Increment extends Field
{
    public $type = 'increment';
}
final class Collection extends Field
{
    public $type = 'collection';
    public $strategy = 'pushPull'; // pushPull, set
}
final class EmbedOne extends Field
{
    public $type = 'one';
    public $embedded = true;
    public $targetDocument;
    public $discriminatorField;
    public $discriminatorMap;
    public $cascade;
}
final class EmbedMany extends Field
{
    public $type = 'many';
    public $embedded = true;
    public $targetDocument;
    public $discriminatorField;
    public $discriminatorMap;
    public $strategy = 'pushPull'; // pushPull, set
    public $cascade;
}
final class ReferenceOne extends Field
{
    public $type = 'one';
    public $reference = true;
    public $targetDocument;
    public $discriminatorField;
    public $discriminatorMap;
    public $cascade;
}
final class ReferenceMany extends Field
{
    public $type = 'many';
    public $reference = true;
    public $targetDocument;
    public $discriminatorField;
    public $discriminatorMap;
    public $cascade;
    public $strategy = 'pushPull'; // pushPull, set
}
class NotSaved extends Field {}
final class Distance extends Field {
    public $distance = true;
}
final class AlsoLoad extends Annotation {
    public $name;
}
final class ChangeTrackingPolicy extends Annotation {}

/* Annotations for lifecycle callbacks */
final class HasLifecycleCallbacks extends Annotation {}
final class PrePersist extends Annotation {}
final class PostPersist extends Annotation {}
final class PreUpdate extends Annotation {}
final class PostUpdate extends Annotation {}
final class PreRemove extends Annotation {}
final class PostRemove extends Annotation {}
final class PreLoad extends Annotation {}
final class PostLoad extends Annotation {}