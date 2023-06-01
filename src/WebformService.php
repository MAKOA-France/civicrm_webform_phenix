<?php


namespace Drupal\civicrm_webform_phenix;

use Drupal\Core\Session\AccountInterface;
use Drupal\media\Entity\Media;

/**
 * Class CustomService
 * @package Drupal\civicrm_webform_phenix\Services
 */
class WebformService {

  protected $currentUser;


  /**
   * CustomService constructor.
   * @param AccountInterface $currentUser
   */
  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  /**
   * Recupere les données d'un contact à partir du cid
   */
  public function getAllDataByCid (&$form) {
    $req = \Drupal::request();
    $cid = $req->query->get('cid');
    if (!is_numeric($cid)) {
      $cid = explode('?', $cid);
      $cid = $cid[0];
    }
    $token = $req->get('token');

    if ($cid != $this->decryptString($token)) {
      return $this->redirectHomePage();
    }
    $contactInfo = \Civi\Api4\Contact::get(FALSE)
    ->addSelect('organization_name', 'org_dlr.descriptif_entreprise', 'address_primary.street_address', 'org_dlr.activiteprincipale',
    'address_primary.postal_code', 'address_primary.city', 'address_primary.country_id:label', 'email_primary.email', 'phone_primary.phone')
    ->addWhere('id', '=', $cid)
    ->execute();
    
    $contactInfo = iterator_to_array($contactInfo);

    $websites = \Civi\Api4\Website::get(FALSE)
      ->addSelect('url')
      ->addWhere('contact_id', '=', $cid)
      ->execute()->column('url');
    $contacts = \Civi\Api4\Contact::get(FALSE)
      ->addSelect()
      ->addWhere('id', '=', $cid)
      ->execute();

    
    $materiel_occasion = \Civi\Api4\Contact::get(FALSE)
      ->addSelect('Materiel.nom_occasion:label')
      ->addWhere('id', '=', $cid)
      ->execute()->first();

    //$iterator = iterator_to_array($marquees);
    $organizationName = ''; 
    $descriptifEntreprise = ''; 
    $email = ''; 
    $phone = ''; 
    $stree_address = ''; 
    $postal_code = ''; 
    $website_url = ''; 
    $city = ''; 
    $activitePrincipal = ''; 
    $latitude = ''; 
    $longitude = ''; 
    if ($contactInfo) {
      $organizationName = reset($contactInfo)['organization_name'];
      $organizationName = reset($contactInfo)['organization_name'];
      $descriptifEntreprise =  reset($contactInfo)['org_dlr.descriptif_entreprise'];
      $email =  reset($contactInfo)['email_primary.email'];
      $phone =  reset($contactInfo)['phone_primary.phone'];
      $stree_address =  reset($contactInfo)['address_primary.street_address'];
      $postal_code =  reset($contactInfo)['address_primary.postal_code'];
      $city =  reset($contactInfo)['address_primary.city'];
      $activitePrincipal =  reset($contactInfo)['org_dlr.activiteprincipale'];
      // $markup = ['#markup' => $descriptifEntreprise];
      // $descriptifEntreprise = \Drupal::service('renderer')->render($markup)->__toString();
    }
    
    $website_url = $websites ? $websites[0] : '';

    $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_contact_organization_name']['#default_value'] = $organizationName;
    $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_custom_50_7584']['#default_value'] = $descriptifEntreprise;
    $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_email_email']['#default_value'] = $email;
    $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_phone_phone']['#default_value'] = $phone;
    $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['nom_entreprise']['#default_value'] = $organizationName;
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_address_street_address']['#default_value'] = $stree_address;
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_address_street_address']['#attributes']['class'][] = 'hide hidden';
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_address_street_address']['#attributes']['disabled'] = true;
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_address_postal_code']['#attributes']['class'][] = 'hide hidden';
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_address_postal_code']['#default_value'] = $postal_code;
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_address_postal_code']['#attributes']['disabled'] = true;
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_website_url']['#default_value'] = $website_url;
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_address_city']['#default_value'] = $city;
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_address_city']['#attributes']['class'][] = 'hide hidden';
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_address_city']['#attributes']['disabled'] = true;
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_activity_1_cg30_custom_7584']['#options'] = $this->getAllActivitePrincipal();
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_activity_1_cg30_custom_7584']['#default_value'] = $activitePrincipal;
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_activity_1_cg30_custom_7584']['#attributes']['disabled'] = true;
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_marque']['#options'] = $this->getAllMarques();
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['#markup'] = '';
     $form['contact_us_markup'] = [
      '#type' => 'markup',
      '#markup' => '<p><a href="mailto:contact@dlr.fr" target="_blank" rel="noreferrer noopener" class="PrimaryLink BaseLink">
      Pour toute autre modification contactez nous sur contact@dlr.fr</a></p>',
    ];
    $form['contact_us_markup']['#weight'] = 155;
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_contact_video_presentation']['#default_value'] = $this->getVideoDefaultValue($cid);
     $lat = $this->getLatAndLondeDefaultValue($cid);
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_contact_latitude']['#default_value'] = $this->getLatAndLondeDefaultValue($cid);
    //  $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_contact_latitude']['#attributes']['class'][] = 'hide hidden';
    //  $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_contact_latitude']['#attributes']['disabled'] = true;
     $lon =  $this->getLatAndLondeDefaultValue($cid, false);;
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_contact_longitude']['#default_value'] = $this->getLatAndLondeDefaultValue($cid, false);
    //  $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_contact_longitude']['#attributes']['class'][] = 'hide hidden';
    //  $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_contact_longitude']['#attributes']['disabled'] = true;
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['nom_entreprise']['#default_value'] = $organizationName;
    $form['actions']['submit']['#value'] = 'Enregistrer';

    $country = $this->getDefaultCountry($cid);
    $countryName = $this->getCountryNameById($country);


     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_contact_longitude'][
      '#suffix'] = $this->html($stree_address, $postal_code, $city,$countryName, $lat, $lon);     
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_nom_location']['#options'] = $this->getAllMateriels();
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_nom_location']['#default_value'] = $this->getDefaultValueLocation($cid);
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_address_country_id']['#options'] = $this->allCountries();
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_address_country_id']['#attributes']['class'][] = 'hide hidden';
     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_address_country_id']['#default_value'] = $this->getDefaultCountry($cid);
    //  $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_address_country_id']['#attributes']['disabled'] = true;

