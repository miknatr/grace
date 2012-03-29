<?php

abstract class Model_Record
{
    public function __construct(array $row = array())
    {
        foreach ($row as $k => $v) {
            //get only public properties
            if (property_exists($this, $k) and $k[0] != '_') {
                $this->$k = $v;
            }
        }
    }
    final public function asArray()
    {
        return get_object_vars($this);
    }
    public function edit($rowOrField, $value = null)
    {
        if (!is_array($rowOrField))
            $rowOrField = array($rowOrField => $value);
        
        //TODO глюки если объект удален в базе уже, а тут есть, нужно удалять тогда и объект?
        self::m()->updateById($this->id, $rowOrField);
        $newRow = self::m()->getRowById($this->id);

        if (is_array($newRow)) {
            foreach ($newRow as $k => $v) {
                //get only public properties
                if (property_exists($this, $k) and $k[0] != '_') {
                    $this->$k = $v;
                }
            }
        }

        return $this;
    }
    public function save()
    {
        self::m()->updateById($this->id, $this->asArray(), true);
        return $this;
    }
    public function delete()
    {
        self::m()->deleteById($this->id);
        return $this;
    }

    static public function m()
    {
        return Model_IdMap::getMapper(get_called_class());
    }
    static public function getMapperName()
    {
        return 'Model_Mapper';
    }
}
