<?php

/**
 * @file
 * An example of UPDATE query.
 */

/**
 * The database service.
 *
 * @var \Drupal\Core\Database\Connection $database
 */
$database = \Drupal::database();
$transaction = $database->startTransaction();
try {
  $result = $database->update('book')
    ->fields(['description' => 'The awesome book'])
    ->condition('description', '%' . $database->escapeLike('awesome') . '%', 'LIKE')
    ->execute();

  var_dump($result);

  $raise_exception = $_SERVER['argv'][3];
  if (isset($raise_exception) && is_string($raise_exception) && $raise_exception == "raise-exception"){
    throw new Exception("error");
  }

  $database = \Drupal::database();
  $result = $database->update('book')
    ->fields(['description' => 'The wonderful book'])
    ->condition('description', '%' . $database->escapeLike('wonderful') . '%', 'LIKE')
    ->execute();

  var_dump($result);
}
catch (Exception $e) {
  $transaction->rollBack();
}