     $form['elements']['civicrm_1_contact_1_fieldset_fieldset']['civicrm_1_contact_1_marque']['#default_value'] = $this->getDefaultValueMarque($cid);
   
      
    
    return $form;
    
  }

  private function getCountryNameById($id) {
    return \Civi\Api4\Country::get(FALSE)
    ->addSelect('name')
    ->addWhere('id', '=', $id)
    ->execute()->first()['name'];
  }

  private function html($rue, $postal,$city, $country, $lat, $lon) {
    return '<div class="our-ebook"><div class="container">
              <div class="row adress-container"><div class="col-6 d-none d-lg-block"> 
              </div>
              <div class="col col-lg-6 conteneur-block">
                <div class="our-ebook-cont">
                  <div class="row custom-row">
                  <div class="col-md-6 ad-firs-col">
                  <h3 class="bold">Adresse principale</h3>
                  <p>' . $rue . '</p>
                  <p>' . $postal . ' ' . $city . '</p>
                  <p>' . $country . '</p>
                  </div>
                  
                  </div>
                </div>
              </div>
            </div>';
  }

  /**
   * Valeur par defaut du pays
   */
  private function getDefaultCountry ($cid) {
    return \Civi\Api4\Address::get(FALSE)
    ->addSelect('country_id')
    ->addWhere('contact_id', '=', $cid)
    ->execute()->first()['country_id'];
  }

  public function getVideoDefaultValue ($cid) {
    if ($cid) {
      $db = \Drupal::database();
      $video_url = '';
      $query = 'select field_video_guide_target_id from civicrm_contact__field_video_guide where entity_id = ' . $cid;
      $video_id = $db->query($query)->fetch()->field_video_guide_target_id;
      if ($video_id) {
        
        $query = 'select field_media_oembed_video_value from media__field_media_oembed_video where entity_id = ' . $video_id;
        $video_url = $db->query($query)->fetch()->field_media_oembed_video_value;
      }
      return $video_url;
    }
  }

  /**
   * Les matériels location lié à l'entreprise
   */
  private function getDefaultValueLocation ($cid) {
    
    $materiel_location = \Civi\Api4\Contact::get(FALSE)
      ->addSelect('Materiel.nom_location')
      ->addWhere('id', '=', $cid)
      ->execute()->getIterator();
      $rentals = iterator_to_array($materiel_location); 
      $rentals = $rentals[0]['Materiel.nom_location'];
      return $rentals;
  }

  /**
   * 
   */
  private function getLatAndLondeDefaultValue($cid, $lat = true) {

    $latitudeAndLongitude = \Civi\Api4\Address::get(FALSE)
      ->addSelect('geo_code_1', 'geo_code_2')
      ->addWhere('contact_id', '=', $cid)
      ->addWhere('is_primary', '=', TRUE)
      ->execute()->getIterator();
      
      $latitudeAndLongitude = iterator_to_array($latitudeAndLongitude); 
      if ($lat) {
        return $latitudeAndLongitude[0]['geo_code_1'];
      }
      return $latitudeAndLongitude[0]['geo_code_2'];
  }
  
  /**
   * Recupère tous les type d'activité principale
   */
  private function getAllActivitePrincipal () {

    $option = [];
    $optionValues = \Civi\Api4\OptionValue::get(FALSE)
      ->addSelect('value', 'label')
      ->addWhere('option_group_id', '=', 100)
      ->execute()->getIterator();
      
    $optionValues = iterator_to_array($optionValues);
    foreach ($optionValues as $key => $value) {
      $option[$value['value']] = $value['label'];
    }
    
    
    
    return $option;
    
  }

  private function getDefaultValueMarque ($cid) {
    $defaultValue = \Civi\Api4\CustomValue::get('Marques', FALSE)
    ->addSelect('nom_Marque')
    ->addWhere('entity_id', '=', $cid)
    ->execute()->getIterator();
    
    $defaultValue = iterator_to_array($defaultValue);
    $defaultValue = array_column($defaultValue, 'nom_Marque');

    return $defaultValue;
  }

  /**
   * Recupère tous les marques
   */
  public function getAllMarques () {
    $options = [];
    $marqueses = \Civi\Api4\OptionValue::get(FALSE)
      ->addSelect('label', 'value')
      ->addWhere('option_group_id', '=', 105)
      ->execute();

    $marqueses = iterator_to_array($marqueses);
    foreach($marqueses as $marque) {
      $options[$marque['value']] = $marque['label'];
    }

    return $options;

  }

  /**
   * Ajout media de type "video en ligne"
   */
  public function createMediaTypeVideo($urlVideo) {

    // Créez un nouvel objet Media pour le type de média vidéo.
    $media = Media::create([
      'bundle' => 'remote_video',
      'langcode' => 'fr', // La langue du média
      'status' => TRUE, // Définissez-le sur TRUE pour publier le média immédiatement
      'field_media_oembed_video' => $urlVideo, 
    ]);

    // Enregistrez le média.
    $media->save();

    return $media;

  }


  /**
   * Ajout d'un video de presentation à la fiche contact
   */
  public function assignVideoToEntreprise ($cid, $mid, $db) {
    $check_if_already_exist_query = 'select * from civicrm_contact__field_video_guide where entity_id = ' . $cid;
    $if_already_exist = $db->query($check_if_already_exist_query)->fetch();
    
    if (!$if_already_exist) {//insert
      // Get the database connection.
      $database = \Drupal::database();

      // Specify the table name and values to insert.
      $table = 'civicrm_contact__field_video_guide';
      $values = [
        'bundle' => 'civicrm_contact',
        'entity_id' => $cid,
        'revision_id' => $cid,
        'field_video_guide_target_id' => $mid,
        'langcode' => 'fr',
        'delta' => 0,
      ];

      // Insert the record into the table.
      $database->insert($table)
        ->fields($values)
        ->execute();
    }else { //update
      $query = 'UPDATE civicrm_contact__field_video_guide  SET field_video_guide_target_id = ' . $mid . ' where entity_id = ' . $cid;
      $db->query($query)->execute(); 
    }
  }

