<?php

class CRM_Stripe_Customer {

  /**
   * Find an existing Stripe customer in the CiviCRM database
   *
   * @param $params
   *
   * @return null|string
   * @throws \CRM_Core_Exception
   */
  public static function find($params) {
    $requiredParams = ['is_live', 'processor_id'];
    foreach ($requiredParams as $required) {
      if (empty($required)) {
        throw new CRM_Core_Exception('Stripe Customer (find): Missing required parameter: ' . $required);
      }
    }
    if (empty($params['email']) && empty($params['contact_id'])) {
      throw new CRM_Core_Exception('Stripe Customer (find): One of email or contact_id is required');
    }
    $queryParams = [
      1 => [$params['contact_id'], 'String'],
      2 => [$params['is_live'], 'Boolean'],
      3 => [$params['processor_id'], 'Positive'],
    ];

    return CRM_Core_DAO::singleValueQuery("SELECT id
      FROM civicrm_stripe_customers
      WHERE contact_id = %1 AND is_live = %2 AND processor_id = %3", $queryParams);
  }

  /**
   * Add a new Stripe customer to the CiviCRM database
   *
   * @param $params
   *
   * @throws \CRM_Core_Exception
   */
  public static function add($params) {
    $requiredParams = ['contact_id', 'customer_id', 'is_live', 'processor_id'];
    foreach ($requiredParams as $required) {
      if (empty($required)) {
        throw new CRM_Core_Exception('Stripe Customer (add): Missing required parameter: ' . $required);
      }
    }

    $queryParams = [
      1 => [$params['contact_id'], 'String'],
      2 => [$params['customer_id'], 'String'],
      3 => [$params['is_live'], 'Boolean'],
      4 => [$params['processor_id'], 'Integer'],
    ];
    CRM_Core_DAO::executeQuery("INSERT INTO civicrm_stripe_customers
          (contact_id, id, is_live, processor_id) VALUES (%1, %2, %3, %3)", $queryParams);
  }

  public static function create($params, $paymentProcessor) {
    $params['processor_id'] = $paymentProcessor->_paymentProcessor['id'];
    $requiredParams = ['contact_id', 'card_token', 'is_live', 'processor_id'];
    // $optionalParams = ['email'];
    foreach ($requiredParams as $required) {
      if (empty($required)) {
        throw new CRM_Core_Exception('Stripe Customer (create): Missing required parameter: ' . $required);
      }
    }

    $contactDisplayName = civicrm_api3('Contact', 'getvalue', [
      'return' => ['display_name'],
      'id' => $params['contact_id'],
    ]);

    $sc_create_params = [
      'description' => $contactDisplayName . ' (CiviCRM)',
      'card' => $params['card_token'],
      'email' => CRM_Utils_Array::value('email', $params),
      'metadata' => ['civicrm_contact_id' => $params['contact_id']],
    ];

    $stripe_customer = $paymentProcessor->stripeCatchErrors('create_customer', $sc_create_params, $params);

    // Store the relationship between CiviCRM's email address for the Contact & Stripe's Customer ID.
    if (isset($stripe_customer)) {
      if ($paymentProcessor->isErrorReturn($stripe_customer)) {
        return $stripe_customer;
      }

      $params = [
        'contact_id' => $params['contact_id'],
        'customer_id' => $stripe_customer->id,
        'is_live' => $params['is_live'],
        'processor_id' => $params['processor_id'],
      ];
      self::add($params);
    }
    else {
      Throw new CRM_Core_Exception(ts('There was an error saving new customer within Stripe.'));
    }
    return $stripe_customer;
  }
  /**
   * Delete a Stripe customer from the CiviCRM database
   *
   * @param $params
   *
   * @throws \CRM_Core_Exception
   */
  public static function delete($params) {
    $requiredParams = ['contact_id', 'is_live', 'processor_id'];
    foreach ($requiredParams as $required) {
      if (empty($required)) {
        throw new CRM_Core_Exception('Stripe Customer (delete): Missing required parameter: ' . $required);
      }
    }

    $queryParams = [
      1 => [$params['contact_id'], 'String'],
      2 => [$params['is_live'], 'Boolean'],
      3 => [$params['processor_id'], 'Integer'],
    ];
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_stripe_customers
            WHERE contact_id = %1 AND is_live = %2 AND processor_id = %3", $queryParams);
  }

}
