<?php
use CRM_Kringsetup_ExtensionUtil as E;
/**
 * Class following Singleton pattern o create or update configuration items from
 * JSON files in resources folder (inspired by Erik Hommel)
 *
 * @author Klaas Eikelboom (CiviCooP) <klaas.eikelboom@civicoop.org>
 * @date 31 May 2017
 * @license AGPL-3.0
 */
class CRM_Kringsetup_ConfigItems_ConfigItems
{

  protected $_resourcesPath;
  protected $_customDataDir;

  /**
   * CRM_Kringsetup_ConfigItems_ConfigItems constructor.
   */
  function __construct() {
    // Get the directory of the extension based on the name.
    $container = CRM_Extension_System::singleton()->getFullContainer();
    $resourcesPath = $container->getPath(E::LONG_NAME) . '/CRM/Kringsetup/ConfigItems/resources/';
    if (!is_dir($resourcesPath) || !file_exists($resourcesPath)) {
      throw new Exception(ts('Could not find the folder ' . $resourcesPath
        . ' which is required for extension ' . E::LONG_NAME . ' in ' . __METHOD__
        . '.It does not exist or is not a folder, contact your system administrator'));
    }
    $customDataDir = $resourcesPath . 'custom_groups/';
    if (!is_dir($customDataDir) || !file_exists($customDataDir)) {
      throw new Exception(ts('Could not find the folder ' . $customDataDir
        . ' which is required for extension ' . E::LONG_NAME . ' in ' . __METHOD__
        . '.It does not exist or is not a folder, contact your system administrator'));
    }
    $this->_resourcesPath = $resourcesPath;
    $this->_customDataDir = $customDataDir;
  }

  function install(){

   /* $this->setContactTypes();
    $this->setRelationshipTypes();
    $this->setMembershipTypes();
    $this->setOptionGroups();
    $this->setGroups();*/
   $this->setActivityTypes();
    // customData as last one because it might need one of the previous ones (option group, relationship types, activity types)
    //$this->setCustomData();
  }

