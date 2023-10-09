<?php

namespace Drupal\civicrm_webform_phenix\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Language\LanguageManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\webform\Entity\Webform;

/**
 * Defines GetDetailController class.
 */
class ValidateWithoutModificationController extends ControllerBase
{

  public function validate() {

    $custom_service = \Drupal::service('civicrm_webform_phenix.webform');
    \Drupal::service('civicrm')->initialize();
    $req = \Drupal::request();
    $getCid = $req->get('getCid');
    $getCid = explode('?', $getCid)[0];
    $activite_subject = "Annuaire - Mise à jour par l'adhérent " . $custom_service->getOrganizationName($getCid);

    $all_activity = ['Modification' => 'Validé par l\'adherent sans modification'];
    $custom_service->createActivity($getCid, $activite_subject, $all_activity);
    return new Response($custom_service->confirmationPage());
  }
}
