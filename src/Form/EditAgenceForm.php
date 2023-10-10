<?php 

namespace Drupal\civicrm_webform_phenix\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
class EditAgenceForm extends FormBase {
  public function getFormId() {
    return 'custom-popup';
  }

   /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
  }
  
  /**
   * Constructs a new YourCustomForm object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(\Drupal\Core\Messenger\MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }
  
  public function buildForm(array $form, FormStateInterface $form_state) {
    $custom_service = \Drupal::service('civicrm_webform_phenix.webform');
    $form['name_agence'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nom de l\'agence'),
      '#required' => TRUE,
      '#wrapper_attributes' => ['class' => ['d-inline-50']]
    ];
    $form['email_agence'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#wrapper_attributes' => ['class' => ['d-inline-50']]
    ];
    $form['detail'] = [
      '#type' => 'details',
      '#title' => $this->t('Adresse'),
      '#open'  => True,
    ];

    $form['detail']['street_agence'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Rue'),
      '#required' => TRUE,
      '#wrapper_attributes' => ['class' => ['d-inlines']]
    ];

    $form['detail']['postal_code_agence'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Code postal'),
      '#required' => TRUE,
      '#wrapper_attributes' => ['class' => ['d-inlines']]
    ];

    $form['detail']['city_agence'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ville'),
      '#required' => TRUE,
      '#wrapper_attributes' => ['class' => ['d-inlines']]
    ];

    $form['detail']['country_agence'] = [
      '#type' => 'select',
      '#title' => $this->t('Pays'),
      '#options' => $custom_service->allCountries(),
      '#wrapper_attributes' => ['class' => ['d-inlines']]
    ];

    $form['current_agence_id'] = [
      '#type' => 'textfield',
      '#title' => 'agence id',
      '#wrapper_attributes' => ['class' => ['hide']]
    ];

    $form['#attributes']['class'] = 'custom-popup hide';


    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Enregistrer'),
    ];

    // $contacts = \Civi\Api4\Contact::get(TRUE)
    // ->addSelect('id', 'contact_type', 'contact_sub_type')
    // ->addWhere('display_name', '=', 'nnnnn')
    // ->execute()->first();
    // dump($contacts);
    return $form;
  }

  /**
   * 
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $custom_service = \Drupal::service('civicrm_webform_phenix.webform');
    $agenceName = $form_state->getValue('name_agence');
    $street = $form_state->getValue('street_agence');
    $city = $form_state->getValue('city_agence');
    $email = $form_state->getValue('email_agence');
    $country = $form_state->getValue('country_agence');
    $current_agence_id = $form_state->getValue('current_agence_id');
    $postal_code_agence = $form_state->getValue('postal_code_agence');
    $cid = \Drupal::service('session')->get('current_contact_id');
    $activite_subject = "Annuaire - Mise à jour par l'adhérent " . $custom_service->getOrganizationName($cid);
    $allAdress['street'] = $street;  
    $allAdress['postal_code'] = $postal_code_agence;  
    $allAdress['city'] = $city;  
    $allAdress['country'] = $country;  
    // $custom_service->createActivity($this->getCid(), $activite_subject, $all_activity);
    $this->updateAdress($allAdress, $current_agence_id);
    $this->updateMail($email, $current_agence_id);
    // $cidCreated = $this->getCreatedContactId($agenceName);
    // $this->createEmailPrimary($cidCreated, $email);
    // $this->createRelationAgenceSiege($cid, $cidCreated);
    // $this->createAdress ($cidCreated, $street, $city, $country);

     // redirection
     $url = "/civicrm/verifie-agence-liste#?id=" . $cid . "&token=" . $custom_service->encryptString($cid) . "";dump($cid);
     $response = new \Symfony\Component\HttpFoundation\RedirectResponse($url);
     return $response->send();
  }

  private function updateAdress ($allAdress, $cid) {
    return \Civi\Api4\Address::update(FALSE)
      ->addValue('street_address', $allAdress['street'])
      ->addValue('city', $allAdress['city'])
      ->addValue('country_id', $allAdress['country'])
      ->addValue('postal_code', $allAdress['postal_code'])
      ->addWhere('contact_id', '=', $cid)
      ->execute();
  }


  private function updateMail($email, $cid) {

    $emails = \Civi\Api4\Email::get(FALSE)
    ->addSelect('email')
    ->addWhere('contact_id', '=', $cid)
    ->execute()->first()['email'];

    if ($emails) {
      return \Civi\Api4\Email::update(FALSE)
      ->addValue('email', $email)
      ->addValue('is_primary', TRUE)
      ->addWhere('contact_id', '=', $cid)
      ->execute();
    }
    
    return \Civi\Api4\Email::create(FALSE)
    ->addValue('contact_id', $cid)
    ->addValue('email', $email)
    ->addValue('is_primary', TRUE)
    ->execute();
  }
  
  private function getCid () {
    $custom_service = \Drupal::service('civicrm_webform_phenix.webform');
    $current_user = \Drupal::currentUser();
    $userEmail = $current_user->getEmail(); // Get the user's ID.
    return  $custom_service->getContactIdByEmail($userEmail);
    
  }

  private function createAdress ($cidAgence, $street, $city, $country = 1076) {
    $results = \Civi\Api4\Address::create(FALSE)
      ->addValue('contact_id', $cidAgence)
      ->addValue('is_primary', TRUE)
      ->addValue('street_address', $street)
      ->addValue('city', $city)
      ->addValue('country_id', $country)
      ->execute();
  }

  private function createEmailPrimary($cid, $email) {
    $results = \Civi\Api4\Email::create(FALSE)
      ->addValue('contact_id', $cid)
      ->addValue('email', $email)
      ->addValue('is_primary', TRUE)
      ->execute();
  }

  private function createRelationAgenceSiege ($contactIdSiege, $contactIdAgence) {
    return \Civi\Api4\Relationship::create(FALSE)
    ->addValue('contact_id_a', $contactIdAgence)
    ->addValue('contact_id_b', $contactIdSiege)
    ->addValue('relationship_type_id', 32)
    ->execute();
  }

  private function createContact ($agenceName) {
    return  \Civi\Api4\Contact::create(FALSE)
      ->addValue('contact_type', 'Organization')
      ->addValue('display_name', $agenceName)
      ->addValue('organization_name', $agenceName)
      ->addValue('contact_sub_type', [
        'Agence',
      ])
      ->execute();
  }

  private function getCreatedContactId($name) {
    return  \Civi\Api4\Contact::get(FALSE)
    ->addSelect('id')
    ->addWhere('display_name', '=', $name)
    ->addWhere('contact_sub_type', '=', 'Agence')
    ->execute()->first()['id'];
  }
}