/**
 * Suppression liaison du cid avec une video de presentation
 */
public function deleteVideoLinkedWithCid($cid) {
  $db = \Drupal::database();
  $query = "DELETE FROM civicrm_contact__field_video_guide
  WHERE entity_id = " . $cid;
  return $db->query($query)->execute();
}

public function updatedb ($cid, $mid)  {

  
  // Get the database connection.
    /* $database = \Drupal::database();

    // Specify the table name and values to update.
    $table = 'civicrm_contact__field_video_guide';
    $values = [
      'bundle' => 'civicrm_contact',
      'entity_id' => $cid,
      'revision_id' => $cid,
      'field_video_guide_target_id' => $mid,
      'delta' => 0,
    ];

    // Specify the condition for the update.
    $condition = [
      'entity_id' => $cid,
    ];

    // Update the records in the table.
    $database->update($table)
    ->fields($values)
    ->condition($condition)
    ->execute(); */


}


    /**
   * 
   */
public function encryptString($id) {
  $cipher = 'AES-256-CBC';
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
    $encrypted = openssl_encrypt($id, $cipher, 'makoa_phenix', OPENSSL_RAW_DATA, $iv);
    return bin2hex($iv . $encrypted);
}

/**
 * Redirect to the homepage
 */
public function redirectHomePage () {
  $response = new \Symfony\Component\HttpFoundation\RedirectResponse(\Drupal\Core\Url::fromRoute('<front>')->toString());
  return $response->send();
}

