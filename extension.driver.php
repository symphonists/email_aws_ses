<?php

	/**
	 * Extension driver
	 *
	 * @package Email Amazon SES extension
	 * @author Nick Dunn
	 */
	Class extension_email_aws_ses extends Extension{
        
		/**
		 * Function to be executed on uninstallation
		 */
		public function uninstall(){
			/**
			 * preferences are defined in the email gateway class,
			 * but removing upon uninstallation must be handled here;
			 */
			Symphony::Configuration()->remove('email_aws_ses');
			Administration::instance()->saveConfig();
			return TRUE;
		}

	}
