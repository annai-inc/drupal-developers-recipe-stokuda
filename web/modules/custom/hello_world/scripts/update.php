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
$result = $database->update('book')
  ->fields(['description' => 'The awesome book'])
  ->condition('description', '%' . $database->escapeLike('awesome') . '%', 'LIKE')
  ->execute();

var_dump($result);

$database = \Drupal::database();
$result = $database->update('book')
  ->fields(['description' => 'The wonderful book'])
  ->condition('description', '%' . $database->escapeLike('wonderful') . '%', 'LIKE')
  ->execute();

var_dump($result);
