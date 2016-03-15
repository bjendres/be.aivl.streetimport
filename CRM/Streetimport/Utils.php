<?php
/**
 * Class with extension specific util functions
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 30 April 2015
 * @license AGPL-3.0
 */

class CRM_Streetimport_Utils {

  /**
   * Method to determine max key in navigation menu (core solutions do not cater for child keys!)
   *
   * @param array $menuItems
   * @return int $maxKey
   */
  public static function getMaxMenuKey($menuItems) {
    $maxKey = 0;
    foreach ($menuItems as $menuKey => $menuItem) {
      if ($menuKey > $maxKey) {
        $maxKey = $menuKey;
      }
      if (isset($menuItem['child'])) {
        foreach ($menuItem['child'] as $childKey => $child) {
          if ($childKey > $maxKey) {
            $maxKey = $childKey;
          }
        }
      }
    }
    return $maxKey;
  }

  /**
   * Function to get activity type with name
   *
   * @param string $activityTypeName
   * @return array|bool
   * @access public
   * @static
   */
  public static function getActivityTypeWithName($activityTypeName) {
    $optionGroup = self::getOptionGroupWithName('activity_type');
    $activityTypeOptionGroupId = $optionGroup['id'];
    $params = array(
      'option_group_id' => $activityTypeOptionGroupId,
      'name' => $activityTypeName);
    try {
      $activityType = civicrm_api3('OptionValue', 'Getsingle', $params);
      return $activityType;
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Function to get activity status id with name
   *
   * @param string $activityStatusName
   * @return int|bool
   * @access public
   * @static
   */
  public static function getActivityStatusIdWithName($activityStatusName) {
    $optionGroup = self::getOptionGroupWithName('activity_status');
    $activityStatusOptionGroupId = $optionGroup['id'];
    $params = array(
      'option_group_id' => $activityStatusOptionGroupId,
      'name' => $activityStatusName);
    try {
      $activityStatus = civicrm_api3('OptionValue', 'Getsingle', $params);
      return $activityStatus['value'];
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Function to get the option group id
   *
   * @param string $optionGroupName
   * @return int $optionGroupId
   * @access public
   * @static
   */
  public static function getOptionGroupWithName($optionGroupName) {
    $params = array(
      'name' => $optionGroupName,
      'is_active' => 1);
    try {
      $optionGroup = civicrm_api3('OptionGroup', 'Getsingle', $params);
      return $optionGroup;
    } catch (CiviCRM_API3_Exception $ex) {
      return array();
    }
  }

  /**
   * Public function to generate label from name
   *
   * @param $name
   * @return string
   * @access public
   * @static
   */
  public static function buildLabelFromName($name) {
    $nameParts = explode('_', strtolower($name));
    foreach ($nameParts as $key => $value) {
      $nameParts[$key] = ucfirst($value);
    }
    return implode(' ', $nameParts);
  }

  /**
   * Function to get contact name
   *
   * @param int $contactId
   * @return string $contactName
   * @access public
   * @static
   */
  public static function getContactName($contactId) {
    $params = array(
      'id' => $contactId,
      'return' => 'display_name');
    try {
      $contactName = civicrm_api3('Contact', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      $contactName = '';
    }
    return $contactName;
  }
  
  /**
   * Function to get the contact id for a donor without error logging.
   * However, exceptions are thrown if needed.
   * Can be safely used in error logging.
   * 
   * @param int $donorId
   * @param int $recruitingOrganizationId
   * @return mixed
   * @throws Exception when more than 1 contact found
   */
  public static function getContactIdFromDonorId($donorId, $recruitingOrganizationId) {
    $config = CRM_Streetimport_Config::singleton();
    $tableName = $config->getExternalDonorIdCustomGroup('table_name');
    $donorCustomField = $config->getExternalDonorIdCustomFields('external_donor_id');
    $orgCustomField = $config->getExternalDonorIdCustomFields('recruiting_organization_id');
    if (empty($donorCustomField)) {
      throw new Exception($config->translate("CustomField external_donor_id not found. Please reinstall."));
    }
    if (empty($orgCustomField)) {
      throw new Exception($config->translate("CustomField recruiting_organization_id not found. Please reinstall."));
    }
    $query = 'SELECT entity_id FROM '.$tableName.' WHERE '.$donorCustomField['column_name'].' = %1 AND '.$orgCustomField['column_name'].' = %2';
    $params = array(
      1 => array($donorId, 'Positive'),
      2 => array($recruitingOrganizationId, 'Positive'));

    $dao = CRM_Core_DAO::executeQuery($query, $params);
    if ($dao->N > 1) {
      throw new Exception($config->translate('More than one contact found for donor ID').': '.$donorId);
    }
     
    if ($dao->fetch()) {
      return $dao->entity_id;
    }
    else {
      return NULL;
	}
  }

  /**
   * Function get country data with an iso code
   *
   * @param $isoCode
   * @return array
   */
  public static function getCountryByIso($isoCode) {
    $country = array();
    if (empty($isoCode)) {
      return $country;
    }
    $query = 'SELECT * FROM civicrm_country WHERE iso_code = %1';
    $params = array(1 => array($isoCode, 'String'));
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    if ($dao->fetch()) {
      $country['country_id'] = $dao->id;
      $country['name'] = $dao->name;
      $country['iso_code'] = $dao->iso_code;
    }
    return $country;
  }

  /**
   * Method to determine gender with prefix
   *
   * @param $prefix
   * @return int
   */
  public static function determineGenderWithPrefix($prefix) {
    $config = CRM_Streetimport_Config::singleton();
    $prefix = strtolower($prefix);
    switch ($prefix) {
      case 'meneer':
        return $config->getMaleGenderId();
      break;
      case 'mevrouw':
        return $config->getFemaleGenderId();
      break;
      default:
        return $config->getUnknownGenderId();
      break;
    }
  }

  /**
   * Method to get list of active option values for select lists
   *
   * @param string $optionGroupName
   * @return array
   * @throws Exception when no option group found
   * @access public
   * @static
   */
  public static function getOptionGroupList($optionGroupName) {
    $valueList = array();
    $optionGroupParams = array(
      'name' => $optionGroupName,
      'return' => 'id');
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $optionGroupParams);
      $optionValueParams = array(
        'option_group_id' => $optionGroupId,
        'is_active' => 1,
        'options' => array('limit' => 99999));
      $optionValues = civicrm_api3('OptionValue', 'Get', $optionValueParams);
      foreach ($optionValues['values'] as $optionValue) {
        $valueList[$optionValue['value']] = $optionValue['label'];
      }
      $valueList[0] = ts('- select -');
      asort($valueList);
    } catch (CiviCRM_API3_Exception $ex) {
      $config = CRM_Streetimport_Config::singleton();
      throw new Exception($config->translate('Could not find an option group with name').' '.$optionGroupName
        .' ,'.$config->translate('contact your system administrator').' .'
        .$config->translate('Error from API OptionGroup Getvalue').': '.$ex->getMessage());
    }
    return $valueList;
  }

  /**
   * Method to set list of date formats for import files
   *
   * @access public
   * @static
   * @return array
   */
  public static function getDateFormatList() {
    return array('dd-mm-jjjj', 'dd/mm/jjjj', 'dd-mm-jj', 'dd/mm/jj', 'jjjj-mm-dd', 'jjjj/mm/dd', 'jj-mm-dd','jj/mm/dd',
      'mm-dd-jjjj', 'mm/dd/jjjj', 'mm-dd-jj', 'mm/dd/jj');
  }

  /**
   * Method to format the CSV import date if new DateTime has thrown error
   *
   * @param $inDate
   * @return string
   */
  public static function formatCsvDate($inDate) {
    if (empty($inDate)) {
      return $inDate;
    }
    $config = CRM_Streetimport_Config::singleton();
    $inDay = null;
    $inMonth = null;
    $inYear = null;
    switch ($config->getCsvDateFormat()) {
      case 0:
        $dateParts = explode("-", $inDate);
        $inDay = $dateParts[0];
        $inMonth = $dateParts[1];
        $inYear = $dateParts[2];
        break;
      case 1:
        $dateParts = explode("/", $inDate);
        if (isset($dateParts[1]) && isset($dateParts[2])) {
          $inDay = $dateParts[0];
          $inMonth = $dateParts[1];
          $inYear = $dateParts[2];
        }
        break;
      case 2:
        $dateParts = explode("-", $inDate);
        $inDay = $dateParts[0];
        $inMonth = $dateParts[1];
        $inYear = $dateParts[2];
        break;
      case 3:
        $dateParts = explode("/", $inDate);
        $inDay = $dateParts[0];
        $inMonth = $dateParts[1];
        $inYear = $dateParts[2];
        break;
      case 4:
        $dateParts = explode("-", $inDate);
        $inDay = $dateParts[2];
        $inMonth = $dateParts[1];
        $inYear = $dateParts[0];
        break;
      case 5:
        $dateParts = explode("/", $inDate);
        $inDay = $dateParts[2];
        $inMonth = $dateParts[1];
        $inYear = $dateParts[0];
        break;
      case 6:
        $dateParts = explode("-", $inDate);
        $inDay = $dateParts[2];
        $inMonth = $dateParts[1];
        $inYear = $dateParts[0];
        break;
      case 7:
        $dateParts = explode("/", $inDate);
        $inDay = $dateParts[2];
        $inMonth = $dateParts[1];
        $inYear = $dateParts[0];
        break;
      case 8:
        $dateParts = explode("-", $inDate);
        $inDay = $dateParts[1];
        $inMonth = $dateParts[0];
        $inYear = $dateParts[2];
        break;
      case 9:
        $dateParts = explode("/", $inDate);
        $inDay = $dateParts[1];
        $inMonth = $dateParts[0];
        $inYear = $dateParts[2];
        break;
      case 10:
        $dateParts = explode("-", $inDate);
        $inDay = $dateParts[1];
        $inMonth = $dateParts[0];
        $inYear = $dateParts[2];
        break;
      case 11:
        $dateParts = explode("/", $inDate);
        $inDay = $dateParts[1];
        $inMonth = $dateParts[0];
        $inYear = $dateParts[2];
        break;
      default:
        return $inDate;
      break;
    }
    return $inDay.'-'.$inMonth.'-'.$inYear;
  }
}
