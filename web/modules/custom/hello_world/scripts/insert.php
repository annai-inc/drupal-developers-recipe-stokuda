<?php

/**
 * @file
 * An example of INSERT query.
 */

/**
 * The database service.
 *
 * @var \Drupal\Core\Database\Connection $database
 */
$database = \Drupal::database();
$id = $database->insert('book')
  ->fields(['name' => 'book 1', 'description' => 'An awesome book'])
  ->execute();

var_dump($id);

$database = \Drupal::database();
$id = $database->insert('book')
  ->fields(['name' => 'book 2', 'description' => 'A wonderful book'])
  ->execute();

var_dump($id);