/**
 * 
 */
public function decryptString($encryptedId) {
  $cipher = 'AES-256-CBC';
    $data = hex2bin($encryptedId);
    $iv = substr($data, 0, openssl_cipher_iv_length($cipher));
    $encrypted = substr($data, openssl_cipher_iv_length($cipher));
    $decryptedId = openssl_decrypt($encrypted, $cipher, 'makoa_phenix', OPENSSL_RAW_DATA, $iv);

  if (!is_numeric($decryptedId)) {
    // return $this->redirectHomePage();
  }

  return $decryptedId;
}


public function getAllMateriels () {
  $optionValues = \Civi\Api4\OptionValue::get(FALSE)
  ->addSelect('id', 'label', 'value')
  ->addWhere('option_group_id', '=', 106)
  ->addWhere('is_active', '=', true)
  ->execute()->getIterator();
      
    $optionValues = iterator_to_array($optionValues);
    foreach ($optionValues as $key => $value) {
      $option[$value['value']] = $value['label'];
    }
    
    
    
    return $option;

}

public function updateLocationNewSetNull ($cid) {
  return \Civi\Api4\Contact::update(FALSE)
  ->addValue('Materiel.nom_location_new', [])
  ->addWhere('id', '=', $cid)
  ->execute();
}
/**
 * Update des location_new en fonction des materiel location selectionné
 */
public function updateLocationNew ($cid, $materiel_location) {
  $final_array = [];
  foreach ($materiel_location as $materiel_id) {
    $final_array[] = $this->getFamilleByLocation($materiel_id);
  }
  $merged_array = array_merge(...$final_array);
  $famille_id_to_be_saved = array_unique($merged_array);
  $materiel_location_new_update = \Civi\Api4\Contact::update(FALSE)
  ->addValue('Materiel.nom_location_new', $famille_id_to_be_saved)
  ->addWhere('id', '=', $cid)
  ->execute();
}

/**
 * Permet de mettre à jour l'email principal
 */
