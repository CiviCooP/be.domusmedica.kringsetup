<?php
/**
 * Class for OptionValue configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 31 May 2017
 * @license AGPL-3.0
 */
class CRM_Kringsetup_ConfigItems_OptionValue {

  protected $_apiParams = array();

  /**
   * CRM_Kringsetup_ConfigItems_OptionValue constructor.
   */
  public function __construct() {
    $this->_apiParams = array();
    // standard name is used for identification
    $this->_key = 'name';
  }

  /**
   * Method to validate params for create
   *
   * @param $params
   * @throws Exception when missing mandatory params
   */
  protected function validateCreateParams($params) {
    if (!isset($params['name']) || empty($params['name'])) {
      throw new Exception(ts('Missing mandatory param name in class '.__METHOD__));
    }
    if (!isset($params['option_group_id']) || empty($params['option_group_id'])) {
      throw new Exception(ts('Missing mandatory param option_group_id in '.__METHOD__));
    }
    if (isset($params['key'])){
      $this->_key=$params['key'];
      // remove key from parameters (it has done its job)
      unset($params['key']);
    }
    if($this->_key!='name'&&$this->_key!='value'){
      throw new Exception(ts('Invalid option value key type '.$this->_key));
    }
    $this->_apiParams = $params;
  }

  /**
   * Method to create or update option value
   *
   * @param $params
   * @return array
   * @throws Exception when error in API Option Value Create
   */
  public function create($params) {
    $this->validateCreateParams($params);
    $existing = $this->getWithKeyAndOptionGroupId();
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
    }
    if (!isset($this->_apiParams['is_active'])) {
      $this->_apiParams['is_active'] = 1;
    }
    if (!isset($this->_apiParams['is_reserved'])) {
      $this->_apiParams['is_reserved'] = 1;
    }
    if (!isset($this->_apiParams['label'])) {
      $this->_apiParams['label'] = ucfirst($this->_apiParams['name']);
    }
    try {
      return civicrm_api3('OptionValue', 'Create', $this->_apiParams);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception(ts('Could not create or update option_value with name'.$this->_apiParams['name']
        .' in option group with id '.$this->_apiParams['option_group_id'].' in '.__METHOD__
          .', error from API OptionValue Create: ').$ex->getMessage());
    }
  }

  /**
   * Method to identify an option group value. For identification
   * two methods are offerd
   * standard is name
   * however, when the key parameter is set, id is also posible
   *
   *
   * @param string $name
   * @param int $optionGroupId
   * @return array|boolean
   */
  public function getWithKeyAndOptionGroupId() {
    if($this->_key=='name') {
      $params = array(
        'name' => $this->_apiParams['name'],
        'option_group_id' =>  $this->_apiParams['option_group_id']
      );
    } elseif($this->_key=='value'){
      $params= array(
        'value'=>$this->_apiParams['value'],
        'option_group_id'=> $this->_apiParams['option_group_id']
      );
    }
    try {
      return civicrm_api3('OptionValue', 'Getsingle', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      return array();
    }
  }
}