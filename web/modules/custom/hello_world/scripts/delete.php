<?php

/**
 * @file
 * An example of DELETE query.
 */

/**
 * The database service.
 *
 * @var \Drupal\Core\Database\Connection $database
 */
$database = \Drupal::database();
$result = $database->delete('book')->execute();

var_dump($result);
