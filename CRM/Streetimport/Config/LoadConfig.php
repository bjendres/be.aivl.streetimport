<?php
/**
 * Class to load or update the initial configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 18 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Streetimport_Config_LoadConfig {

  protected $_resourcesPath = null;

  /**
   * CRM_Streetimport_Config_LoadConfig constructor.
   */
  function __construct() {
    $config = CRM_Streetimport_Config::singleton();
    $this->_resourcesPath = $config->getResourcePath();
    $this->setContactTypes();
    $this->setRelationshipTypes();
    $this->setOptionGroups();
    $this->setGroups();
    $this->setActivityTypes();
    // customData as last one because it might need one of the previous ones (option group, relationship types)
    $this->setCustomData();
  }

  /**
   * Method to create or get relationship types
   *
   * @throws Exception when resource file could not be loaded
   */
  protected function setRelationshipTypes() {
    $jsonFile = $this->_resourcesPath.'relationship_types.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load relationship types configuration file for extension,
      contact your system administrator!');
    }
    $relationshipTypesJson = file_get_contents($jsonFile);
    $relationshipTypes = json_decode($relationshipTypesJson, true);
    foreach ($relationshipTypes as $relationName => $params) {
      $relationshipType = new CRM_Streetimport_Config_RelationshipType();
      $relationshipType->create($params);
    }
  }

  /**
   * Method to create option groups
   *
   * @throws Exception when resource file not found
   * @access protected
   */
  protected function setOptionGroups() {
    $jsonFile = $this->_resourcesPath.'option_groups.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load option_groups configuration file for extension,
      contact your system administrator!');
    }
    $optionGroupsJson = file_get_contents($jsonFile);
    $optionGroups = json_decode($optionGroupsJson, true);
    foreach ($optionGroups as $name => $optionGroupParams) {
      $optionGroup = new CRM_Streetimport_Config_OptionGroup();
      $optionGroup->create($optionGroupParams);
    }
  }

  /**
   * Method to create contact types
   *
   * @throws Exception when resource file not found
   * @access protected
   */
  protected function setContactTypes() {
    $jsonFile = $this->_resourcesPath.'contact_sub_types.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load contact_sub_types configuration file for extension,
      contact your system administrator!');
    }
    $contactTypesJson = file_get_contents($jsonFile);
    $contactTypes = json_decode($contactTypesJson, true);
    foreach ($contactTypes as $name => $params) {
      $contactType = new CRM_Streetimport_Config_ContactType();
      $contactType->create($params);
    }
  }

  /**
   * Method to create activity types
   *
   * @throws Exception when resource file not found
   * @access protected
   */
  protected function setActivityTypes() {
    $jsonFile = $this->_resourcesPath.'activity_types.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load activity_types configuration file for extension,
      contact your system administrator!');
    }
    $activityTypesJson = file_get_contents($jsonFile);
    $activityTypes = json_decode($activityTypesJson, true);
    foreach ($activityTypes as $name => $params) {
      $activityType = new CRM_Streetimport_Config_ActivityType();
      $activityType->create($params);
    }
  }

  /**
   * Method to create or get groups
   *
   * @throws Exception when resource file could not be loaded
   */
  protected function setGroups() {
    $jsonFile = $this->_resourcesPath . 'groups.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load groups configuration file for extension,
      contact your system administrator!');
    }
    $groupJson = file_get_contents($jsonFile);
    $groups = json_decode($groupJson, true);
    foreach ($groups as $params) {
      $group = new CRM_Streetimport_Config_Group();
      $group->create($params);
    }
  }

  /**
   * Method to set the custom data groups and fields
   *
   * @throws Exception when config json could not be loaded
   * @access protected
   */
  protected function setCustomData() {
    $jsonFile = $this->_resourcesPath.'custom_data.json';
    if (!file_exists($jsonFile)) {
      throw new Exception('Could not load custom data configuration file for extension, contact your system administrator!');
    }
    $customDataJson = file_get_contents($jsonFile);
    $customData = json_decode($customDataJson, true);
    foreach ($customData as $customGroupName => $customGroupData) {
      $customGroup = new CRM_Streetimport_Config_CustomGroup();
      $created = $customGroup->create($customGroupData);
      foreach ($customGroupData['fields'] as $customFieldName => $customFieldData) {
        $customFieldData['custom_group_id'] = $created['id'];
        $customField = new CRM_Streetimport_Config_CustomField();
        $customField->create($customFieldData);
      }
      // remove custom fields that are still on install but no longer in config
      CRM_Streetimport_Config_CustomField::removeUnwantedCustomFields($created['id'], $customGroupData);
    }
  }
}