<?php
namespace Application\Db;

use User;
use DateTime;
use AuditLog;

trait AuditableDataTrait
{
	/**
	 * table should be audited
	 * @var bool
	 */
	protected $audit = true;
	
	/**
	 * audited value
	 * @var string
	 */
	protected $audited;
	
	/**
	 * (non-PHPdoc)
	 * @see \Base\{DbClass}::preSave()
	 */
	public function preSave(\Propel\Runtime\Connection\ConnectionInterface $con = null)
	{
		$differences = null;
		
		if ($this->audit)
		{
			$changes = $this->getModifiedColumns();
			if (count($changes) > 0)
			{
				$differences = [];
				foreach($changes as $columnName)
				{
					$realColumnName = strtolower(substr($columnName, strpos($columnName, '.')+1 ));
					$differences[$realColumnName] = $this->{$realColumnName};
				}
			}
			
			if (!is_null($differences))
			{
				$this->audited = json_encode($differences);
			}
		}
		
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Base\{DbClass}::postSave()
	 */
	public function postSave(\Propel\Runtime\Connection\ConnectionInterface $con = null)
	{
		if ($this->audit)
		{
			if (isset($this->audited) && strlen(trim($this->audited)) > 0)
			{
				$auditObject = new AuditLog();
				
				try {
					$user = User::getLoggedIn();
				} catch (\Exception $e) {
					$user = null;
				}
				
				if (!is_null($user))
					$auditObject->set_user_id($user->getPrimaryKey());
						
					$auditObject->setObject(get_class($this));
					$auditObject->setObjectId($this->getPrimaryKey());
					$auditObject->setData($this->audited);
					$auditObject->setCreatedAt(new DateTime());
					$auditObject->save();
			}
		
			unset($this->audited);
		}
	}
	
	
	
}