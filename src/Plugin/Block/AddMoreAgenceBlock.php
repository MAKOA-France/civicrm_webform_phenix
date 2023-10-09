<?php

namespace Drupal\civicrm_webform_phenix\Plugin\Block;

use Drupal\node\Entity\Node;
use \Drupal\node\NodeInterface;
use Drupal\Core\Block\BlockBase;
/**
 * Provides a 'Back button' block.
 *
 * @Block(
 *  id = "add_agence_block",
 *  admin_label = @Translation("Custom Add Agence Block"),
 *  category = @Translation("Custom Add Agence Block"),
 *  context_definitions = {
 *  }
 * )
 */
class AddMoreAgenceBlock  extends BlockBase  {

  /**
   * {@inheritdoc}
   */
  public function build() {
    \Drupal::service('cache.render')->invalidateAll();
    
    $html = '<a href="' . \Drupal::request()->server->get('HTTP_REFERER') . '" class="button button-go-back js-form-submit form-submit">Retour</a>';
    $path = $_SERVER['REQUEST_URI'];

    
    // Add your custom form to the block.
    $form = \Drupal::formBuilder()->getForm('Drupal\civicrm_webform_phenix\Form\AddNewAgenceForm');
    return $form;
    // return [
    //   '#markup' => $form,
    //   '#cache' => ['max-age' => 0],
    // ];
  }
 

}