  /**
   * Method to create option groups
   *
   * @throws Exception when resource file not found
   * @access protected
   */
  protected function setOptionGroups()
  {
    $jsonFile = $this->_resourcesPath . 'option_groups.json';
    if (!file_exists($jsonFile)) {
      throw new Exception(ts('Could not load option_groups configuration file for extension,
      contact your system administrator!'));
    }
    $optionGroupsJson = file_get_contents($jsonFile);
    $optionGroups = json_decode($optionGroupsJson, true);
    foreach ($optionGroups as $name => $optionGroupParams) {
      $optionGroup = new CRM_Kringsetup_ConfigItems_OptionGroup();
      $optionGroup->create($optionGroupParams);
    }
  }

  /**
   * Method to create activity types
   *
   * @throws Exception when resource file not found
   * @access protected
   */
  protected function setActivityTypes()
  {
    $jsonFile = $this->_resourcesPath . 'activity_types.json';
    if (!file_exists($jsonFile)) {
      throw new Exception(ts('Could not load activity_types configuration file for extension,
      activity your system administrator!'));
    }
    $activityTypesJson = file_get_contents($jsonFile);
    $activityTypes = json_decode($activityTypesJson, true);
    foreach ($activityTypes as $name => $activityTypeParams) {
      $activityType = new CRM_Kringsetup_ConfigItems_ActivityType();
      $activityType->create($activityTypeParams);
    }
  }

  /**
   * Method to create contact types
   *
   * @throws Exception when resource file not found
   * @access protected
   */
  protected function setContactTypes()
  {
    $jsonFile = $this->_resourcesPath . 'contact_types.json';
    if (!file_exists($jsonFile)) {
      throw new Exception(ts('Could not load contact_types configuration file for extension,
      contact your system administrator!'));
    }
    $contactTypesJson = file_get_contents($jsonFile);
    $contactTypes = json_decode($contactTypesJson, true);
    foreach ($contactTypes as $name => $contactTypeParams) {
      $contactType = new CRM_Kringsetup_ConfigItems_ContactType();
      $contactType->create($contactTypeParams);
    }
  }

  /**
   * Method to create relationship types
   *
   * @throws Exception when resource file not found
   * @access protected
   */
  protected function setRelationshipTypes()
  {
    $jsonFile = $this->_resourcesPath . 'relationship_types.json';
    if (!file_exists($jsonFile)) {
      throw new Exception(ts('Could not load relationship_types configuration file for extension, 
            contact your system administrator!'));
    }
    $relationshipTypesJson = file_get_contents($jsonFile);
    $relationshipTypes = json_decode($relationshipTypesJson, true);
    $relationshipType = new CRM_Kringsetup_ConfigItems_RelationshipType();

    foreach ($relationshipTypes as $name => $relationshipTypeParams) {
      $relationshipType->create($relationshipTypeParams);
    }

    // disable core relationship types that are not required
    $relationshipType->disableRelationshipType("Child of");
    $relationshipType->disableRelationshipType("Spouse of");
    $relationshipType->disableRelationshipType("Sibling of");
    $relationshipType->disableRelationshipType("Volunteer for");
    $relationshipType->disableRelationshipType("Head of Household for");
    $relationshipType->disableRelationshipType("Household Member of");
    $relationshipType->disableRelationshipType("Homeless Services Coordinator is");
    $relationshipType->disableRelationshipType("Health Services Coordinator is");
    $relationshipType->disableRelationshipType("Senior Services Coordinator is");
    $relationshipType->disableRelationshipType("Benefits Specialist is");
  }

  /**
   * Method to create membership types
   *
   * @throws Exception when resource file not found
   * @access protected
   */
  protected function setMembershipTypes()
  {
    $jsonFile = $this->_resourcesPath . 'membership_types.json';
    if (!file_exists($jsonFile)) {
      throw new Exception(ts('Could not load member_types configuration file for extension,
      contact your system administrator!'));
    }
    $membershipTypesJson = file_get_contents($jsonFile);
    $membershipTypes = json_decode($membershipTypesJson, true);
    foreach ($membershipTypes as $name => $membershipTypeParams) {
      $membershipType = new CRM_Kringsetup_ConfigItems_MembershipType();
      $membershipType->create($membershipTypeParams);
    }
  }


  protected function setGroups()
  {
    $jsonFile = $this->_resourcesPath . 'groups.json';
    if (!file_exists($jsonFile)) {
      throw new Exception(ts('Could not load groups configuration file for extension,
      contact your system administrator!'));
    }
    $groupsJson = file_get_contents($jsonFile);
    $groups = json_decode($groupsJson, true);
    foreach ($groups as $name => $groupParams) {
      $group = new CRM_Kringsetup_ConfigItems_Group();
      $group->create($groupParams);
    }
  }

  /**
   * Method to set the custom data groups and fields
   *
   * @throws Exception when config json could not be loaded
   * @access protected
   */
  protected function setCustomData()
  {
    // read all json files from custom_groups dir
    $customDataPath = $this->_resourcesPath . 'custom_groups';
    if (file_exists($customDataPath) && is_dir($customDataPath)) {
      // get all json files from dir
      $jsonFiles = glob($customDataPath . DIRECTORY_SEPARATOR . "*.json");
      foreach ($jsonFiles as $customDataFile) {
        $customDataJson = file_get_contents($customDataFile);
        $customData = json_decode($customDataJson, true);
        foreach ($customData as $customGroupName => $customGroupData) {
          $customGroup = new CRM_Kringsetup_ConfigItems_CustomGroup();
          $created = $customGroup->create($customGroupData);
          foreach ($customGroupData['fields'] as $customFieldName => $customFieldData) {
            $customFieldData['custom_group_id'] = $created['id'];
            $customField = new CRM_Kringsetup_ConfigItems_CustomField();
            $customField->create($customFieldData);
          }
          // remove custom fields that are still on install but no longer in config
          CRM_Kringsetup_ConfigItems_CustomField::removeUnwantedCustomFields($created['id'], $customGroupData);
        }
      }
    }
  }

  /**
   * Method to disable configuration items
   */
  public function disable()
  {
    $this->disableCustomData();
    $this->disableOptionGroups();
    $this->disableContactTypes();

  }

  /**
   * Method to enable configuration items
   */
  public function enable()
  {
    $this->enableCustomData();
    $this->enableOptionGroups();
    $this->enableContactTypes();

  }

  /**
   * Method to uninstall configuration items
   */
  public function uninstall()
  {
    $this->uninstallCustomData();
    $this->uninstallOptionGroups();
    $this->uninstallContactTypes();
  }

  /**
   * Method to uninstall custom data
   */
  private function uninstallCustomData()
  {
    // read all json files from custom_groups dir
    $container = CRM_Extension_System::singleton()->getFullContainer();
    $customDataPath = $container->getPath(E::LONG_NAME) . '/CRM/Kringsetup/ConfigItems/resources/custom_groups';
    if (file_exists($customDataPath) && is_dir($customDataPath)) {
      // get all json files from dir
      $jsonFiles = glob($customDataPath . DIRECTORY_SEPARATOR . "*.json");
      foreach ($jsonFiles as $customDataFile) {
        $customDataJson = file_get_contents($customDataFile);
        $customData = json_decode($customDataJson, true);
        foreach ($customData as $customGroupName => $customGroupData) {
          $customGroup = new CRM_Kringsetup_ConfigItems_CustomGroup();
          $customGroup->uninstall($customGroupName);
        }
      }
    }
  }

  /**
   * Method to enable custom data
   */
  private function enableCustomData()
  {
    // read all json files from custom_groups dir
    $customDataPath = $this->_customDataDir;
    if (file_exists($customDataPath) && is_dir($customDataPath)) {
      // get all json files from dir
      $jsonFiles = glob($customDataPath . DIRECTORY_SEPARATOR . "*.json");
      foreach ($jsonFiles as $customDataFile) {
        $customDataJson = file_get_contents($customDataFile);
        $customData = json_decode($customDataJson, true);
        foreach ($customData as $customGroupName => $customGroupData) {
          $customGroup = new CRM_Kringsetup_ConfigItems_OptionGroup();
          $customGroup->enable($customGroupName);
        }
      }
    }
  }

  /**
   * Method to disable custom data
   */
  private function disableCustomData()
  {
    // read all json files from custom_groups dir

    $customDataPath = $this->_customDataDir;
    if (file_exists($customDataPath) && is_dir($customDataPath)) {
      // get all json files from dir
      $jsonFiles = glob($customDataPath . DIRECTORY_SEPARATOR . "*.json");
      foreach ($jsonFiles as $customDataFile) {
        $customDataJson = file_get_contents($customDataFile);
        $customData = json_decode($customDataJson, true);
        foreach ($customData as $customGroupName => $customGroupData) {
          $customGroup = new CRM_Kringsetup_ConfigItems_CustomGroup();
          $customGroup->disable($customGroupName);
        }
      }
    }
  }

  /**
   * Method to disable option groups
   */
  private function disableOptionGroups()
  {
    $resourcePath = $this->_resourcesPath;
    $jsonFile = $resourcePath . 'option_groups.json';
    if (file_exists($jsonFile)) {
      $optionGroupsJson = file_get_contents($jsonFile);
      $optionGroups = json_decode($optionGroupsJson, true);
      foreach ($optionGroups as $name => $optionGroupParams) {
        $optionGroup = new CRM_Kringsetup_ConfigItems_OptionGroup();
        $optionGroup->disable($name);
      }
    }
  }

  /**
   * Method to disable contact types
   */
  private function disableContactTypes()
  {
    // read all json files from dir
    $resourcePath = $this->_resourcesPath;
    $jsonFile = $resourcePath . 'contact_types.json';
    if (file_exists($jsonFile)) {
      $contactTypesJson = file_get_contents($jsonFile);
      $contactTypes = json_decode($contactTypesJson, true);
      foreach ($contactTypes as $name => $contactTypeParams) {
        $contactType = new CRM_Kringsetup_ConfigItems_ContactType();
        $contactType->disableContactType($name);
      }
    }
  }

  /**
   * Method to enable contact types
   */
  private function enableContactTypes()
  {
    $resourcePath = $this->_resourcesPath;
    $jsonFile = $resourcePath . 'contact_types.json';
    if (file_exists($jsonFile)) {
      $contactTypesJson = file_get_contents($jsonFile);
      $contactTypes = json_decode($contactTypesJson, true);
      foreach ($contactTypes as $name => $contactTypeParams) {
        $contactType = new CRM_Kringsetup_ConfigItems_ContactType();
        $contactType->enableContactType($name);
      }
    }
  }

  /**
   * Method to uninstall contact types
   */
  private function uninstallContactTypes()
  {
    // read all json files from dir
    $resourcePath = $this->_resourcesPath;
    $jsonFile = $resourcePath . 'contact_types.json';
    if (file_exists($jsonFile)) {
      $contactTypesJson = file_get_contents($jsonFile);
      $contactTypes = json_decode($contactTypesJson, true);
      foreach ($contactTypes as $name => $contactTypeParams) {
        $contactType = new CRM_Kringsetup_ConfigItems_ContactType();
        $contactType->uninstallContactType($name);
      }
    }
  }

  /**
   * Method to enable option groups
   */
  private function enableOptionGroups()
  {
    $jsonFile = $this->_resourcePath . 'option_groups.json';
    if (file_exists($jsonFile)) {
      $optionGroupsJson = file_get_contents($jsonFile);
      $optionGroups = json_decode($optionGroupsJson, true);
      foreach ($optionGroups as $name => $optionGroupParams) {
        $optionGroup = new CRM_Kringsetup_ConfigItems_OptionGroup();
        $optionGroup->enable($name);
      }
    }
  }

  /**
   * Method to uninstall option groups
   */
  private function uninstallOptionGroups()
  {
    $jsonFile = $this->_resourcePath . 'option_groups.json';
    if (file_exists($jsonFile)) {
      $optionGroupsJson = file_get_contents($jsonFile);
      $optionGroups = json_decode($optionGroupsJson, true);
      foreach ($optionGroups as $name => $optionGroupParams) {
        $optionGroup = new CRM_Kringsetup_ConfigItems_OptionGroup();
        $optionGroup->uninstall($name);
      }
    }
  }
}