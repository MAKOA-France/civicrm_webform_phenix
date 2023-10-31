<?php 

namespace Drupal\civicrm_webform_phenix\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
class AddNewAgenceForm extends FormBase {
  public function getFormId() {
    return 'mon_module_form';
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
    
    $cid = \Drupal::service('session')->get('current_contact_id');
    $custom_service = \Drupal::service('civicrm_webform_phenix.webform');
    $form['Title']['#markup'] = '<h1 class="add-new-agenceform">Ajouter une agence</h1>';
    

        
    $default_name = '';
    
    $contacts = \Civi\Api4\Contact::get(FALSE)
    ->addSelect('display_name')
    ->addWhere('id', '=', $cid)
    ->execute()->first();
    
    $default_name = $contacts ? $contacts['display_name'] : '';

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nom de l\'agence'),
      '#required' => TRUE,
      '#wrapper_attributes' => ['class' => ['d-inline-50']],
      '#attributes' => ['readonly'=> 'readonly'],
      '#value' => $default_name
    ];
   
    $form['detail'] = [
      '#type' => 'details',
      '#title' => $this->t('Adresse'),
      '#open'  => True,
    ];

    $form['detail']['street'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Rue'),
      '#wrapper_attributes' => ['class' => ['d-inlines']]
    ];

    $form['detail']['postal_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Code postal'),
      '#wrapper_attributes' => ['class' => ['d-inlines']]
    ];

    $form['detail']['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ville'),
      '#wrapper_attributes' => ['class' => ['d-inlines']]
    ];

    $form['detail']['country'] = [
      '#type' => 'select',
      '#title' => $this->t('Pays'),
      '#options' => $custom_service->allCountries(),
      '#wrapper_attributes' => ['class' => ['d-inlines']],
      '#default_value' => 1076
    ];

    $form['detail']['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Téléphone'),
      '#wrapper_attributes' => ['class' => ['d-inlines']],
      '#attributes' => ['maxlength' => 14]
    ];

    $form['detail']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#wrapper_attributes' => ['class' => ['d-inline-50']]
    ];

    

    \Drupal::service('session')->set('current_contact_id', $cid);

    $form['contact_id_hidden'] = [
      '#type' => 'textfield',
      '#title' => 'id contact',
      '#wrapper_attributes' => ['class' => ['d-inlines hide']],
      '#attributes' => ['class' => ['id_contact_hidden']]
    ];

    $referrer_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $exploded  = explode('cid=', $referrer_url);
    $exploded_again = explode('?3FaddressId', $exploded[1]);
    $contact_id = $exploded_again[0];

    
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Enregistrer'),
      '#attributes' => [
        'data-contact-id' => $contact_id,
        'class' => ['page-list-agence']
      ],
      
    ];

    // dump(\Drupal::request()->get('extra'));
    return $form;
  }

  /**
   * 
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $custom_service = \Drupal::service('civicrm_webform_phenix.webform');
    $agenceName = $form_state->getValue('name');
    $street = $form_state->getValue('street');
    $city = $form_state->getValue('city');
    $email = $form_state->getValue('email');
    $phone = $form_state->getValue('phone');
    $phone = implode(" ", str_split($phone, 2));
    $country = $form_state->getValue('country');
    $postal_code = $form_state->getValue('postal_code');
    $contact_id_hidden = $form_state->getValue('contact_id_hidden');

    // Retrieve the session variable
    $sessionValue = \Drupal::service('session')->get('current_contact_id');

    $cid = $contact_id_hidden;
    $activite_subject = "Annuaire - Mise à jour par l'adhérent " . $custom_service->getOrganizationName($cid);
    $all_activity['Agence'] = ' Nom agence :  ' . $agenceName . '<br>';  
    $all_activity['Agence'] .= ' Rue : ' . $street. '<br>';  
    $all_activity['Agence'] .= ' Ville :  ' . $city. '<br>';  
    $all_activity['Agence'] .= ' Email :  ' . $email. '<br>';  
    $all_activity['Agence'] .= ' Phone :  ' . $phone. '<br>';  
    // $custom_service->createActivity($this->getCid(), $activite_subject, $all_activity);

    // $this->createContact($agenceName . ' agence');

    $createdContact = $this->createContact($agenceName);
    $cidCreated = $createdContact->first()['id'];
    // $cidCreated = $this->getCreatedContactId($agenceName);
    $this->createEmailPrimary($cidCreated, $email);
    $this->createRelationAgenceSiege($cid, $cidCreated);
    if ($phone) {
      $custom_service->createPhonePrimary($cidCreated, $phone);
    }
    $this->createAdress ($cidCreated, $street, $city, $country, $postal_code);


    $this->messenger->addMessage("L'agence $agenceName a été bien créée.");


    $gettedChecksum = $custom_service->getChecksumBiCid($cid);
    // redirection
    $url = "/civicrm/verifie-agence-liste#?id=" . $cid . "&token=" . $gettedChecksum . "";
    $response = new \Symfony\Component\HttpFoundation\RedirectResponse($url);
    return $response->send();
  }
  
  private function getCid ($form) {
    $custom_service = \Drupal::service('civicrm_webform_phenix.webform');
    $current_user = \Drupal::currentUser();
    $userEmail = $current_user->getEmail(); // Get the user's ID.
    return  $form['submit']['#attributes']['data-contact-id'];
    
  }

  private function createAdress ($cidAgence, $street, $city, $country = 1076, $postal_code) {
    $results = \Civi\Api4\Address::create(FALSE)
      ->addValue('contact_id', $cidAgence)
      ->addValue('is_primary', TRUE)
      ->addValue('street_address', $street)
      ->addValue('city', $city)
      ->addValue('postal_code', $postal_code)
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
      ->addValue('relationship_type_id', 32)
      ->addValue('contact_id_a', $contactIdAgence)
      ->addValue('contact_id_b', $contactIdSiege)
      ->execute();
  }

  private function createContact ($agenceName) {
    return  \Civi\Api4\Contact::create(FALSE)
      ->addValue('contact_type', 'Organization')
      ->addValue('display_name', $agenceName)
      ->addValue('organization_name', $agenceName)
      ->addValue('org_annuaireenligne.annuaireenligne_DLR', 1)
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
