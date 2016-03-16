<?php
/**
 * Page LoadType to list all Segments with their Children
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 14 November 2015
 * @license AGPL-3.0
 */
require_once 'CRM/Core/Page.php';

class CRM_Streetimport_Page_LoadType extends CRM_Core_Page {
  private $_loadTypes = array();
  /**
   * Standard run function created when generating page with Civix
   *
   * @access public
   */
  function run() {
    $this->setPageConfiguration();
    $displayLoadTypes = $this->getLoadTypes();
    $this->assign('loadTypes', $displayLoadTypes);
    parent::run();
  }

  /**
   * Function to get the load types
   *
   * @return array $displayLoadTypes
   * @access protected
   */
  private function getLoadTypes() {
    $displayLoadTypes = array();
    foreach ($this->_loadTypes as $loadTypeName => $loadType) {
      $row = array();
      $row['label'] = $loadType['label'];
      $row['predecessor'] = $this->buildPredecessor($loadType['predecessor']);
      if ($loadType['unique'] == 1) {
        $row['unique'] = ts("Yes");
      } else {
        $row['unique'] = ts("No");
      }
      $settingsUrl = CRM_Utils_System::url('civicrm/siloadtypesettings', 'action=update&ltid='.$loadType['id'], true);
      $row['action'] = '<a class="action-item" title="Settings" href="'.$settingsUrl.'">Settings</a>';
      $displayLoadTypes[$loadType['id']] = $row;
    }
    return $displayLoadTypes;
  }

  /**
   * Method to get the label of the predecessor(s)
   *
   * @param $predecessor
   * @return string
   */
  private function buildPredecessor($predecessor) {
    if (empty($predecessor)) {
      return "";
    }
    if (!is_array($predecessor)) {
      return $this->_loadTypes[$predecessor]['label'];
    } else {
      $result = array();
      foreach ($predecessor as $name) {
        $result[] = $this->_loadTypes[$name]['label'];
      }
      return implode("; ", $result);
    }

  }

  /**
   * Function to set the page configuration
   *
   * @access protected
   */
  private function setPageConfiguration() {
    $loadType = new CRM_Streetimport_LoadType();
    $this->_loadTypes = $loadType->get();
    $config = CRM_Streetimport_Config::singleton();
    $this->assign('pageHelpText', $config->translate('The existing load types for Street Import are listed below.'));
    $this->assign('addButtonLabel', $config->translate('Add load type'));
    $this->assign('loadTypeLabel', $config->translate('Label'));
    $this->assign('predecessorLabel', $config->translate('Only after'));
    $this->assign('uniqueLabel', $config->translate('Can only exist once'));
    CRM_Utils_System::setTitle($config->translate('Street Import Load Types'));
    $this->assign('addUrl', CRM_Utils_System::url('civicrm/siloadtype', 'action=add&reset=1', true));
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext(CRM_Utils_System::url('civicrm/siloadtypelist', 'reset=1', true));
  }
}