public function updateMail ($email, $cid) {
  return \Civi\Api4\Email::update(FALSE)
  ->addValue('email', $email)
  ->addWhere('contact_id', '=', $cid)
  ->addWhere('is_primary', '=', TRUE)
  ->execute();
}

/**
 * Permet de mettre à jour le site web principal
 */
public function updateWebsite ($website, $cid) {
  if ($website) {
    $websiteAlready = \Civi\Api4\Website::get(FALSE)
      ->addSelect('url')
      ->addWhere('contact_id', '=', $cid)
      ->execute()->getIterator();
      $websiteAlready = iterator_to_array($websiteAlready);
    if (!empty($websiteAlready)) {//s'il y a déjà un site web
      //add conditino if exist
      return  \Civi\Api4\Website::update(FALSE)
      ->addValue('url', $website)
      ->addWhere('contact_id', '=', $cid)
      ->addWhere('website_type_id', '=', 2)//principal
      ->execute();
    }
    return \Civi\Api4\Website::create()
    ->addValue('contact_id', $cid)
    ->addValue('url', $website)
    ->addValue('website_type_id', 2)
    ->execute();
  }else {//delete
    return \Civi\Api4\Website::delete(FALSE)
    ->addWhere('contact_id', '=', $cid)
    ->addWhere('website_type_id', '=', 2)//principal
    ->execute();
  }
}

/**
 * Permet de mettre à jour le numero de tel principal
 */
public function updatePhone ($data_phone, $cid) {
  if ($data_phone) {
    $results = false;
    $results = \Civi\Api4\Phone::update(FALSE)
    ->addValue('phone_numeric', $data_phone)
    ->addValue('phone', $data_phone)
    ->addWhere('contact_id', '=', $cid)
    ->execute();

      // $updateLon = 'update civicrm_phone set phone_numeric = "' . $data_phone . '" where contact_id = ' . $cid . ' and is_primary = 1';
      // $results = \Drupal::database()->query($updateLon)->execute();
      if ($results) {

      }else {
        //$this->redirectHomePage();
      } 
  }
}

/**
 *  Correspondance entre location et location_new 
 */
