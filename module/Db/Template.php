<?php

use Base\Template as BaseTemplate;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Skeleton subclass for representing a row from the 'templates' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Template extends BaseTemplate implements InputFilterAwareInterface
{
    use \Application\Db\AuditableDataTrait;

    /**
     *
     * @var InputFilter $inputFilter
     */
    protected $inputFilter;

    /**
     * @param InputFilterInterface $inputFilter
     * @return void|InputFilterAwareInterface
     * @throws \Exception
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception(__METHOD__.' - Not Implemented');
    }

    /**
     * (non-PHPdoc)
     * @see Zend\InputFilter.InputFilterAwareInterface::getInputFilter()
     */
    public function getInputFilter()
    {
        if($this->inputFilter instanceof InputFilter)
            return $this->inputFilter;

        $inputFilter = new InputFilter();

        $inputFilter->add(array(
            'name' => 'name',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 255
                    ),
                )
            ),
        ));

        $inputFilter->add(array(
            'name' => 'event',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 255
                    ),
                )
            ),
        ));

        $inputFilter->add(array(
            'name' => 'subject',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 255
                    ),
                )
            ),
        ));

        $inputFilter->add(array(
            'name' => 'body',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 65500
                    ),
                )
            ),
        ));

        return $this->inputFilter = $inputFilter;
    }


}
