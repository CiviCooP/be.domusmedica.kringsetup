<?php
/**
 * Class for ContactType configuration
 *
 * @author Klaas Eikelboom (CiviCooP) <klaas.eikelboom@civicoop.org>
 * @date 17 Jan 2018
 * @license AGPL-3.0
 */
class CRM_Kringsetup_ConfigItems_LocationType {

  protected $_apiParams = array();

  /**
   * Method to validate params for create
   *
   * @param $params
   * @throws Exception when missing mandatory params
   */
  protected function validateCreateParams($params) {
    if (!isset($params['name']) || empty($params['name'])) {
      throw new Exception('Missing mandatory param name in '.__METHOD__);
    }
    $this->_apiParams = $params;
  }

  /**
   * Method to create membership type
   *
   * @param array $params
   * @return mixed
   * @throws Exception when error from API ContactType Create
   */
  public function create($params) {
    $this->validateCreateParams($params);
    $existing = $this->getWithName($this->_apiParams['name']);
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
    }
    $this->_apiParams['is_active'] = 1;
    try {
      civicrm_api3('LocationType', 'Create', $this->_apiParams);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not create or update contact type with name '.$this->_apiParams['name']
        .' in '.__METHOD__.', error from API LocationType Create: '.$ex->getMessage());
    }
  }


  /**
   * Method to get membership sub type with name
   *
   * @param string $membershipTypeName
   * @return array|bool
   * @access public
   * @static
   */
  public function getWithName($locationTypeName) {
    try {
      return civicrm_api3('LocationType', 'Getsingle', array('name' => $locationTypeName));
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to disable membership type
   *
   * @param $locationTypeName
   */
  public function disableLocationType($locationTypeName) {
    if (!empty($locationTypeName)) {
      // catch any errors and ignore (disabling can be done manually if problems)
      try {
        // get membership type with name
        $locationTypeId = civicrm_api3('LocationType', 'getvalue', array('name' => $locationTypeName, 'return' => 'id'));
        $sqlContactType = "UPDATE civicrm_location_type SET is_active = %1 WHERE id = %2";
        CRM_Core_DAO::executeQuery($sqlContactType, array(
          1 => array(0, 'Integer'),
          2 => array($locationTypeId, 'Integer')));
      } catch (CiviCRM_API3_Exception $ex) {
      }
    }
  }

  /**
   * Method to enable membership type
   *
   * @param $locationTypeName
   */
  public function enableContactType($locationTypeName) {
    if (!empty($locationTypeName)) {
      // catch any errors and ignore (disabling can be done manually if problems)
      try {
        // get membership type with name
        $locationTypeId = civicrm_api3('LocationType', 'getvalue', array('name' => $locationTypeName, 'return' => 'id'));
        $sqlContactType = "UPDATE civicrm_membership_type SET is_active = %1 WHERE id = %2";
        CRM_Core_DAO::executeQuery($sqlContactType, array(
          1 => array(1, 'Integer'),
          2 => array($locationTypeId, 'Integer')));
      } catch (CiviCRM_API3_Exception $ex) {
      }
    }
  }

  /**
   * Method to uninstall membership type
   *
   * @param $locationTypeName
   */
  public function uninstallContactType($locationTypeName) {
    if (!empty($locationTypeName)) {
      // catch any errors and ignore (disabling can be done manually if problems)
      try {
        // get membership type with name
        $locationTypeId = civicrm_api3('LocationType', 'getvalue', array('name' => $locationTypeName, 'return' => 'id'));
        civicrm_api3('LocationType', 'delete', array('id' => $locationTypeId,));
      } catch (CiviCRM_API3_Exception $ex) {
      }
    }
  }
}