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
class EditAgenceController extends ControllerBase
{

  public function editAgence() {

    $req = \Drupal::request();
    $get_referer = $req->server->get('HTTP_REFERER');
    $custom_service = \Drupal::service('civicrm_webform_phenix.webform');
    $cid = explode('cid=', $get_referer);
    $cid = $cid[1];
    \Drupal::service('civicrm')->initialize();

    $agencdId = $req->request->get('agenceId');

    $addresses = \Civi\Api4\Address::get(FALSE)
    ->addSelect('street_address', 'city', 'country_id', 'postal_code')
    ->addWhere('contact_id', '=', $agencdId)
    ->execute()->first();

    $emails = \Civi\Api4\Email::get(FALSE)
    ->addSelect('email')
    ->addWhere('contact_id', '=', $agencdId)
    ->execute()->first()['email'];

    $phones = \Civi\Api4\Phone::get(FALSE)
      ->addSelect('phone')
      ->addWhere('contact_id', '=', $agencdId)
      ->execute()->first()['phone'];


    $contactName = \Civi\Api4\Contact::get(FALSE)
      ->addSelect('display_name')
      ->addWhere('id', '=', $agencdId)
      ->execute()->first()['display_name'];


    return new JsonResponse(['address' => $addresses, 'mail' => $emails, 'name' => $contactName, 'phone' => $phones]);
  }

  public function checkToken () {
    $custom_service = \Drupal::service('civicrm_webform_phenix.webform');
    $req = \Drupal::request();
    $getToken = $req->request->get('token');
    $cid = $req->request->get('cid');
    \Drupal::service('civicrm')->initialize();
    $isChecksumValid = $custom_service->CustomizeValidateChecksum($cid, $getToken);
    if ($isChecksumValid) {
      return new JsonResponse(['cid' => $isChecksumValid]);
    }
    return new JsonResponse([]);
  }


  public function backToForm() {
    $req = \Drupal::request();
    $getId = $req->query->get('id');
    $custom_service = \Drupal::service('civicrm_webform_phenix.webform');
    $cryptedId = $custom_service->encryptString($getId);
    $addressId = $this->getAddressID($getId);
    
    $urlBackLink = '/form/formulaire-pour-adherent?cid=' . $getId . '?3FaddressId=' . $addressId . '&token=' . $cryptedId;
    return new Response($urlBackLink);
  }

  private function getAddressID ($cid) {
    $civicrm = \Drupal::service('civicrm');
    $civicrm->initialize();     

    $addresses = \Civi\Api4\Address::get(FALSE)
      ->addSelect('id')
      ->addWhere('contact_id', '=', $cid)
      ->execute()->column('id')[0];
    return $addresses;
  }
}
