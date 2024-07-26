(function($, Drupal, drupalSettings) {

    Drupal.behaviors.webform = {
      attach: function(context, settings) {
        jQuery.noConflict();

        $(window).on('load',function() {

            if ($('.page-civicrm-verifie-agence-liste').length)  {
                console.log(' here ')
                //Recuperer le nom de l'agence
                let cid =  jQuery('.page-list-agence').attr('data-contact-id');
                $.ajax({
                    url: '/civicrm/get-nom-agence',
                    type: "POST",
                    data: {cid: cid},
                    success: (successResult, val, ee) => {
                        console.log(successResult, successResult.nameCible, ' AVEC')
                        if (!$('[name="name"]').val()) {
                            $('[name="name"]').val(successResult.nameCible)
                        }
                    },
                    error: function(error) {
                       
                        // console.log(error, 'ERROR PARSING TOKEN AJAX')
                    }
                });
            }
            

            //formulaire adherent 
            let tr = $( 'body').find('.table.table-striped tbody tr');
            let btnw = $( 'body').find('.btn-warning');
         
            $('body').once('leaflet').on('click', '.table.table-striped tbody tr .btn-default', function (e) {
                e.preventDefault();
                let idAgence = $(this).attr('ng-href').split('/#')[1];
                let cid = idAgence;

                $.ajax({
                    url: '/civicrm/verifie-agence-liste/agenceId',
                    type: "POST",
                    data: {agenceId: idAgence},
                    success: (successResult, val, ee) => {
                    let address = successResult.address;
                    let mail = successResult.mail;
                    let name = successResult.name;
                    let phone = successResult.phone;
                    console.log(phone, 'h po')
                    console.log('valeur : ', successResult)
                    $('[name="name_agence"]').val(name);
                    $('[name="email_agence"]').val(mail);
                    $('[name="street_agence"]').val(address ? address.street_address : '');
                    $('[name="postal_code_agence"]').val(address.postal_code);
                    $('[name="city_agence"]').val(address.city);
                    $('[name="city_agence"]').val(address.city);
                    $('[name="country_agence"]').val(address.country_id);
                    $('[name="current_agence_id"]').val(idAgence);
                    $('[name="phone_agence"]').val(phone);
                    },
                    error: function(error) {
                    console.log(error, 'ERROR')
                    }
                });

                $('[name="names"]').val('test')

                    Drupal.dialog('#custom-popup', {
                        title: 'Modification agence',
                        width: '800',
                        height: '400',
                    }).showModal();
                    $('#custom-popup').removeClass('hide');

            })

            if (window.location.href.includes('/civicrm/verifie-agence-liste')) {
                let contactId = window.location.href.split('?id=')[1].split('&token')[0];
                $('.id_contact_hidden').val(contactId)
            }

            const urlParams = new URLSearchParams(window.location.search);
            // Get a specific parameter by name.
            let  getCid = urlParams.get('cid');
            if (window.location.href.includes('/formulaire-pour-adherent/confirmation')) {
                if (window.location.href.includes('formulaire-pour-adherent')) {
                    $.ajax({
                        url: '/form/formulaire-pour-adherent/confirmation/back_link',
                        success: (successResult, val, ee) => {
                            console.log('back link ', successResult.back_link, 'verifyyy' , successResult.verify_agence, ' btn' , successResult.btn_verify)
                            $('.webform-confirmation__back a').attr('href', successResult.back_link)
                            if(!$('.button.btn-blue').length) {
                                // $(successResult.btn_verify).insertBefore('.webform-confirmation__back');
                                $(successResult.verify_agence).insertBefore('.webform-confirmation__back');
                            }
                            
                        },
                        error: function(error) {
                            console.log(error, 'ERROR')
                        }
                    });
                    
                }
            }


            
            $('body').on('click', '.page-civicrm-verifie-agence-liste .btn.btn-xs.btn-warning', function () {
                event.preventDefault();
                return false;
                Drupal.dialog('#custom-popup', {
                    title: 'Modification agence',
                    width: '600',
                    height: '400',
                }).showModal();
                $('#custom-popup').removeClass('hide');
            });
        })
               
        
        let url = window.location.href;
            // Create a URL object to parse the URL
            let urlObject = new URL(url);

            // Get the value of the "id" parameter from the query string
            let fragmentId = urlObject.hash.substr(1); // Remove the "#" symbol
            let fragmentParams = new URLSearchParams(fragmentId);
            let getContactId = fragmentParams.get("id");
            var token = fragmentParams.get('cs');
            if (url.indexOf("/civicrm/verifie-agence-liste") !== -1) {
                if (!getContactId) {
                    location.href= "/";
                }
            }

        //Document ready
        $(document).ready(function() {
            let allParams = new URLSearchParams(window.location.search);
            let  getCid = allParams.get('cid');
            //Form adherent, on click sur le btn valider sans modif
          /*   $('.valid-without-modif').once('leaflet').on('click', function () {
                $.ajax({
                    url: '/form/formulaire-pour-adherent/validate-without-modification',
                    type: "POST",
                    data: {getCid: getCid},
                    success: (successValue, val) => {
                        if(successValue) {
                            location.href = successValue   
                        }
                    },
                    error: function(error) {
                        console.log(error, 'ERROR')
                    }
                });
            }) */

            //Redirect page liste des agence si ce n'est pas le bon id dans l'url
            let url = window.location.href;
            // Create a URL object to parse the URL
            let urlObject = new URL(url);

            // Get the value of the "id" parameter from the query string
            let fragmentId = urlObject.hash.substr(1); // Remove the "#" symbol
            let fragmentParams = new URLSearchParams(fragmentId);
            let getContactId = fragmentParams.get("id");
            var token = fragmentParams.get('cs');
            if (url.indexOf("/civicrm/verifie-agence-liste") !== -1) {
                if (!getContactId) {
                    location.href= "/";
                }
            }

            if (!token) {
              var query = window.location.href;
              var vars = query.split('&');
              if (vars && vars[1]) {
                 token = vars[1].split('token=')[1];
              }
            }
            $('.page-list-agence').attr('data-contact-id', getContactId)
            if (url.indexOf("/civicrm/verifie-agence-liste") !== -1) {
                if (token) {

                    $.ajax({
                        url: '/civicrm/verifie-agence-liste/checkToken',
                        type: "POST",
                        data: {token: token, cid: getContactId},
                        success: (successResult, val, ee) => {
                            console.log(successResult, successResult.cid, ' SUCC')
                            console.log('NOT SUCCESS', successResult)
                            if (!successResult.cid) {//on n'a pas le bon checksum on redirige à la page d'accueil
                                location.href= "/";
                                console.log('no checksum')
                            }
                        },
                        error: function(error) {
                            location.href= "/";
                            // console.log(error, 'ERROR PARSING TOKEN AJAX')
                        }
                    });
                }else {
                  console.log('esle...')
                    location.href= "/";
                }
            }

            jQuery('.marque-select.form-select').on('change', function () {
              var selectedOptions = jQuery(this).find('option:selected');
              
              // Initialize an array to store the labels.
              var labels = [];
          
              // Loop through the selected option elements and get their labels.
              selectedOptions.each(function () {
                  labels.push(jQuery(this).text());
              });
              
              if (CKEDITOR && CKEDITOR.instances['edit-civicrm-2-activity-1-activity-details-value']) {
                CKEDITOR.instances['edit-civicrm-2-activity-1-activity-details-value'
                    ].setData(labels);

              }
                jQuery('.civicrm-enabled.form-textarea').val(labels);

            });

            $('[name="civicrm_1_contact_1_phone_phone"]').on('mouseout', function() {
                let dataPhone = $(this).val();
                // Remove whitespace
                dataPhone = dataPhone.replace(/\s+/g, '');

                // Split the string into pairs of characters
                dataPhone = dataPhone.match(/.{1,2}/g).join(" ");
                $(this).val(dataPhone);
            })

            $('.page-civicrm-verifie-agence-liste [name="phone_agence"], .page-civicrm-verifie-agence-liste [name="phone"],[name="civicrm_1_contact_1_phone_phone"]').on('keyup', function (e) {
                // Get the current input value
                var inputValue = $(this).val();
                
                // Use a regular expression to remove any non-numeric characters
                var numericValue = inputValue.replace(/[^0-9 ]/g, '');
                
                // Update the input field with the numeric value
                $(this).val(numericValue);
              });

        });//End document ready
      }

    }
})(jQuery, Drupal, drupalSettings);    