public static function getFamilleByLocation($materiel_id) {
  $whiteslist = [];
  switch ($materiel_id){
    case 1:    // Air comprimé
      $whiteslist = [4];
        //add_famille_to_arr($arr_famille, 4);
        break;
    case 2:    //Blindage
      $whiteslist = [1, 3, 5];
        // add_famille_to_arr($arr_famille, 1); // ÉQUIPER / SÉCURISER UN SITE
        // add_famille_to_arr($arr_famille, 3); //TRAVAILLER LES SOLS
        // add_famille_to_arr($arr_famille, 5); // CONSTRUIRE / ENTRETENIR / AMÉNAGER  BÂTIMENT
        break;
    case 3:  // Carrière
      $whiteslist = [3];//TRAVAILLER LES SOLS
      break;
    case 4: //  Chariot industriel
      $whiteslist = [6];
      break;
    case 5: //  Chariot tÃ©lescopique
      $whiteslist = [5, 6, 8];
      break;
    case 6: // Compactage
      $whiteslist = [3];
      break;
    case 7: //  a supprimer DÃ©coration & Bricolage
      $whiteslist = [];
      break;
    case 8: //  Fournitures Ã©lectriques & Ã©clairage
      $whiteslist = [1, 4, 7];
      break;
    case 9: //  a supprimer Espaces verts
      $whiteslist = [];
      break;
    case 10: //  Echafaudage
      $whiteslist = [1, 2, 5];
      break;
    case 11: //  Forage/Sondage/Injection
      $whiteslist = [3];
      break;
    case 12: //  Forage horizontal & trancheuses
      $whiteslist = [3];
      break;
    case 13: //  Grues Ã  tour
      $whiteslist = [2, 5, 6];
      break;
    case 14: //  HÃ©bergement, base-vie
      $whiteslist = [1, 5, 7];
      break;
    case 15: //  Levage de charge
      $whiteslist = [2, 5, 6];
      break;
    case 16: //  MÃ©tronomie/Controle
      $whiteslist = [1, 3, 5, 9, 10];
      break;
    case 17: //  Nacelle/Plateforme ElÃ©vatrice
      $whiteslist = [2, 5, 7, 9];
      break;
    case 18: //  Perforation/Abattage
      $whiteslist = [3, 10];
      break;
    case 19: //  Pompage
      $whiteslist = [3, 11];
      break;
    case 20: //  Nettoyage
      $whiteslist = [5, 7, 8, 9, 10, 11];
      break;
    case 21: //  SÃ©curitÃ©, environnement
      $whiteslist = [1, 2, 5, 7, 11];
      break;
    case 22: //  Sciage
      $whiteslist = [3, 5, 8, 9];
      break;
    case 23: //  Rabotage
      $whiteslist = [3];
      break;
    case 24: //  Second oeuvre
      $whiteslist = [5, 10];
      break;
    case 25: //  Outillage Ã©lectroportatif
      $whiteslist = [10];
      break;
    case 26://   Signalisation, accÃ¨s, stabilisation
      $whiteslist = [1, 2, 3, 5, 7, 8, 10];
      break;
    case 27://   Terrassement
      $whiteslist = [3, 5, 6];
      break;
    case 28://   Traitement surface et sol
      $whiteslist = [3, 5, 8];
      break;
    case 29://   Traitement bÃ©ton/Projection
      $whiteslist = [5];
      break;
    case 30://  a supprimer Camion-benne, Fourgon, Remorques
      $whiteslist = [];
      break;
    case 39://  Etaiement
      $whiteslist = [1, 2, 5, 9];
      break;
    case 40://  Sanitaire, hygiÃ¨ne
      $whiteslist = [1, 5, 7, 11];
      break;
    case 41://  a supprimer Chauffage, climatisation
      $whiteslist = [];
      break;
    case 42://  Coffrage
      $whiteslist = [1, 5];
      break;
    case 43://   Soudage
      $whiteslist = [4, 5, 10];
      break;
    case 44://   DÃ©molition
      $whiteslist = [9];
      break;
    case 45://   a supprimer Ã‰vÃ©nement & rÃ©ception
      $whiteslist = [];
      break;
    case 46://   Drones
      $whiteslist = [1,2,6,7,8,9];
      break;
    case 47://   Groupe Ã©lectrogÃ¨ne
      $whiteslist = [1, 4, 7];
      break;
    case 48://   Maritime & fluviale
      $whiteslist = [2, 6, 8];
      break;
    case 49://   VÃ©hicules Ã©lectriques
      $whiteslist = [6, 11];
      break;
    case 50://   Route
      $whiteslist = [3, 6, 10];
      break;
    case 51://   Recyclage, concassage, criblage
      $whiteslist = [9, 11];
      break;
    case 52://   UnitÃ©s mobiles de dÃ©contamination
      $whiteslist = [1, 9, 11];
      break;
    case 60://   Brumisateurs
      $whiteslist = [1, 3, 5, 7, 9];
      break;
    case 61://   Sablage
      $whiteslist = [5];
      break;
    case 62://   Toilettes sÃ¨ches
      $whiteslist = [1, 5, 7, 9, 11];
      break;
    case 63://   Topographie
      $whiteslist = [1, 3, 5, 8];
      break;
    case 64://   Laser
      $whiteslist = [1, 3, 5, 8];
      break;
    case 65:// Camion-benne
      $whiteslist = [6];
      break;
    case 66://  Fourgon
      $whiteslist = [6];
      break;
    case 67://  Remorques
      $whiteslist = [6];
      break;
    case 68://  Chauffage
      $whiteslist = [1, 4, 5, 7];
      break;
    case 69://  Climatisation
      $whiteslist = [1, 4, 5, 7];
      break;
    case 70: //   DÃ©coration
      $whiteslist = [7];
      break;
    case 71: //  Bricolage
      $whiteslist = [10];
      break;
    case 72: //  Coupe et broyage
      $whiteslist = [8];
      break;
    case 73: //  Taille et entretien
      $whiteslist = [8];
      break;
    case 74: //  Agriculture
      $whiteslist = [8];
      break;
    case 75: //  //ransport des vÃ©gÃ©taux
      $whiteslist = [6, 8];
      break;
    case 76: //  PrÃ©paration des sols
      $whiteslist = [3, 8];
      break;
    case 77: //  Tentes, Chapiteaux, Barnumsâ€¦
      $whiteslist = [7];
      break;
    case 78: //  Mobilier
      $whiteslist = [7];
      break;
    case 79: //  Cuisine professionnelle
      $whiteslist = [7];
      break;
    case 80: //  Audio-visuel
      $whiteslist = [7];
      break;
  }

  return $whiteslist;
}


