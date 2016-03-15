<?php
/**
 * Class for processing of Load Types
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 17 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Streetimport_LoadType {

  private $_resourcePath = NULL;
  private $_loadTypes = array();


  /**
   * CRM_Streetimport_LoadType constructor.
   */
  public function __construct() {
    $config = CRM_Streetimport_Config::singleton();
    $this->_resourcePath = $config->getResourcePath();
    $this->load();
  }

  /**
   * Method to get generic settings
   *
   * @return array
   */
  public function get() {
    return $this->_loadTypes;
  }

  /**
   * Method to load the load types from the JSON file
   *
   * @throws Exception when file can not be loaded
   */
  private function load() {
    $jsonFile = $this->_resourcePath.'load_types.json';
    if (!file_exists($jsonFile)) {
      throw new Exception(ts('Could not load load_types configuration file for extension,
      contact your system administrator!'));
    }
    $loadTypesJson = file_get_contents($jsonFile);
    $this->_loadTypes = json_decode($loadTypesJson, true);
  }
}