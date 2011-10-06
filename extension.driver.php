<?php

	/**
	 * Extension driver
	 *
	 * @package Email Amazon SES extension
	 * @author Nick Dunn
	 */
	Class extension_Email_Amazon_Ses extends Extension{

		/**
		 * Extension information
		 */
		public function about(){
			return array(
				'name'         => 'Email Gateway: Amazon Simple Email Service',
				'version'      => '1.0',
				'release-date' => '2011-03-09',
				'author' => array(
					'name' => 'Nick Dunn',
					'website' => 'https://nick-dunn.co.uk',
				)
			);
		}

		/**
		 * Function to be executed on uninstallation
		 */
		public function uninstall(){
			/**
			 * preferences are defined in the email gateway class,
			 * but removing upon uninstallation must be handled here;
			 */
			Symphony::Configuration()->remove('email_amazon_ses');
			Administration::instance()->saveConfig();
			return TRUE;
		}

	}
