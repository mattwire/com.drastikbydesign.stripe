<?php

/**
 * Stripe Customer API
 *
 */

/**
 * Stripe.Customer_Contactids API
 *  This api will update the civicrm_stripe_customers table and add contact IDs for all known email addresses
 *
 * @param array $params
 * @see civicrm_api3_create_success
 *
 * @return array
 */
function civicrm_api3_stripe_customercontactids($params) {
  $dao = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_stripe_customers');
  $counts = [
    'updated' => 0,
    'failed' => 0,
  ];
  while ($dao->fetch()) {
    try {
      $contactId = civicrm_api3('Contact', 'getvalue', [
        'return' => "id",
        'email' => $dao->email,
      ]);
      CRM_Core_DAO::executeQuery("UPDATE `civicrm_stripe_customers` SET contact_id={$contactId} WHERE email='{$dao->email}'");
      $counts['updated']++;
    } catch (Exception $e) {
      Civi::log()
        ->debug('Stripe Upgrader: No contact ID found for stripe customer with email: ' . $dao->email);
      $counts['failed']++;
    }
  }
  return civicrm_api3_create_success($counts);
}
