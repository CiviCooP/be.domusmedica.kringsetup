<?php
/**
 * Class for OptionValue configuration
 *
 * @author Klaas Eikelboom (CiviCooP) <klaas.eikelboom@civicoop.org>
 * @date 15 May 2018
 * @license AGPL-3.0
 */

class CRM_Kringsetup_Utils {

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
   * Utility function to add replacement words (these are pretty simple so
   * an seperate object is overkill.)
   *
   * @param $findWord
   * @param $replaceWord
   *
   * @throws \CiviCRM_API3_Exception
   */
  public static function addReplaceWord($findWord, $replaceWord) {
    $apiParams = [
      'find_word' => $findWord,
      'replace_word' => $replaceWord,
    ];

    try {
      $result = civicrm_api3('WordReplacement', 'getsingle', [
        'find_word' => $findWord,
      ]);
      $apiParams['id'] = $result['id'];
    } catch (CiviCRM_API3_Exception $ex) {

    }
    civicrm_api3('WordReplacement', 'create', $apiParams);
  }

}