/**
 * Recupere l'option pour le champ pays
 */
public function allCountries () {
  $countries = \Civi\Api4\Country::get(FALSE)
  ->addSelect('id', 'name')
  ->execute()->getIterator();
      
  $optionValues = iterator_to_array($countries);
  foreach ($optionValues as $key => $value) {
    $option[$value['id']] = t($value['name']);
  }
  
  
  
  return $option;
}



  public function whiteList () {
   
    $array = [
      'formulaire_pour_adherent',
      "backbuttonblock",
      "blocbasdepage",
      "9",
      "alphabetique",
      "fiche_details",
      "asiderightlabelse",
      "asiderigthmembreassoc",
      "b_zf_account_menu",
      "b_zf_branding",
      "b_zf_content",
      "b_zf_footer",
      "b_zf_help",
      "b_zf_local_actions",
      "b_zf_local_tasks",
      "b_zf_main_menu",
      "b_zf_messages",
      "b_zf_page_title",
      "b_zf_tools",
      "backbuttonblock",
      "blocbasdepage",
      "9",
      "companyprofilesecondcolumnviewblock",
      "customsearchblock",
      "formulairederecherche",
      "formulaireexposelocation_occasion_membres_associes_liste_carte",
      "formulaireexposelocation_occasion_membres_associes_page_1",
      "formulaireexposelocation_occasion_membres_associes_page_2",
      "guide",
      "headerannuairelogoblock",
      "imagehomeannuaire",
      "liens",
      "2",
      "makoa",
      "7",
      "menuprincipal",
      "menuprincipal_2",
      "pagetitle",
      "publiciteheadblock",
      "textepagedeconnexionentete",
      "6",
      "textepieddepage",
      "8",
      "textepourformulaires",
      "views_block__actualites_block_1",
      "views_block__actualites_block_2",
      "views_block__actualites_block_3",
      "views_block__civievents_base_sur_le_contact__block_1",
      "views_block__civievents_base_sur_le_contact__block_1_2",
      "views_block__civievents_base_sur_le_contact__block_2",
      "views_block__civievents_base_sur_le_contact__block_2_2",
      "views_block__civievents_base_sur_le_contact__block_3",
      "views_block__civievents_base_sur_le_contact__block_4",
      "views_block__civievents_base_sur_le_contact__block_5",
      "views_block__fiche_block_1",
      "views_block__hero_actualites_block_1",
      "views_block__illustration_koama_block_1",
      "views_block__lien_update_effectifs_lien_update_effectif",
      "views_block__publicite_publicite_guide_en_hauteur",
      "corrige_lien_image_page_accueil_sidebar_left",
      "alphabetique",
      "champ_de_recherche",
      "civicrm",
      "civicrm_fiche_contact_",
      "correction_bug_liens",
      "corrige_lien_image_page_accueil_sidebar_left",
      "document_thumbnail",
      "fiche_details",
      "filtres",
      "form_masquer_boutton_preview",
      "front_extranet_masquer_nom_machine",
      "image_hover_effect",
      "koama_illustration",
      "menu",
      "menu_annuaire",
      "pages_de_recherche",
      "page_view_civicrm_event_content",
      "submitted",
      "user_profile",
      "vue_guide",
      611,       //id page annuaire,


      //format de texte
      'basic_html',
      'restricted_html',
      'developper',
      'plain_text',
    ];
    
    
    return $array;
  }

}