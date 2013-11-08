<?php
// Define JRequest::clean to protect our variables!
define('_JREQUEST_NO_CLEAN', 1); 

// basic to make J! happy
define('_JEXEC', 1); //make j! happy
define('JPATH_BASE', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

// Load up the standard stuff for testing
require_once JPATH_BASE . DS . 'includes' . DS . 'defines.php';
require_once JPATH_BASE . DS . 'includes' . DS . 'framework.php';

$mainframe = JFactory::getApplication('site');
$mainframe->initialise();

class StarHelper
{
    protected $table = '';
    protected $db = '';
    protected $query = '';

    function __construct() {
        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);
    }

    public function setTable($table = '')
    {
        if ($table == '') {
            echo 'Table ' . $table . ' Not Found';
            die;
        }

        $table = $this->db->replacePrefix('#__' . $table);
        $tables = $this->db->getTableList();

        if (in_array($table, $tables)) {
            $this->table = $table;
        }
    }

    public function getCorrectTableName($tableName)
    {
        return $this->db->replacePrefix('#__' . $tableName);
    }

    /**
     * Function to get rows from the table
     *
     * @return array
     * @author Manoj Kumar
     **/
    public function getRows($id = null, $columns = array('*'), $whereColumn = 'id', $singleRow = true)
    {
        $this->query
            ->select($columns)
            ->from($this->table);

        if (!is_null($id)) {
            $this->query
                ->where($this->db->quoteName($whereColumn) . ' = ' . $this->db->quote($id));

            $this->db->setQuery($this->query);
            $this->flushQuery();

            if ($singleRow) {
                return $this->db->loadObject();
            }
            else {
                return $this->db->loadObjectList();
            }
        }

        $this->db->setQuery($this->query);
        $this->flushQuery();
        return $this->db->loadObjectList();
    }

    public function getRow($id = null, $columns = array('*'), $whereColumn = 'id')
    {
        $this->query
            ->select($columns)
            ->from($this->table);

        if (!is_null($id)) {
            $this->query
                ->where($this->db->quoteName($whereColumn) . ' = ' . $this->db->quote($id));

            $this->db->setQuery($this->query);
            $this->flushQuery();

            return $this->db->loadObject();
        }

        $this->db->setQuery($this->query);
        $this->flushQuery();
        return $this->db->loadObjectList();
    }

    public function joinTable($joinType = 'INNER', $table, $primaryKeyColumnName, $foreignKeyColumnName)
    {
        $tableName = $this->getCorrectTableName($table);
        $this->query
            ->join($joinType, $this->db->quoteName($tableName) . ' ON ' . $primaryKeyColumnName . ' = ' . $foreignKeyColumnName);
    }

    public function updateTable($object, $primaryColumnName)
    {
        if (!isset($object->id)) {
            $this->db->insertObject($this->table, $object);
        }
        else {
            $this->db->updateObject($this->table, $object, $primaryColumnName);
        }
    }

    public function deleteRecord($conditions)
    {
        $db = $this->db;
        $query = $db->getQuery(true);

        $query->delete($db->quoteName($this->table));
        $query->where($conditions);

        $db->setQuery($query);
        $db->query();
    }

    public function flushQuery()
    {
        $this->query = $this->db->getQuery(true);
    }

    public function getResponseFromUrl($url, $post = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!empty($post)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        $result = curl_exec ($ch);

        curl_close ($ch);

        return $result;
    }
}
