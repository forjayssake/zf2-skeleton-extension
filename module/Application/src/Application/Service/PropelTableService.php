<?php
namespace Application\Service;

use ModelCriteria;
use Application\Adapter\PropelPaginator;
use Zend\Cache\Storage\Adapter\Session;
use Zend\Session\Container as SessionContainer;
use Zend\Paginator\Paginator;
use Zend\Http\PhpEnvironment\Request;
use Exception;
use Zend\Form\Form;
use Zend\Di\ServiceLocator;
use User;

class PropelTableService 
{
	const DEFAULT_PARTIAL 				= 'partial/table';
	const DEFAULT_HEADER_PARTIAL 		= 'partial/table-header';
	const DEFAULT_PAGINATION_PARTIAL 	= 'partial/table-pagination';
	const DEFAULT_ROWS_PER_PAGE 		= 50;
	const DEFAULT_PAGINATION_RANGE		= 5;
	
	const TABLE_FILTER_KEY				= 'propel_table_filter';
	const FILTER_MATCH_EXACT			= 'exact';
	const FILTER_MATCH_BOTH				= 'both';
	const FILTER_MATCH_NULL				= 'null';
	const FILTER_MATCH_LEFT				= 'left';
	const FILTER_MATCH_RIGHT			= 'right';
	
	const CHECKBOX_FORM_ABOVE			= 1;
	const CHECKBOX_FORM_BELOW			= 2;
	const CHECKBOX_FORM_MODAL			= 3;
	
	/**
	 * 
	 * @var ServiceLocatorAwareInterface
	 */
	protected $serviceLocator;
	
	/**
	 * partials to handle table rendering
	 * @var string
	 */
	protected $partial;
	protected $headerPartial;
	protected $paginationPartial;
	
	/**
	 * Propel model to use as data source for table
	 * @var ModelCriteria
	 */
	protected $model;
	
	/**
	 * Number of table rows to display per page
	 * @var int
	 */
	protected $rowsPerPage;
	
	/**
	 * 
	 * @var Paginator
	 */
	protected $paginator;
	
	/**
	 * Session container to hold sort/filter options
	 * @var SessionContainer
	 */
	protected $sessionContainer;
	
	/**
	 * table options array
	 * 
	 * $config = [
	 * 		'tableclass' => 'table-class-name'		// class name to append to table
	 * 		'columns' => [							// array - table columns definition @See $columns
	 *	 		...
	 * 		],
	 * 		'checkBoxes' => false,					// bool - prepend a column of checkboxes to the table
	 * 		'checkBoxForm' => [
	 * 			'form' => $form,					// Form object to display on checkbox selection
	 * 			'partial' => $partial				// string - optional partial to render form
	 * 			'displayType' => $type				// string - 1 = below | 2 = above| 3 = modal - defaults to `below` Use supplied class constants 
	 * 		]
	 * 		'showViewLink' => false,				// bool - show a link to view the row record
	 * 		'showEditLink' => false,				// bool - show a link to edit the record
	 * 		'showDeleteLink' => false,				// bool - show a link to delete the record
	 * 		'linkRoute'	=> 'route/name',			// string - the route to direct links to - actions determined by link type
	 * 		'linkRouteParams' => ['id' => 'id'],	// array - parameters required to build the link route [paramName => fieldName] - also used for column links
	 * 		'sortOnLoad' => '+-columnName' 			// string - column to sort on initial load, prefixed with +/- 
	 * ]
	 * 
	 * @var array
	 */	
	protected $config = [];
	
	/**
	 * Array of Propel objects representing the rows of the table
	 * @var array
	 */
	protected $rows = [];
	
	/**
	 * should this table be rendered as responsive for mobile
	 * @var bool
	 */
	protected $responsive = true;
	
	/**
	 * an array of column definitions
	 * $columns  = [
	 * 		'name' = [								// string - column name to reference 
	 * 			'label' => 'NAME',					// string - translation string to display on render
	 * 			'canSort' => true,					// bool - column is sortable
	 * 			'isLink' => true,					// bool - should the column value be rendered as a link?
	 * 			'filter' => [						// array - filter definition
	 * 				
	 * 			],
	 * 			'helper' => [						// array - view helper definition
	 * 				'name' => 'HelperName',				// string - class name of view helper
	 *				'params' => [value1, value2],		// array - optional parameter values to parse to the view helper 
	 *				'parseRow' => true,					// bool - parse the row object as a final parameter to the view helper
	 * 			]
	 * 		]
	 * ]
	 * @var array
	 */
	protected $columns = [];
	
