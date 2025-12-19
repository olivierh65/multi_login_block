<?php

namespace Drupal\multi_login_block\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\block\Entity\Block;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for Multi Login Block admin page.
 */
class AdminController extends ControllerBase {

  /**
   * Admin page callback.
   */
  public function index() {
    // Find the first Multi Login Block instance.
    $block_ids = \Drupal::entityQuery('block')
      ->condition('plugin', 'multi_login_block')
      ->accessCheck(FALSE)
      ->execute();

    if (empty($block_ids)) {
      throw new NotFoundHttpException('Multi Login Block not found. Please place the block first.');
    }

    $block_id = reset($block_ids);
    $block = Block::load($block_id);

    // Get the block form.
    $block_form = $this->entityTypeManager()->getFormObject('block', 'default');
    $block_form->setEntity($block);

    return $this->formBuilder()->getForm($block_form);
  }

}
