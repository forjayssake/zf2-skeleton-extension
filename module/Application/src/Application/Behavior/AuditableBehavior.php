<?php
namespace Application\Behavior;

use Propel\Generator\Model\Behavior;

class AuditableBehavior extends Behavior
{
	protected $auditTable 	= 'AuditLogs';
	protected $phpName		= 'AuditLog';
	
	/**
	 * (non-PHPdoc)
	 * @see Behavior::modifyTable()
	 */
	public function modifyTable()
	{
		if($this->getTable()->getDatabase()->hasTable($this->auditTable) === true)
			throw new \Exception('Auditable behaviour requires the ' . $this->auditTable . ' table to be created');
	}
	
	/**
	 * @return string
	 */
	protected function getPreHook()
	{
		return '
			if ($this->audit)
			{
				$changes = $this->getModifiedColumns();
				if (count($changes) > 0)
				{
					$differences = [];
					foreach($Changes as $columnName)
					{
						$realColumnName = substr($columnName, strpos($columnName, '.')+1 ));
						$differences[] = $this->{$realColumnName};
					}
				}
			}
			$this->audited = json_encode($diff);';
	}
	
	/**
	 * @return string
	 */
	protected function getPostHook()
	{
		return '
			if ($this->audit)
			{
				if (isset($this->audited) && strlen(trim($this->audited)) > 0)
				{
					$auditObject = new $this->phpName();
					$user = \\User::getLoggedIn();
					if (!is_null($user))
						$auditObject->set_user_id($user->getPrimaryKey());
					
					$auditObject->setObject(get_class($this));
					$auditObject->setObjectId($this->getPrimaryKey());
					$auditObject->setData($this->audited);
					$auditObject->setDateCreated(new \\DateTime());
					$auditObject->save();
				}
				
				unset($this->audited);
			}';
	}
	
	/**
	 * create an $audit property for auditable tables
	 * @param PHP5ObjectBuilder $builder
	 */
	public function objectAttributes(PHP5ObjectBuilder $builder)
	{
		return 'protected $audit = true;';
	}
	
	public function preUpdate(PHP5ObjectBuilder $builder)
	{
		return $this->getPreHook();
	}
	
	public function postUpdate(PHP5ObjectBuilder $builder)
	{
		return $this->getPostHook();
	}
	
	public function preInsert(PHP5ObjectBuilder $builder)
	{
		return $this->getPreHook();
	}
	
	public function postInsert(PHP5ObjectBuilder $builder)
	{
		return $this->getPostHook();
	}
	
}