	/**
	 * holder for http request object
	 * @var Request
	 */
	protected $request;
	
	/**
	 * range of pages to display in pagination 
	 * @var int
	 */
	protected $paginationRange;
	
	/**
	 * form container for table filter elements
	 * @var Form
	 */
	protected $filterForm;
	
	/**
	 * string prefix to uniquely identify this table
	 * @var string
	 */
	protected $identifier;
	
	/**
	 * @var Session
	 */
	protected $storage;
	
	/**
	 * 
	 * @var string
	 */
	protected $hash;
	
	public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}
	
	/**
	 * sets various sensible defaults for rendering the table
	 * called by $this->setConfig()
	 * @throws Exception
	 * @return PropelTableService
	 */
	private function initTable()
	{
		if (is_null($this->config))
			throw new Exception(__CLASS__ . '::' . __FUNCTION__ . ' Says: A table configuration must be set with setConfig() before calling tableInit()');
		
		$this->columns = isset($this->config['columns']) ? $this->config['columns'] : [];
		
		if (is_null($this->partial))
			$this->setPartial();
		
		if (is_null($this->headerPartial))
			$this->setHeaderPartial();
		
		if (is_null($this->paginationPartial))
			$this->setPaginationPartial();
		
		$this->setResponsive();
		
		$this->setPaginationRange();
		
		$this->identifier = md5(json_encode($this->config));
		
		$this->generateFilterForm($this->getActiveFilters());
		
		return $this;
	}
	
	/**
	 * determine whether this table should display checkboxes
	 * @return bool
	 */
	public function hasCheckboxes()
	{
		return isset($this->config['checkBoxes']) && $this->config['checkBoxes'] === true;
	}
	
	/**
	 * return the checkbox form from config if available
	 * @return Form | null
	 */
	public function fetchCheckboxForm()
	{
		if (isset($this->config['checkBoxForm']) && isset($this->config['checkBoxForm']['form']))
			return $this->config['checkBoxForm']['form'];
		
		return  null;
	}
	
	/**
	 * return location for checkbox form (if copnfigured) from the table config array
	 * @return int | null
	 */
	public function fetchCheckboxFormLocation()
	{
		if ($this->hasCheckboxes() === false)
			return null;
		
		if (isset($this->config['checkBoxForm']) && isset($this->config['checkBoxForm']['form']))
		{
			if (isset($this->config['checkBoxForm']['displayType'])) {
				return $this->config['checkBoxForm']['displayType'];
			} else {
				return self::CHECKBOX_FORM_BELOW;
			}
		}
		
		return null;
	}
	
	/**
	 * return the checkbox form partial if available
	 * @return string | null
	 */
	public function fetchCheckboxPartial()
	{
		if (isset($this->config['checkBoxForm']['partial']) && isset($this->config['checkBoxForm']['partial']))
			return $this->config['checkBoxForm']['partial'];
		
		return  null;
	}
	
	/**
	 * return the $this->identifier variable
	 * @return string
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}
	
	/**
	 * populate the table configuration array and trigger tableInit()
	 * @param array $config
	 * @return PropelTableService
	 */
	public function setConfig(array $config = [])
	{
		$this->config = $config;
		$this->initTable();
		return $this;
	}
	
	/**
	 * return the table $config array
	 * @return array 
	 */
	public function getConfig()
	{
		return $this->config;
	}
	
	/**
	 * set the number of page links to display in the pagination controls
	 * @param string $range
	 */
	public function setPaginationRange($range = null)
	{
		if (is_null($range) || (int)$range == 0) {
			$this->paginationRange = self::DEFAULT_PAGINATION_RANGE;
		} else {
			$this->paginationRange = (int)$range;
		}
		
		return $this;
	}
	
	/**
	 * retrun the number of page links to display in pagination controls
	 * @return int
	 */
	public function getPaginationRange()
	{
		return $this->paginationRange;
	}
	
	/**
	 * set the partial for this table
	 * @param string $partial
	 * @return \Application\Service\PropelTableServiceService
	 */
	public function setPartial($partial = null)
	{
		if (is_null($partial))
			$this->partial = self::DEFAULT_PARTIAL;
		
		return $this;
	}
	
	/**
	 * return the partial name to render the table body
	 * @return string
	 */
	public function getPartial()
	{
		return $this->partial;
	}
	
	/**
	 * set the header partial for this table
	 * @param string $partial
	 * @return \Application\Service\PropelTableServiceService
	 */
	public function setHeaderPartial($partial = null)
	{
		if (is_null($partial))
			$this->headerPartial = self::DEFAULT_HEADER_PARTIAL;
	
		return $this;
	}
	
	/**
	 * return the partial name to render the table header
	 * @return string
	 */
	public function getHeaderPartial()
	{
		return $this->headerPartial;
	}
	
	/**
	 * set the pagination partial for this table
	 * @param string $partial
	 * @return \Application\Service\PropelTableServiceService
	 */
	public function setPaginationPartial($partial = null)
	{
		if (is_null($partial))
			$this->paginationPartial = self::DEFAULT_PAGINATION_PARTIAL;
	
		return $this;
	}
	
	/**
	 * return the name of the partial used to render the table pagination controls
	 * @return string
	 */
	public function getPaginationPartial()
	{
		return $this->paginationPartial;
	}
	
	/**
	 * Set a Propel Query object as the data source for a table 
	 * @param mixed $model created from any Propel model
	 * @return \Application\Service\PropelTableServiceService
	 */
	public function setPropelModel($model)
	{
		$this->model = $model;
		return $this;
	}
	
	/**
	 * return the propel model for this table
	 * @return mixed and PropelQuery class
	 */
	public function getPropelModel()
	{
		return $this->model;
	}
	
	/**
	 * Return the raw SQL for a table's Propel model
	 * @return String|NULL
	 */
	public function getPropelModelSqlAsString()
	{
		if (!is_null($this->model))
			return $this->model->toString();
		
		return null;
	}
	
	/**
	 * set the number of rows for each paginated page 
	 * @param int $rowsPerPage
	 */
	public function setRowsPerPage($rowsPerPage = null)
	{
		if (is_null($rowsPerPage)) {
			$this->rowsPerPage = self::DEFAULT_ROWS_PER_PAGE;
		} else {
			$this->rowsPerPage = (int)$rowsPerPage;
		}
		
		return $this;
	}
	
	/**
	 * return the number of rows per page for pagination
	 */
	public function getRowsPerPage()
	{
		return $this->rowsPerPage;
	}
	
	/**
	 * set the columns definition for this table
	 * @param array $columns
	 */
	public function setColumns(array $columns = [])
	{
		$this->columns = $columns;
		
		return $this;
	}

	/**
	 * return an array of [column name => column config]
	 * @return array
	 */
	public function getColumns()
	{
		return $this->columns;
	}
	
	/**
	 * set whether this total should be rendered responsive
	 * @param bool $responsive
	 */
	public function setResponsive($responsive = true)
	{
		$this->responsive = $responsive;
		
		return $this;
	}
	
	/**
	 * return whether this table should be responsive
	 */
	public function isResponsive()
	{
		return $this->responsive;
	}
	
	/**
	 * return the collection of rows for the current table page
	 */
	public function getRows()
	{
		return $this->rows;
	}
	
	/**
	 * return the url string for a link
	 * @param string $linkType
	 * @return string
	 */
	public function getLinkUrlString($linkType = 'view')
	{
		$linkType = strtolower($linkType);
		
		if ($linkType == 'view')
			return $this->config['linkRoute'];
		
		return $this->config['linkRoute'] . '/actions';
	}
	
	/**
	 * return the parameters for table links
	 * @param mixed $row A table row represented by a Propel object
	 * @param string $linkType 
	 * @return array
	 */
	public function getLinkUrlParameters($row, $linkType = 'view')
	{
		if (!isset($this->config['linkRouteParams']) || empty($this->config['linkRouteParams']))
			return [];
		
		$linkType = strtolower($linkType);
		
		$urlParams = ['action' => $linkType];
		foreach($this->config['linkRouteParams'] as $paramName => $fieldName)
		{
			if (is_array($fieldName))
			{
				$foreignMethod 				= 'get' . key($fieldName);
				$fieldMethod  				= 'get' . array_shift($fieldName);
				$paramValue 				= $row->$foreignMethod()->$fieldMethod();
				$urlParams[key($fieldName)] = $paramValue;
			} else {
				$method 	= 'get' . $fieldName;
				$paramValue = $row->$method();
			}
			
			$urlParams[$paramName] = $paramValue;
		}
		
		return $urlParams;
	}
	
	/**
	 * determine whether this table should display link buttons 
	 * @return bool
	 */
	public function hasLinkButtons()
	{
		if (isset($this->config['showViewLink']) && $this->config['showViewLink'] == true)
			return true;
		
		if (isset($this->config['showEditLink']) && $this->config['showEditLink'] == true)
			return true;
		
		if (isset($this->config['showDeleteLink']) && $this->config['showDeleteLink'] == true)
			return true;
		
		return false;
	}
	
	/**
	 * return an array of routes and parameters to render link buttons
	 * @param mixed $row A table row represented by a Propel object
	 * @return array
	 */
	public function getLinkButtonParameters($row)
	{
		$links = [];
		
		$linkTypes = ['View', 'Edit', 'Delete'];
		
		foreach($linkTypes as $type)
		{
			$key = 'show' . $type . 'Link';
			if (isset($this->config[$key]) && $this->config[$key] == true)
			{
				$links[$type] = [
					'urlString' => $this->getLinkUrlString(strtolower($type)),
					'urlParameters' => $this->getLinkUrlParameters($row, strtolower($type)),
					'linkText' => strtoupper($type), // translation string
					'linkIcon' => $this->getLinkIconForType(strtolower($type)),
					'linkClass' => $this->getLinkClassForType(strtolower($type))
				];
			}
		}
		return $links;
	}
	
	/**
	 * return the sort column name and order if found in the table's request object
	 * @return array
	 */
	public function getPageSortFromRequest()
	{
		if(!is_null($this->request))
		{
			$sort = $this->request->getQuery('sort');
			$sortColumn = str_replace(['+', '-', ' '], '', $sort);
		
			if (!is_null($sortColumn) && $sortColumn !== '')
			{
				if (!$this->columnExists($sortColumn))
					throw new Exception(__CLASS__ . '::' . __FUNCTION__ . ' Says: requested sort column `' . $sortColumn . '` does not exist in the table config!');
				
				$sortValues = [
					'columnName' => $sortColumn,
					'sortOrder' => substr($sort, 0, 1) == '-' ? 'DESC' : 'ASC',
				];
				
				$this->storage->setItem($this->fetchTableHash() . 'sort', $sortValues);
					
				return $sortValues;
			}
		}
		
		if (isset($this->config['sortOnLoad']) && strlen($this->config['sortOnLoad']) > 1)
		{
			$sortColumn = str_replace(['+', '-', ' '], '', $this->config['sortOnLoad']);
			
			if (!$this->columnExists($sortColumn))
				throw new Exception(__CLASS__ . '::' . __FUNCTION__ . ' Says: default sort column `' . $sortColumn . '` does not exist in the table config!');
			
			return [
				'columnName' => $sortColumn,
				'sortOrder' => substr($this->config['sortOnLoad'], 0, 1) == '-' ? 'DESC' : 'ASC',
			];
		}
		
		if($this->storage->hasItem($this->fetchTableHash() . 'sort'))
		{
			return $this->storage->getItem($this->fetchTableHash() . 'sort');
		}
		
		return false;
	}

	private function fetchTableHash()
	{
		if (is_null($this->hash))
		{
			$user = User::getLoggedIn();
			if (is_null($user))
				throw new Exception(__CLASS__ . '::' . __FUNCTION__ . ' Says: No user found');
			
			
			$this->hash = md5(json_encode($this->getConfig()) . $user->getEmail());
		}
		
		return $this->hash;
	}
	
	/**
	 * return a href link for a given column for table sorting 
	 * @param string $column
	 * @return string
	 */
	public function getSortLink($column)
	{
		$router = $this->serviceLocator->get('application')->getMvcEvent()->getRouter();
		$routeMatches = $this->serviceLocator->get('application')->getMvcEvent()->getRouteMatch();
		$route = $routeMatches->getMatchedRouteName();
		$routeParameters = $routeMatches->getParams();
		
		$sorting = $this->getPageSortFromRequest();
		if($sorting !== false)
		{
			if ($sorting['columnName'] == $column)
			{
				$column = ($sorting['sortOrder'] === 'DESC' ? '+' : '-') . $sorting['columnName'];
			}
		}
		
		$query = [
			'sort' => $column,
		];
		
		$page = $this->getPage();
		if($page > 1) {
			$query['page'] = $page;
		}
		
		$routeOptions = ['name' => $route, 'query' => $query];
		
		return $router->assemble($routeParameters, $routeOptions);
	}
	
	/**
	 * determine whether this table has filters specified for any columns
	 */
	public function hasFilters()
	{
		$columns = $this->getColumns();
		foreach($columns as $columnName => $columnConfig)
		{
			if (isset($columnConfig['filter']) && !empty($columnConfig['filter']))
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * return an array of filter values set for this table
	 */
	public function getActiveFilters()
	{
		$activeFilters = [];
		
		$action = $this->request->getPost('filteroption');
		if (!is_null($action))
		{
			if ($action === 'clearfilter')
			{
				return $activeFilters;
			} else {
				$postFilters = $this->request->getPost(self::TABLE_FILTER_KEY);
				foreach($postFilters as $columnName => $filterValue)
				{
					if(isset($this->columns[$columnName]['filter']) && isset($this->columns[$columnName]['filter']['type']))
					{
						if (strlen(trim($filterValue)) > 0) 
						{
							$activeFilters[$columnName] = $filterValue; 
						}
					}
				}
				
				$this->storage->setItem($this->fetchTableHash() . 'filter', $activeFilters);
			}
		}
		
		if($this->storage->hasItem($this->fetchTableHash() . 'filter'))
		{
			foreach ($this->storage->getItem($this->fetchTableHash() . 'filter') as $key => $filterValue) {
				if (!isset($activeFilters[$key])) {
					$activeFilters[$key] = $filterValue;
				}
			}
		}
		
		return $activeFilters;
	}
	
	/**
	 * determine whether this table requires a form wrapper for filters or checkboxes
	 * @return bool
	 */
	public function requiresFilterForm()
	{
		if ($this->hasFilters() || (isset($this->config['checkBoxes']) && $this->config['checkBoxes'] == true) )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * create a form to hold table filter elements
	 * @param array $currentFilters
	 * @return Form
	 */
	private function generateFilterForm(array $currentFilters = [])
	{
		if (!$this->requiresFilterForm())
			return null;
		
		$form = new Form($this->identifier . '_tablefilters');
		
		foreach($this->columns as $columnName => $columnConfig)
		{
			if (!isset($columnConfig['filter']))
				continue;
			
			$form->add([
				'name' => self::TABLE_FILTER_KEY . '[' . $columnName . ']',
				'type' => $columnConfig['filter']['type'],
				'options' => isset($columnConfig['filter']['options']) ? $columnConfig['filter']['options'] : [],
				'attributes' => [
					'class' => 'propel-table-filter',
				]
			]);
			
			if (array_key_exists($columnName, $currentFilters))
			{
				$form->get(self::TABLE_FILTER_KEY . '[' . $columnName . ']')->setValue($currentFilters[$columnName]);
			}
		}
		
		$this->filterForm = $form;
	}
	
	public function getFilterForm()
	{
		return $this->filterForm;
	}
	
	/**
	 * return a form element relating to a given $columnName
	 * @param string $columnName
	 * @return Element
	 */
	public function getFilterElement($columnName)
	{
		return $this->getFilterForm()->get(self::TABLE_FILTER_KEY . '[' . $columnName . ']');
	}
	
	/**
	 * determine whether a given column name exists in the table config 
	 * @param string $column
	 */
	private function columnExists($column)
	{
		$columns = $this->getColumns();
		return array_key_exists($column, $columns);
	}
	
	/**
	 * return an icon to display on a link button for a given link type
	 * @param string $linkType
	 * @return string
	 */
	private function getLinkIconForType($linkType = 'view')
	{
		$linkType = strtolower($linkType);
		
		$icon = '';
		switch ($linkType)
		{
			case 'view' : $icon = 'fa fa-arrow-right'; break;
			case 'edit' : $icon = 'fa fa-edit'; break;
			case 'delete' : $icon = 'fa fa-remove'; break;
			default: break;
		}
		
		return $icon;
	}
	
	/**
	 * return a class to render a link button for a given link type
	 * @param string $linkType
	 * @return string
	 */
	private function getLinkClassForType($linkType = 'view')
	{
		$linkType = strtolower($linkType);

		switch ($linkType)
		{
			case 'view' : $class = 'btn-primary'; break;
			case 'edit' : $class = 'btn-default'; break;
			case 'delete' : $class = 'btn-danger'; break;
			default: $class = 'btn-default'; break;
		}
		
		return $class;
	}
	
	/**
	 * Create a new Paginator instance for this table
	 * @return $this
	 */
	private function setPaginator()
	{
		if (!$this->paginator instanceOf Paginator)
			$this->paginator = new Paginator(new PropelPaginator()); 
			
		return $this;
	}
	
	/**
	 * return the paginator object for this table
	 */
	public function getPaginator()
	{
		return $this->paginator;
	}
	
	/**
	 * return the current page number for pagination
	 */
	private function getPage()
	{
		// get current page from session storage
		if($this->request !== null)
		{
			$page = $this->request->isGet() ? $this->request->getQuery('page') : $this->request->getPost('page');
			if($page !== null)
				return (int)$page;
		}
		
		
		return 1;
	}
	
	/**
	 * prepare this table and associated data for rendering
	 * @throws Exception
	 */
	public function prepare()
	{
		if (is_null($this->model))
			throw new Exception(__CLASS__ . '::' . __FUNCTION__ . ' Says: A Propel Query object must be defined as a data source!');
		
		if (is_null($this->rowsPerPage))
			$this->setRowsPerPage();
		
		$this->setPaginator();
		
		// sort
		$sort = $this->getPageSortFromRequest();
		if (is_array($sort))
		{
			$this->model->orderBy($sort['columnName'], $sort['sortOrder']);
		}
		
		// filter
		$filters = $this->getActiveFilters();
		foreach($filters as $columnName => $filterValue)
		{
			$this->model->where($this->getFilterCondition($columnName, $filterValue));
		}
		
		$this->cache($sort === false ? [] : $sort, $filters);
		
		$this->paginator->getAdapter()->setModel($this->model);
		$this->paginator->setItemCountPerPage($this->getRowsPerPage());
		$this->paginator->setCurrentPageNumber($this->getPage());
		$this->rows = $this->paginator->getCurrentItems();
		
		return $this;
	}

	/**
	 * store sort and filter arrays to session
	 * @param array $sort
	 * @param array $filters
	 */
	public function cache(array $sort = [], array $filters = [])
	{
		if (count($sort) > 0)
		{
			if ($this->storage->hasItem($this->fetchTableHash() . 'sort')) {
				$this->storage->replaceItem($this->fetchTableHash() . 'sort', $sort);
			} else {
				$this->storage->addItem($this->fetchTableHash() . 'sort', $sort);
			}
		} else {
			$this->storage->removeItem($this->fetchTableHash() . 'sort');
		}
		
		if (count($filters) > 0)
		{
			if ($this->storage->hasItem($this->fetchTableHash() . 'filter')) {
				$this->storage->replaceItem($this->fetchTableHash() . 'filter', $filters);
			} else {
				$this->storage->addItem($this->fetchTableHash() . 'filter', $filters);
			}
		} else {
			$this->storage->removeItem($this->fetchTableHash() . 'filter');
		}
		
	}
	
	/**
	 * return a string where clause for a given $column name and filter $value
	 * @param string $column
	 * @param mixed|null $value
	 * @return string
	 */
	private function getFilterCondition($column, $value) 
	{
		$filterMatchType = $this->columns[$column]['filter']['match'];
		switch ($filterMatchType) 
		{
			case self::FILTER_MATCH_BOTH : 
				return $column . " LIKE '%" . $value . "%' ";
				break;
			case self::FILTER_MATCH_EXACT : 
				return $column . " = '" . $value . "' ";
				break;
			case self::FILTER_MATCH_LEFT :
				return $column . " LIKE '%" . $value . " ";
				break;
			case self::FILTER_MATCH_NULL :
				return $column . " IS NULL ";
				break;
			case self::FILTER_MATCH_RIGHT :
			default : 
				return $column . " LIKE '" . $value . "%' ";
				break;
		}
	}
	
	/**
	 * Set the current HTTP request object
	 * @param Request $request
	 * @return $this
	 */
	public function setRequestObject(Request $request)
	{
		$this->request = $request;
		return $this;
	}
	
	/**
	 * return the Request object linked to this table
	 * @return Request
	 */
	public function getRequestObject()
	{
		return $this->request;
	}
	
	public function setStorage(Session $session)
	{
		$this->storage = $session;
		return $this;
	}
	
	
}