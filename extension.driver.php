<?php

/**
 * Extension driver
 *
 * @package Email Amazon SES extension
 * @author Nick Dunn
 */
class extension_email_aws_ses extends Extension
{
    /**
     * Function to be executed on uninstallation
     */
    public function uninstall()
    {
        /**
         * preferences are defined in the email gateway class,
         * but removing upon uninstallation must be handled here;
         */
        Symphony::Configuration()->remove('email_aws_ses');
        Symphony::Configuration()->write();
        return true;
    }
}
