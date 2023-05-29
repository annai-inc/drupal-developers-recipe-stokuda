<?php

namespace Drupal\hello_world\Service;

use Drupal\Core\Session\AccountInterface;
use Drupal\hello_world\EchoMessageServiceInterface;
use Drupal\node\NodeInterface;

class NullMessenger implements EchoMessageServiceInterface {

  public function helloWorld() {
    return Null;
  }

  public function saySomething(string $message) {
    return Null;
  }

  public function inspectUser(AccountInterface $user) {
    return Null;
  }

  public function inspectNode(NodeInterface $node) {
    return Null;
  }

}
