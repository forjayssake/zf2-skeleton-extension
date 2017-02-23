<?php

use Base\Setting as BaseSetting;

/**
 * Skeleton subclass for representing a row from the 'settings' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Setting extends BaseSetting
{
    const TYPE_INT 		= 1;
    const TYPE_STRING 	= 2;
    const TYPE_FLOAT 	= 3;
    const TYPE_BOOL		= 4;

    /**
     * @var array
     */
    public static $settingTypes = [
        self::TYPE_INT		=> 'Integer',
        self::TYPE_FLOAT	=> 'Float',
        self::TYPE_STRING	=> 'String',
        self::TYPE_BOOL		=> 'Boolean',
    ];

    /**
     * @return int
     */
    public function getType()
    {
        if (!is_null($this->getvalueInt()))
            return self::TYPE_INT;

        if (!is_null($this->getvalueString()))
            return self::TYPE_STRING;

        if (!is_null($this->getvalueFloat()))
            return self::TYPE_FLOAT;

        if (!is_null($this->getvalueBool()))
            return self::TYPE_BOOL;
    }

    /**
     * @return bool|int|null|string
     */
    public function getValue()
    {
        if (!is_null($this->getvalueInt()))
            return $this->getvalueInt();

        if (!is_null($this->getvalueString()))
            return $this->getvalueString();

        if (!is_null($this->getvalueFloat()))
            return $this->getvalueFloat();

        if (!is_null($this->getvalueBool()))
            return $this->getvalueBool();

        return null;
    }

    /**
     *
     * @param mixed $value
     * @param mixed $valueType
     *
     * @return mixed
     */
    public function setValue($value, $valueType)
    {
        switch ($valueType)
        {
            case Setting::TYPE_INT :
                $this->setvalueInt((int)$value);
                break;
            case Setting::TYPE_FLOAT :
                $this->setvalueFloat((float)$value);
                break;
            case Setting::TYPE_STRING :
                $this->setvalueString((string)$value);
                break;
            case Setting::TYPE_BOOL :
                $this->setvalueBool((bool)$value);
                break;
        }
        return $this;
    }
}
