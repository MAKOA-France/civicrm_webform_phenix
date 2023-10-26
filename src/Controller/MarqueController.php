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
class MarqueController extends ControllerBase
{

  public function getAllMarque() {

    $results = [];
    $req = \Drupal::request();
    $get_referer = $req->server->get('HTTP_REFERER');
    $custom_service = \Drupal::service('civicrm_webform_phenix.webform');
    $cid = explode('cid=', $get_referer);
    $cid = $cid[1];
    \Drupal::service('civicrm')->initialize();
    $marquees = $custom_service->getAllMarques();
    $filter = $req->query->get('q');
    if ($marquees) {
      foreach ($marquees as $marque_key => $marque_value) {
        if (stripos($marque_value, $filter) !== false) {
          $results[] = [
              'label' => $marque_value,
              'value' => $marque_key,
              'data-val' => $marque_key,
              'alt' => $marque_key,
          ];
        }
      }
    }
    

    /* return [
      // 3984 => 'EUROFOR',
        3985 => 'KRAMER'
        ]
        ; */
        
        // Load a webform by its ID.
        
        /* $ request = \Drupal::request();
        $custom_service = \Drupal::service('civicrm_webform_phenix.view_services');
        $subFamilys = $custom_service->sousFamille();
        $sorted = asort($subFamilys);
        
        $input = $request->query->get('q');
        foreach ($subFamilys as $key => $subFamily) {
          if (stripos($subFamily, $input) !== false) {
            $results[] = [
              'value' => $subFamily . '(' . $key . ')',
              'label' => $subFamily,
              'data-val' => $key,
            ];
          }
    } */



    return new JsonResponse($results);
  }

  
  public function backToForm() {
    $req = \Drupal::request();
    $getId = $req->query->get('id');
    $custom_service = \Drupal::service('civicrm_webform_phenix.webform');
    $getId = $getId ? $getId : \Drupal::service('session')->get('current_contact_id');
    $custom_service = \Drupal::service('civicrm_webform_phenix.webform');

    // $hashContactViaDatabase = $custom_service->checkIfHashContactIsGood($getId);
    
    // $urlBackLink = '/form/formulaire-pour-adherent?cid=' . $getId . '?&token=' . $hashContactViaDatabase;

    \Drupal::service('civicrm')->initialize();
    $gettedChecksum = $custom_service->getChecksumBiCid($getId);
    $urlVerifyAgence = '<a href="/civicrm/verifie-agence-liste#?id=' . $getId . '&token=' . $gettedChecksum . '" class="button btn-blue">Vérifier les agences</a>';
    $html_verify = '<div class="verify-agence"><p class="see-all-agence">Vos informations sont enregistrées. Merci de bien vouloir vérifier la liste de vos agences.</p>  ' . $urlVerifyAgence . '   </div>';
    return new JsonResponse([/* 'back_link' => $urlBackLink, */ 'verify_agence' => $html_verify, 'btn_verify' => $urlVerifyAgence]);
  }


}
