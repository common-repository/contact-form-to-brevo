<?php
namespace CFB_PSD\Base;
use Exception;
use Brevo\Client\Configuration;
use Brevo\Client\Api\ContactsApi;
use Brevo\Client\Model\CreateContact;
use GuzzleHttp;

if(!class_exists('CFB_PSD_Subscribe')){
	class CFB_PSD_Subscribe{

		public function register(){

			add_action( 'wpcf7_before_send_mail', array($this, 'cfb_psd_subscribe' ),10,1); 
		}

		public function cfb_psd_subscribe($contact_form ){
			
			$submission = \WPCF7_Submission::get_instance();
		    $posted_data = $submission->get_posted_data();
		    $wpcf7 = \WPCF7_ContactForm::get_current();
			$form_id = $wpcf7->id;
		    $enable_cfb_psd = get_post_meta($form_id,'cfb_psd_enable',true);
		    $fields = get_post_meta($form_id,'cfb_psd_fields',true);
		    $gdpr_key = isset($fields['cfb_psd_gdpr']) ? esc_html($fields['cfb_psd_gdpr']) : '';

		    if(!empty($gdpr_key)){
				$gdpr_val = isset($posted_data[$gdpr_key][0]) ? $posted_data[$gdpr_key][0] : 0;
			}else{
				$gdpr_val = 1;
			}

			if($enable_cfb_psd == 'yes' && $gdpr_val==1 && apply_filters( 'cfb_psd_send_data', 'true' ) == 'true' ){

				$emailkey=isset($fields['cfb_psd_email']) ? esc_html($fields['cfb_psd_email']) : '';
				$fnamekey=isset($fields['cfb_psd_first_name']) ? esc_html($fields['cfb_psd_first_name']) : '';
				$lnamekey=isset($fields['cfb_psd_last_name']) ? esc_html($fields['cfb_psd_last_name']) : '';
				$phonekey=isset($fields['cfb_psd_phone']) ? esc_html($fields['cfb_psd_phone']) : '';
				$extrakey  = isset($fields['extra-fields']) ? $fields['extra-fields'] : '';

				$email='';
				if(!empty($emailkey))
				$email=isset($posted_data[$emailkey]) ? $posted_data[$emailkey] : '';

				$fname='';
				if(!empty($fnamekey))
				$fname=isset($posted_data[$fnamekey]) ? $posted_data[$fnamekey] : '';

				$lname='';
				if(!empty($lnamekey))
				$lname=isset($posted_data[$lnamekey]) ? $posted_data[$lnamekey] : '';

				$phone='';
				if(!empty($phonekey))
				$phone=isset($posted_data[$phonekey]) ? $posted_data[$phonekey] : '';


				if(!empty($email)){
					$brevo = get_post_meta($form_id,'cfb_psd_credentials',true);
					$update_existing = get_post_meta($form_id,'cfb_psd_update_existing',true);

					if(isset($brevo['api_key']) && !empty($brevo['api_key']) && isset($brevo['list_id'])){

						$api_key = $brevo['api_key'];
						$list_id = $brevo['list_id'];

						//Brevo API call
				        $credentials = Configuration::getDefaultConfiguration()->setApiKey('api-key', $api_key);
				        $apiInstance = new ContactsApi(
				            new GuzzleHttp\Client(),
				            $credentials
				        );

				        $attr = array(
				            'FIRSTNAME'=> $fname,
				            'LASTNAME'=>$lname
				        );
						if(!empty($extrakey)){
							foreach($extrakey['name'] as $key=>$val){
								$name = isset($extrakey['name'][$key]) ? trim($extrakey['name'][$key]) : '';
								$value = isset($extrakey['value'][$key]) ? trim($extrakey['value'][$key]) : '';
								
                                $value=isset($posted_data[$value]) ? $posted_data[$value] : $value;
								$special_mail_tags = array( '_serial_number', '_remote_ip',
									'_user_agent', '_url', '_date', '_time', '_post_id', '_post_name',
									'_post_title', '_post_url', '_post_author', '_post_author_email',
									'_site_title', '_site_description', '_site_url', '_site_admin_email',
									'_user_login', '_user_email', '_user_display_name' );
                                if(in_array($value, $special_mail_tags)){
                                	$value = apply_filters( 'wpcf7_special_mail_tags', '',$value, false );
                                }
                                
								/**
								 * Checkboxes and select dropdown support
								 *
								 * @since  1.0.7
								 */
                                if(is_array($value)){
								   $value = implode("||",$value);
								}

                                if($value!=''){
									$attr[$name] = $value;
							    }

							}
						}
				        $fields = array(
				            'email' => $email,
				            'attributes' => (object)$attr,     
				            'listIds' => array_map('intval', [$list_id])
				        );
				        if($update_existing=='yes'){
				        	$fields['updateEnabled'] = true;
				        }else{
				        	$fields['updateEnabled'] = false;
				        }

				        //print_r($fields);
				        //die();

				        $createContact = new CreateContact($fields);

				        try {
				            $result = $apiInstance->createContact($createContact);
				        } catch (Exception $e) {
				                //print_r($e);
				            echo 'Exception when calling ContactsApi->createContact: ', esc_html($e->getMessage()), PHP_EOL;
				        }

					}
				}
				
			}
		}

	}

}