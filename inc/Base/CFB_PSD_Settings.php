<?php
namespace CFB_PSD\Base;


class CFB_PSD_Settings{

	public function __construct(){

	}

	public function register(){
        add_filter( 'wpcf7_editor_panels', array($this, 'cfb_psd_tab'),10,1);
        add_action('save_post_wpcf7_contact_form', array($this, 'save_contact_form_seven_ac_settings'));
	}

	public function cfb_psd_tab($panels){
		$panels['brevo-panel'] = array( 
            'title' => __( 'Brevo', 'contactform-to-brevo' ),
            'callback' => array($this, 'cfb_psd_tab_callback')
        );
        return $panels;
	}

	public function save_contact_form_seven_ac_settings($post_id){
		if ( ! isset( $_POST['cfb_psd_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['cfb_psd_nonce_field'])), 'cfb_psd_nonce' ) ) {
		   echo esc_html__('Sorry, your nonce did not verify.','contactform-to-brevo');
		   exit;
		} else {
		   // process form data

			$cfb_psd_fields = isset( $_REQUEST['cfb_psd_fields']) ? (array) maybe_unserialize($_REQUEST['cfb_psd_fields']) : array();

			//sanitization here

			update_post_meta($post_id,'cfb_psd_fields', $cfb_psd_fields);
			// enable
			$cfb_psd_enable = isset($_POST['cfb_psd_enable'])?sanitize_key($_POST['cfb_psd_enable']):'';
			update_post_meta($post_id,'cfb_psd_enable',sanitize_key($cfb_psd_enable));


			if(isset($_POST['cfb_psd_update_existing'])){
				update_post_meta($post_id,'cfb_psd_update_existing',sanitize_text_field('yes'));
			}else{
				update_post_meta($post_id,'cfb_psd_update_existing',sanitize_text_field('no'));
			}

			// Brevo
			$cfb['url']=isset($_POST['cfb_psd_url']) ? trim(sanitize_text_field($_POST['cfb_psd_url'])) : '';
			$cfb['api_key']=isset($_POST['cfb_psd_api_key']) ? trim(sanitize_text_field($_POST['cfb_psd_api_key'])) : '';
			$cfb['list_id']=isset($_POST['cfb_psd_list_id']) ? trim(absint($_POST['cfb_psd_list_id'])) : '';
			update_post_meta($post_id,'cfb_psd_credentials',$cfb);
		}
	}
	public function cfb_psd_tab_callback(){
		global $post;
        $cf7 = \WPCF7_ContactForm::get_instance(absint($_GET['post']));
        $tags = '';
        if(!empty($cf7)){
        	$tags = $cf7->collect_mail_tags();	
        }
        
        $post_id = isset($_GET['post']) ? absint($_GET['post']) : '';

        $enable = get_post_meta($post_id,'cfb_psd_enable',true);
        $update_existing = get_post_meta($post_id,'cfb_psd_update_existing',true);

        ?>
        <div id="cfb-psd-settings">
    	<?php wp_nonce_field( 'cfb_psd_nonce', 'cfb_psd_nonce_field' ); ?>
        <h2><?php echo esc_html__("Brevo Setttings","contactform-to-brevo"); ?></h2>

        <h3><label for="cfb_psd_enable"><input type="checkbox" name="cfb_psd_enable" id="cf7_email_subscription" value="yes" <?php echo (($enable=='yes')?'checked':''); ?> ><?php echo esc_html__("Enable Brevo for this form.","contactform-to-brevo"); ?></label></h3><hr>
	    <div class="cfb-psd-settings-tab clearfix">
	    	<ul class="tab-wrap clearfix">
	    		<li class="tab active" data-id="general">
	    			<?php echo esc_html__('General Settings','contactform-to-brevo'); ?>
	    		</li>
	    		<li class="tab" data-id="form-fields">
	    		    <?php echo esc_html__('Form Fields','contactform-to-brevo'); ?>
	    		</li>
	    		<li class="tab" data-id="form-pro" style="background:#05c305; color:#fff;" >
	    		    <?php echo esc_html__('Pro Version','contactform-to-brevo'); ?>
	    		</li>
	    	</ul>
	    </div>
        <div id="cfb_psd_enable">

        <div class="cfb-psd-main-settings tab-pane general general-settings-section">
	        <h1><?php echo esc_html__("Brevo Settings","contactform-to-brevo"); ?></h1>
	        <?php
	        $cfb = get_post_meta($post_id,'cfb_psd_credentials',true);
	        ?>
	        <p>
	        	<label for="cfb_psd_enable"><input type="checkbox" name="cfb_psd_update_existing" id="cfb_psd_update_existing" value="yes" <?php echo (($update_existing=='yes')?'checked':''); ?> ><?php echo esc_html__("Update existing contacts","contactform-to-brevo"); ?></label>
	        </p>
	        <p>
	        	<label for="cfb_psd_api_key"><?php echo esc_html__("Brevo API KEY","contactform-to-brevo"); ?></label>
	        	<input type="text" name="cfb_psd_api_key" class="widefat" id="cfb_psd_api_key" value="<?php echo (isset($cfb['api_key']) ?  esc_attr($cfb['api_key']) : '' ); ?>">
	        	<?php /* translators: %s: links */ ?>
	        	<em><?php echo sprintf(esc_html__( 'You can get API key like %s.', 'contactform-to-brevo' ),'<a href="https://help.brevo.com/hc/en-us/articles/209467485-Create-and-manage-your-API-keys" target="_blank">this</a>');?></em>
	        </p>

            <p>
            	<label for="cfb_psd_list_id"><?php echo esc_html__("Brevo Email List ID","contactform-to-brevo"); ?></label>
        		<input type="number" name="cfb_psd_list_id" class="widefat" id="cfb_psd_list_id" value="<?php echo (isset($cfb['list_id']) ?  esc_attr($cfb['list_id']) : '' ); ?>">
            </p>
            <p>
		    <div class="contacts-meta-section-wrapper">
				<span class="add-button table-contacts"><a href="javascript:void(0)" class="docopy-table-list button"><?php esc_html_e('Add List','contactform-to-brevo'); ?></a></span>
		    </div>
		    <em class="pro"><?php echo esc_html__("Available in Premium Version.","contactform-to-brevo"); ?>
		    	<a href="https://www.linkedin.com/in/sagar-giri-5bb771130/" target="_blank"><?php esc_html_e('Get Pro Version','contactform-to-brevo'); ?></a>
		    </em>
		    </p>
        </div><!--general Settings -->
        <div class="cfb-psd-main-settings tab-pane form-fields clearfix" style="display:none">
            <?php
	        if(!empty($tags)){
	        	?>
	        	<h1><?php echo esc_html__("Select form fields","contactform-to-brevo"); ?></h1>
	            <?php	
	            $fields = get_post_meta($post_id,'cfb_psd_fields',true);
	            // email field
	            ?>
	            <div class="form-fields">
		            <label for="cfb_psd_email" class="fleft"><?php echo esc_html__("Email Field* : ","contactform-to-brevo"); ?></label>
		            <select name="cfb_psd_fields[cfb_psd_email]" id="cfb_psd_email" class="fleft">
		            <option value=""><?php echo esc_html__("Select field name for email","contactform-to-brevo"); ?></option>
		            <?php
		            foreach ($tags as $key => $tag) {
		                 $selected='';
		                if(isset($fields['cfb_psd_email']) && $fields['cfb_psd_email']==$tag)
		                    $selected='selected';

		                echo '<option value="'.esc_attr($tag).'" '.esc_attr($selected).'>'.esc_attr($tag).'</option>';
		            }
		            ?>
		            </select>
	            </div>
	            <p><em><?php echo esc_html__("Following fields are optional select if available in form, otherwise leave unselected. Only email field is required.","contactform-to-brevo"); ?></em></p>
                <div class="form-fields">
	            	<label for="cfb_psd_first_name" class="fleft"><?php echo esc_html__("First Name Field : ","contactform-to-brevo"); ?></label>
		            <select name="cfb_psd_fields[cfb_psd_first_name]" id="cfb_psd_first_name" class="fleft">
		            <option value=""><?php echo esc_html__("Select field name for first name","contactform-to-brevo"); ?></option>
		            <?php
		            foreach ($tags as $key => $tag) {
		                $selected='';
		                if(isset($fields['cfb_psd_first_name']) && $fields['cfb_psd_first_name']==$tag)
		                    $selected='selected';

		                echo '<option value="'.esc_attr($tag).'" '.esc_attr($selected).'>'.esc_attr($tag).'</option>';
		            }
		            ?>
		            </select>
                </div>
                <div class="form-fields">
		            <label for="cfb_psd_last_name" class="fleft"><?php echo esc_html__("Last Name Field : ","contactform-to-brevo"); ?></label>
		            <select name="cfb_psd_fields[cfb_psd_last_name]" id="cfb_psd_last_name" class="fleft">
		            <option value=""><?php echo esc_html__("Select field name for last name","contactform-to-brevo"); ?></option>
		            <?php
		            foreach ($tags as $key => $tag) {
		                $selected='';
		                if(isset($fields['cfb_psd_last_name']) && $fields['cfb_psd_last_name']==$tag)
		                    $selected='selected';

		                echo '<option value="'.esc_attr($tag).'" '.esc_attr($selected).'>'.esc_attr($tag).'</option>';
		            }
		            ?>
		           </select>
               </div>
               <div class="form-fields">
		            <label for="cfb_psd_phone" class="fleft"><?php echo esc_html__("Phone Number Field : ","contactform-to-brevo"); ?></label>
		            <select name="cfb_psd_fields[cfb_psd_phone]" id="cfb_psd_phone" class="fleft">
		            <option value=""><?php echo esc_html__("Select field name for Phone","contactform-to-brevo"); ?></option>
		            <?php
		            foreach ($tags as $key => $tag) {
		                $selected='';
		                if(isset($fields['cfb_psd_phone']) && $fields['cfb_psd_phone']==$tag)
		                    $selected='selected';

		                echo '<option value="'.esc_attr($tag).'" '.esc_attr($selected).'>'.esc_attr($tag).'</option>';
		            }
		            ?>
		            </select>
	            </div>
	            <p><hr></p>

	            <div class="form-fields">
					<label for="cfb_psd_gdpr" class="fleft"><?php echo esc_html__("Acceptance Field for GDPR compliance : ","contactform-to-brevo"); ?></label>
		            <select name="cfb_psd_fields[cfb_psd_gdpr]" id="cfb_psd_gdpr" class="fleft">
			            <option value=""><?php echo esc_html__("Choose Field","contactform-to-brevo"); ?></option>
		            <?php
		            foreach ($tags as $key => $tag) {
		                $selected='';
		                if(isset($fields['cfb_psd_gdpr']) && $fields['cfb_psd_gdpr']==$tag)
		                    $selected='selected';

		                echo '<option value="'.esc_attr($tag).'" '.esc_attr($selected).'>'.esc_attr($tag).'</option>';
		            }
		            ?>
		            </select>
				</div>
				<p><hr></p>
                <label><?php echo esc_html__("Extra Fields","contactform-to-brevo"); ?></label>
                <div class="form-fields">
				<div class="single-contact wp-dynamic">
					<div class="single-section-title clear">
						<h4 class="contact-title fleft"><?php echo esc_html__("Field 1 : ","contactform-to-brevo"); ?></h4>
				        <div class="contact-inputfield fleft">
				        	<em>Field Name</em>
				        	<input type="text" name="cfb_psd_fields[extra-fields][name][1]" value="<?php echo esc_attr($fields['extra-fields']['name']['1'])?>" placeholder="FIELDNAME" value="" />
				        </div>
				        <div class="contact-inputfield fleft clearfix">
				        	<em>Field Value</em>
				        	<input type="text" name="cfb_psd_fields[extra-fields][value][1]" value="<?php echo esc_attr($fields['extra-fields']['value']['1'])?>" placeholder="your-name" value="">
				        </div>
				    </div>
				</div>
				<div class="single-contact wp-dynamic">
					<div class="single-section-title clear">
						<h4 class="contact-title fleft"><?php echo esc_html__("Field 2 : ","contactform-to-brevo"); ?></h4>
				        <div class="contact-inputfield fleft">
				        	<em>Field Name</em>
				        	<input type="text" name="cfb_psd_fields[extra-fields][name][2]" placeholder="FIELDNAME" value="<?php echo esc_attr($fields['extra-fields']['name']['2'])?>" />
				        </div>
				        <div class="contact-inputfield fleft clearfix">
				        	<em>Field Value</em>
				        	<input type="text" name="cfb_psd_fields[extra-fields][value][2]" placeholder="your-name" value="<?php echo esc_attr($fields['extra-fields']['value']['2'])?>">
				        </div>
				    </div>
				    <em><?php echo esc_html__("Field Name needs to be your brevo custom field name and Field Value can be either text or contact form 7 name field.","contactform-to-brevo"); ?></em>
				</div>
				</div>
                <div class="form-fields">
				    <div class="contacts-meta-section-wrapper">
				    	<span class="add-button table-contacts"><a href="javascript:void(0)" class="docopy-table-contact button"><?php esc_html_e('Add Field','contactform-to-brevo'); ?></a></span>
				    	<em class="pro"><?php echo esc_html__("Available in Premium Version.","contactform-to-brevo"); ?>
				    		<a href="https://www.linkedin.com/in/sagar-giri-5bb771130/" target="_blank"><?php esc_html_e('Get Pro Version','contactform-to-brevo'); ?></a>
				    	</em>
				    </div>
			    </div>
				<p><hr></p>
                <label><?php echo esc_html__("Add Conditions","contactform-to-brevo"); ?></label>
                <div class="form-fields">
				    <div class="contacts-meta-section-wrapper">
				    	<span class="add-button table-contacts"><a href="javascript:void(0)" class="docopy-table-contact button"><?php esc_html_e('Add Condition','contactform-to-brevo'); ?></a></span>
				    	<em class="pro"><?php echo esc_html__("Available in Premium Version.","contactform-to-brevo"); ?>
				    		<a href="https://www.linkedin.com/in/sagar-giri-5bb771130/" target="_blank"><?php esc_html_e('Get Pro Version','contactform-to-brevo'); ?></a>
				    	</em>
				    </div>
			    </div>
	           <?php
	        }
	        else{
	            echo esc_html__('Please Add Contact Form Tags First!', 'contactform-to-brevo');
	        }
	        ?>
	        <hr>
        </div><!--Form Fields -->
        <div class="cfb-psd-main-settings tab-pane form-pro clearfix" style="display:none">
        	<div class="pro-features">
        		<h2><?php esc_html_e('Pro Features','contactform-to-brevo'); ?></h2>
        		<hr>
        		<ul>
        			<li><?php esc_html_e('Adds “Contact Form 7” data to “Brevo”.','contactform-to-brevo');?></li>
					<li><?php esc_html_e('Adds Contacts to unlimited List ID\'s.','contactform-to-brevo'); ?></li>
					<li><?php esc_html_e('Option to select “Contact Form 7” fields for “Brevo” list.','contactform-to-brevo'); ?></li>
					<li><?php esc_html_e('Option to add Unlimited Fields.','contactform-to-brevo'); ?></li>
					<li><?php esc_html_e('Option to add Tags','contactform-to-brevo'); ?></li>
					<li><?php esc_html_e('Supports Contact Form 7 Special Mail Tags','contactform-to-brevo'); ?></li>
					<li><?php esc_html_e('GDPR acceptance field','contactform-to-brevo'); ?></li>
					<li><?php esc_html_e('Life Time Free Updates & Support.','contactform-to-brevo'); ?></li>
					
					

        		</ul>
        		<a href="https://www.linkedin.com/in/sagar-giri-5bb771130/" class="button-secondary" target="_blank">
        			<?php esc_html_e('Get Pro Version','contactform-to-brevo'); ?>
        		</a>
        	</div>
        	<hr>
        	<h2><?php esc_html_e('Please Spread Your Love With 5 Star Rating.','contactform-to-brevo'); ?></h2>
        	<span><?php esc_html_e('If you are loving our plugin please give us nice rating.','contactform-to-brevo'); ?></span>
        	<a href="https://wordpress.org/support/plugin/contactform-to-brevo/reviews/#new-post" class="button-primary" target="_blank">
        			<?php esc_html_e('Rate Now','contactform-to-brevo'); ?>
        		</a>
        </div><!-- Premium Version -->
        <hr>
        </div>
        </div>
        <?php

	}
}
