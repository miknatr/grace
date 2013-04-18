<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM;

use Grace\DBAL\AbstractConnection\ExecutableInterface;
use Grace\DBAL\AbstractConnection\ResultInterface;
use Grace\SQLBuilder\Factory;
use Grace\SQLBuilder\SelectBuilder;

abstract class FinderAbstract implements ExecutableInterface, ResultInterface
{
    protected $baseClass;
    protected $orm;

    public function __construct($baseClass, ORMManagerAbstract $orm)
    {
        $this->baseClass = $baseClass;
        $this->orm       = $orm;
    }



    //IMPLEMETATIONS OF InterfaceExecutable, InterfaceResult

    /** @var \Grace\DBAL\AbstractConnection\ResultInterface */
    private $queryResult;
    /** @return RecordAbstract|bool */
    final public function fetchOneOrFalse()
    {
        if ($row = $this->queryResult->fetchOneOrFalse()) {
            return $this->getFromIdentityMapOrMakeRecord($row);
        }

        return false;
    }
    /** @return RecordAbstract[] */
    final public function fetchAll()
    {
        if (!is_object($this->queryResult)) {
            throw new \LogicException('Unprepared sql select result');
        }

        $records = array();
        while ($row = $this->queryResult->fetchOneOrFalse()) {
            $records[] = $this->getFromIdentityMapOrMakeRecord($row);
        }

        $this->queryResult = null;

        return $records;
    }
    final public function fetchResult()
    {
        if (!is_object($this->queryResult)) {
            throw new \LogicException('Unprepared sql select result');
        }

        $r = $this->queryResult->fetchResult();
        $this->queryResult = null;

        return $r;
    }
    final public function fetchHash()
    {
        if (!is_object($this->queryResult)) {
            throw new \LogicException('Unprepared sql select result');
        }

        $r = $this->queryResult->fetchHash();
        $this->queryResult = null;

        return $r;
    }
    final public function fetchColumn()
    {
        if (!is_object($this->queryResult)) {
            throw new \LogicException('Unprepared sql select result');
        }

        $r = $this->queryResult->fetchColumn();
        $this->queryResult = null;

        return $r;
    }
    final public function execute($query, array $arguments = array())
    {
        if (!is_object($this->queryResult)) {
            throw new \LogicException('Unprepared sql select result');
        }

        $this->queryResult = $this->orm->db->execute($query, $arguments);
        $this->queryResult = null;

        return $this;
    }



    //SELECT BUILDER CREATION

    const TABLE_ALIAS = 'Resource';
    /** @return SelectBuilder */
    public function getSelectBuilder()
    {
        return (new Factory($this))->select($this->baseClass)->setFromAlias(self::TABLE_ALIAS);
    }



    //RECORD GETTERS

    /**
     * @param $id
     * @return RecordAbstract|bool
     */
    public function getByIdOrFalse($id)
    {
        if ($this->orm->identityMap->issetRecord($this->baseClass, $id)) {
            return $this->orm->identityMap->getRecord($this->baseClass, $id);
        }

        if ($row = $this->orm->db->getSQLBuilder()->select($this->baseClass)->eq('id', $id)->fetchOneOrFalse()) {
            return $this->getFromIdentityMapOrMakeRecord($row);
        }

        return false;
    }


    /**
     * @param array $fields
     * @return RecordAbstract
     */
    //STOPPER поменялась сигнатура
    public function create(array $fields = array())
    {
        //TODO magic string 'id'
        if (!isset($fields['id'])) {
            $fields['id'] = $this->orm->db->generateNewId($this->baseClass);
        }

        $record = $this->getFromIdentityMapOrMakeRecord($fields);
        $this->orm->unitOfWork->markAsNew($this->baseClass, $fields['id']);

        return $record;
    }



    //ON COMMIT EVENTS

    public function insertRecordOnCommit(RecordAbstract $record)
    {
        $values = $this->convertRecordToDbArray($record);
        $this->orm->db->getSQLBuilder()->insert($this->baseClass)->values($values)->execute();
        $record->flushDefaults();
    }
    public function updateRecordOnCommit(RecordAbstract $record)
    {
        $changes = $this->convertRecordToDbChangesArray($record);
        $this->orm->db->getSQLBuilder()->update($this->baseClass)->values($changes)->eq('id', $record->getId())->execute();
        $record->flushDefaults();
    }
    public function deleteRecordOnCommit(RecordAbstract $record)
    {
        $this->orm->db->getSQLBuilder()->delete($this->baseClass)->eq('id', $record->getId())->execute();
    }



    //MAPPING


    /**
     * @param array $dbArray
     * @return RecordAbstract
     */
    protected function getFromIdentityMapOrMakeRecord(array $dbArray)
    {
        //TODO magic string 'id'
        //if already exists in IdentityMap -  we get from IdentityMap because we don't want different objects related to one db row
        if ($this->orm->identityMap->issetRecord($this->baseClass, $dbArray['id'])) {
            $record = $this->orm->identityMap->getRecord($this->baseClass, $dbArray['id']);
        } else {
            $record = $this->convertDbArrayToRecord($dbArray);
            $this->orm->identityMap->setRecord($this->baseClass, $dbArray['id'], $record);
        }

        return $record;
    }

    /**
     * @abstract
     * @param array $dbArray
     * @return array
     */
    protected function convertDbArrayToRecord(array $dbArray)
    {
        $recordArray = array();
        foreach ($this->orm->modelsConfig->models[$this->baseClass]->properties as $propertyName => $propertyConfig) {
            if ($propertyConfig->mapping) {
                $recordArray[$propertyName] = $this->orm->typeConverter->convertDbToPhp($propertyConfig->mapping, $dbArray[$propertyName]);
            } else {
                //STOPPER вообще вычисляемые поля должны быть определены именно здесь, но откуда? да мне похуй откуда, здесь и все
                //то что нет доступа на поле или его надо высчитывать от юзера это вопрос апи-мэперов, а не этих
                $recordArray[$propertyName] = null;
            }
        }

        $recordClass = $this->orm->classNameProvider->getModelClass($this->baseClass);
        return new $recordClass($recordArray);
    }

    /**
     * @param RecordAbstract $record
     * @return array
     */
    protected function convertRecordToDbArray(RecordAbstract $record)
    {
        $recordArray = $record->getFields();
        $dbArray = array();
        foreach ($this->orm->modelsConfig->models[$this->baseClass]->properties as $propertyName => $propertyConfig) {
            if ($propertyConfig->mapping) {
                if (isset($recordArray[$propertyName])) {
                    $dbArray[$propertyName] = $this->orm->typeConverter->convertPhpToDb($propertyConfig->mapping, $recordArray[$propertyName]);
                } else {
                    //STOPPER выбрать стратегию, или дефолт или нулы
                    //$row[$field] = null; //default values in db must be used
                }
            }
        }
        return $dbArray;
    }

    /**
     * @param RecordAbstract $record
     * @return array
     */
    protected function convertRecordToDbChangesArray(RecordAbstract $record)
    {
        $recordArray = $record->getFields();
        $recordArrayDefaults = $record->getDefaults();

        $dbChangesArray = array();
        foreach ($this->orm->modelsConfig->models[$this->baseClass]->properties as $propertyName => $propertyConfig) {
            if ($propertyConfig->mapping) {
                if (isset($recordArray[$propertyName]) and $recordArray[$propertyName] != $recordArrayDefaults[$propertyName]) {
                    $dbChangesArray[$propertyName] = $this->orm->typeConverter->convertPhpToDb($propertyConfig->mapping, $recordArray[$propertyName]);
                }
            }
        }

        return $dbChangesArray;
    }
}
