<?php

namespace Drupal\multi_login_block\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\block\Entity\Block;

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
      return [
        '#markup' => '<p>' . $this->t('Multi Login Block is not yet placed. Go to <a href="@url">Structure > Block layout</a> to add it.', [
          '@url' => '/admin/structure/block',
        ]) . '</p>',
      ];
    }

    $block_id = reset($block_ids);
    $block = Block::load($block_id);

    if (!$block) {
      return [
        '#markup' => '<p>' . $this->t('Error loading Multi Login Block.') . '</p>',
      ];
    }

    // Get the block form.
    $block_form = $this->entityTypeManager()->getFormObject('block', 'default');
    $block_form->setEntity($block);

    return $this->formBuilder()->getForm($block_form);
  }

}
