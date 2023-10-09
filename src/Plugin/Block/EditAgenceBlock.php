<?php

namespace Drupal\civicrm_webform_phenix\Plugin\Block;

use Drupal\node\Entity\Node;
use \Drupal\node\NodeInterface;
use Drupal\Core\Block\BlockBase;
/**
 * Provides a 'Edit Agence' block.
 *
 * @Block(
 *  id = "edit_agence_block",
 *  admin_label = @Translation("Custom Edit Agence Block"),
 *  category = @Translation("Custom Edit Agence Block"),
 *  context_definitions = {
 *  }
 * )
 */
class EditAgenceBlock  extends BlockBase  {

  /**
   * {@inheritdoc}
   */
  public function build() {
    \Drupal::service('cache.render')->invalidateAll();
    

    // Add your custom form to the block.
    $form = \Drupal::formBuilder()->getForm('Drupal\civicrm_webform_phenix\Form\EditAgenceForm');
    return $form;
  }
 

}
