<?php
/**
 * LoadConfig.Update API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_load_config_update($params) {
  $config = CRM_Streetimport_Config::singleton();
  $returnValues = array($config->translate('Data Setup for Street Import Updated'));
  new CRM_Streetimport_Config_LoadConfig();
  return civicrm_api3_create_success($returnValues, $params, 'LoadConfig', 'Update');
}

