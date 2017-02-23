<?php
namespace Application\Controller\Plugin;

use Application\Exception\GenericException;
use Application\Service\PropelTableService;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Application\View\Model\CsvModel;

class ExportTableControllerPlugin extends AbstractPlugin
{

    /**
     * csv columns headings
     * @var array
     */
    protected $headings = [];

    /**
     * csv data to export
     * @var array
     */
    protected $csvData = [];

    /**
     * table to export
     * @var PropelTableService
     */
    protected $table;

    /**
     * @var ViewHelperPluginManager
     */
    protected $viewHelperManager;


    public function __invoke(PropelTableService $table)
    {
        $this->table = $table;
        $this->viewHelperManager = $this->getController()->viewHelperManager;

        $this->fetchColumnHeading()->fetchCSVData();

        return $this->toCSV();
    }

    /**
     * output csv
     *
     * @return CsvModel
     */
    private function toCSV()
    {
        $csvModel = new CsvModel();

        $now = new \DateTime();

        $csvModel->setFilename('Export_' . $now->format('d-m-Y'));
        $csvModel->setColumnHeaders(array_values($this->headings));
        $csvModel->setData($this->csvData);
        $fileContents = $csvModel->write();

        return $fileContents;
    }

    /**
     * populate and return data to write to CSV from $this->table
     *
     * @return ExportTableControllerPlugin
     */
    private function fetchCSVData()
    {
        $model  = $this->table->getPropelModel();
        $data   = $model->limit($this->table->getPaginator()->getTotalItemCount())->find();
        foreach($data as $d)
        {
            $include = [];

            foreach($this->table->getColumns() as $fieldName => $details) {

                $method = 'get' . $fieldName;
                if (!method_exists($d, $method)) {
                    throw new GenericException('Unable to extract data for column ' . $fieldName . ', method ' . $method . ' not found');
                } else {
                    $value = $d->$method();
                }

                if ($value instanceOf \DateTime && !isset($details['helper'])) {
                    $value = $value->format('j M Y');
                } elseif (is_bool($value)) {
                    switch($value)
                    {
                        case true :
                            $value = 'Yes';
                            break;
                        case false :
                            $value = 'No';
                            break;
                        default :
                            $value = '';
                            break;
                    }
                } elseif (isset($details['helper'])) {

                    if ($details['helper'] instanceOf \Closure)
                    {
                        $value = $details['helper']($value, $d);
                    } elseif (isset($details['helper']) && isset($details['helper']['name'])) {

                        $params = [$value];

                        if (isset($details['helper']['params']))
                        {
                            $helperParams = is_array($details['helper']['params']) ? $details['helper']['params'] : [$details['helper']['params']];
                            $params = array_merge($params,$helperParams);
                        }

                        if (isset($details['helper']['parseRow']) && $details['helper']['parseRow'] === true)
                        {
                            $params[] = $d;
                        }

                        $vh = $this->viewHelperManager->get($details['helper']['name']);
                        $value = call_user_func_array($vh , $params);
                    }
                }

                $vh = $this->viewHelperManager->get('escapeHtml');
                $value = call_user_func_array($vh , [$value]);

                $include[] = $value;
            }

            $this->csvData[] = $include;
        }

        return $this;
    }

    /**
     * populate columns headers from columns in $this->table
     *
     * @return ExportTableControllerPlugin
     */
    private function fetchColumnHeading()
    {
        $headings = array();

        foreach ($this->table->getColumns() as $key => $details) {

            $columnName = $this->getController()->translate($details['label']);

            /**
             * NB excel cannot have columns called 'ID' causes a format failure
             */
            if ($columnName == 'ID')
            {
                $columnName = '_ID';
            }

            $headings[$key] = $columnName;
        }

        $this->headings = $headings;

        return $this;
    }

}