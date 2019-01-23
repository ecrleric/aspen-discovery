<?php
/**
 * Table Definition for Obituary
 */
require_once 'DB/DataObject.php';
require_once 'DB/DataObject/Cast.php';

class Obituary extends DB_DataObject {
	public $__table = 'obituary'; // table name
	public $obituaryId;
	public $personId;
	public $source;
	public $date;
	public $dateDay;
	public $dateMonth;
	public $dateYear;
	public $sourcePage;
	public $contents;
	public $picture;

	function keys() {
		return array('obituaryId');
	}

	function id() {
		return $this->obituaryId;
	}

	function label() {
		return $this->source . ' ' . $this->sourcePage . ' ' . $this->date;
	}

	function getObjectStructure() {
		$structure = array(
			array('property' => 'obituaryId', 'type' => 'label', 'label' => 'Id', 'description' => 'The unique id of the obituary in the database', 'storeDb' => true),
			array('property' => 'personId', 'type' => 'hidden', 'label' => 'Person Id', 'description' => 'The id of the person this obituary is for', 'storeDb' => true),
			//array('property'=>'person', 'type'=>'method', 'label'=>'Person', 'description'=>'The person this obituary is for', 'storeDb' => false),
			array('property' => 'source', 'type' => 'text', 'maxLength' => 100, 'label' => 'Source', 'description' => 'The source of the obituary', 'storeDb' => true),
			array('property' => 'sourcePage', 'type' => 'text', 'maxLength' => 100, 'label' => 'Source Page', 'description' => 'The page where the obituary was found', 'storeDb' => true),
			array('property' => 'date', 'type' => 'partialDate', 'label' => 'Date', 'description' => 'The date of the obituary.', 'storeDb' => true, 'propNameMonth' => 'dateMonth', 'propNameDay' => 'dateDay', 'propNameYear' => 'dateYear'),
			array('property' => 'contents', 'type' => 'textarea', 'rows' => 10, 'cols' => 80, 'label' => 'Full Text of the Obituary', 'description' => 'The full text of the obituary.', 'storeDb' => true, 'hideInLists' => true),
			array('property' => 'picture', 'type' => 'image', 'thumbWidth' => 65, 'mediumWidth' => 250, 'label' => 'Picture', 'description' => 'A scanned image of the obituary.', 'storeDb' => true, 'storeSolr' => false, 'hideInLists' => true),
		);
		return $structure;
	}

	function insert() {
		$ret = parent::insert();
		//Load the person this is for, and update solr
		if ($this->personId) {
			require_once ROOT_DIR . '/sys/Genealogy/Person.php';
			$person = new Person();
			$person->personId = $this->personId;
			$person->find(true);
			$person->saveToSolr();
		}
		return $ret;
	}

	function update($dataObject = false) {
		$ret = parent::update($dataObject);
		//Load the person this is for, and update solr
		if ($this->personId) {
			require_once ROOT_DIR . '/sys/Genealogy/Person.php';
			$person = new Person();
			$person->personId = $this->personId;
			$person->find(true);
			$person->saveToSolr();
		}
		return $ret;
	}

	function delete($useWhere = false) {
		$personId = $this->personId;
		$ret = parent::delete($useWhere);
		//Load the person this is for, and update solr
		if ($personId) {
			require_once ROOT_DIR . '/sys/Genealogy/Person.php';
			$person = new Person();
			$person->personId = $this->personId;
			$person->find(true);
			$person->saveToSolr();
		}
		return $ret;
	}
}