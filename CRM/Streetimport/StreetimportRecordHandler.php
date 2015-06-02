<?php
/**
 * Abstract class bundle common street import functions
 *
 * @author Björn Endres (SYSTOPIA) <endres@systopia.de>
 * @license AGPL-3.0
 */
abstract class CRM_Streetimport_StreetimportRecordHandler extends CRM_Streetimport_RecordHandler {

  /** 
   * in case the recruiter ID is not set,
   * we will create a new recruiter, but store it so we can use the same one
   * 
   * keys for the array are "$last_name//$first_name//$prefix"
   */
  protected $created_recruiters = array();


  protected function getConfigValue($key) {
    // TODO: replace with config/ts lookup
    if ($key=='Recruiter') {
      return 'Werver';
    } elseif ($key=='Unknown Recruiter') {
      return 'Unknown Werver';
    } else {
      return 'LOOKUP-ERROR';
    }
  }


  /** 
   * look up the recruiting organisation
   *
   * From the process description:
   * "For first record of file: check if recruiting organization exists.
   * If it does not, create activity of type ‘Foutieve data import’ with 
   * relevant error message in subject and details. No further processing of file possible."
   */
  protected function getRecruitingOrganisation($record) {
    if (empty($record['Recruiting organization ID'])) {
      $this->logger->abort("Recruiting organization ID not given.");
      return NULL;
    }
    $recruiting_organisation = $this->getContact((int) $record['Recruiting organization ID'], true);
    if ($recruiting_organisation==NULL) {
      $this->logger->abort("Recruiting organization [{$record['Recruiting organization ID']}] not found.", true);
      return NULL;
    }
    $this->logger->logDebug("Recruiting organization identified as contact [{$recruiting_organisation['id']}]");
    return $recruiting_organisation;
  }

  /**
   * this method will lookup or create the recruiter
   *
   * From the process description:
   * "check if recruiter exists. If not, create recruiter (see CSV description) 
   * and create activity of type ‘Foutieve data import’ 
   * with relevant error message in subject and details"
   *
   * @return $recruiter array with contact entity
   */
  protected function processRecruiter($record, $recruiting_organisation) {
    $recruiter = NULL;
    if (!empty($record['Recruiter ID'])) {
      $recruiter = $this->getContact($record['Recruiter ID']);
    }

    if ($recruiter==NULL) {
      $this->logger->logDebug("Recruiter not found, creting new one...");      
      // "If the contact is not known, a contact of the contact subtype 'Werver' is to be created"
      $recruiter_data = array(
        'contact_type'     => 'Individual',
        'contact_sub_type' => $this->getConfigValue('Recruiter'),
        'first_name'       => CRM_Utils_Array::value('Recruiter First Name', $record),
        'last_name'        => CRM_Utils_Array::value('Recruiter Last Name',  $record),
        'prefix'           => CRM_Utils_Array::value('Recruiter Prefix', $record),
      );
      $recruiter_known = TRUE;
      
      // "If the first name and last name are empty, the values 'Unknown Werver' 
      //  "and 'Organization name of recruiting org' will be used as first and last name."
      if (empty($record['Recruiter First Name']) && empty($record['Recruiter Last Name'])) {
        $recruiter_data['first_name'] = $this->getConfigValue('Unknown Recruiter');
        $recruiter_data['last_name']  = CRM_Utils_Array::value('organization_name', $recruiting_organisation);
        $recruiter_data['prefix']     = '';
        $recruiter_known = FALSE;
      }

      // check if we had already created the recruiter
      $recruiter_key = "{$recruiter_data['last_name']}//{$recruiter_data['first_name']}//{$recruiter_data['prefix']}";
      if (!empty($this->created_recruiters[$recruiter_key])) {
        // ...and indeed we have
        $recruiter = $this->created_recruiters[$recruiter_key];
        $this->logger->logDebug("Recruiter [{$recruiter['id']}] already created.");
        return $recruiter;
      }

      // else, we have to create the recruiter...
      $recruiter = $this->createContact($recruiter_data, true);
      if (!$recruiter) {
        $this->logger->abort("Recruiter could not be created.");
      }

      // ..."with a relationship 'Werver' to the recruiting organization."
      $this->createRelationship($this->getConfigValue('Recruiter'), $recruiter['id'], $recruiting_organisation['id']);

      // "In all cases where the contact is not known, an activity of the type 'Incompleet werver contact' 
      //     will be generated  and assigned to the admin ID entered as a param"
      if (!$recruiter_known) {
        $this->createActivity(array(
                              'activity_type_id'   => 2,                   // TODO: config (FollowUp)
                              'subject'            => 'Incompleet werver contact', // TODO: config
                              'status_id'          => 1,                   // TODO: config
                              'activity_date_time' => date('YmdHis'),
                              'target_contact_id'  => (int) $donor['id'],
                              'source_contact_id'  => $recruiter['id'],
                              'assignee_contact_id'=> (int) $record['Admin ID'],
                              'details'            => $this->renderTemplate('activities/IncompleteRecruiterContact.tpl', $record),
                              ));        
      }

      // finally, store the result so we don't create the same recruiter over and over
      $this->created_recruiters[$recruiter_key] = $recruiter;
      $this->logger->logDebug("Recruiter [{$recruiter['id']}] created.");
    } else {
      $this->logger->logDebug("Recruiter [{$record['Recruiter ID']}] found.");
    }

    return $recruiter;
  }

  /**
   * Manages the contact_id <-> donor_id (external) mapping
   */
  protected function setDonorID($contact_id, $donor_id) {
    // TODO: implement
    $this->logger->logError("setDonorID not implemented!");
  }

  /**
   * Manages the contact_id <-> donor_id (external) mapping
   * 
   * @return mixed contact_id or NULL if not found
   */
  protected function getContactForDonorID($donor_id) {
    // TODO: implement
    $this->logger->logError("getContactForDonorID not implemented!");
    return NULL;
  }

  /**
   * Create CiviSEPA mandate
   */
  protected function createSDDMandate($mandate_data) {
    // TODO: implement
    $this->logger->logError("createSDDMandate not implemented!");
    return NULL;    
  }
 
}