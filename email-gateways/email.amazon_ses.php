<?php

	require_once(TOOLKIT . '/class.emailgateway.php');
	require_once(TOOLKIT . '/class.emailhelper.php');

	Class Amazon_SesGateway extends EmailGateway{

		protected $_api_key;

		public function __construct(){
			require_once(EXTENSIONS . '/email_aws_ses/lib/aws-sdk-for-php/sdk.class.php');
			require_once(EXTENSIONS . '/email_aws_ses/lib/aws-sdk-for-php/services/ses.class.php');
			parent::__construct();
		}

		public function about(){
			return array(
				'name' => 'Amazon Simple Email Service',
			);
		}

		public function send(){
			
			try {
				
				$from_email = Symphony::Configuration()->get('from_address', 'email_aws_ses');
				$this->setSenderEmailAddress($from_email);
				
				$from_name = Symphony::Configuration()->get('from_name', 'email_aws_ses');
				$this->setSenderName($from_name);
				
				$this->validate();
				
				// build from address
				if (empty($from_name)) {
					$from = $from_email;
				}
				else {
					$from = $from_name . ' <' . $from_email . '>';
				}
				
				// build to addresses
				$to = array();
				foreach($this->_recipients as $name => $address) {
					if (is_numeric($name)) {
						$to[] = $address;
					}
					else {
						$to[] = $name . ' <' . $address . '>';
					}
				}
				
				// build reply-to addresses
				$reply_to = array();
				if($this->_reply_to_email_address){
					if($this->_reply_to_name){
						$reply_to[] = $this->_reply_to_name . ' <' . $this->_reply_to_email_address . '>';
					}
					else{
						$reply_to[] = $this->_reply_to_email_address;
					}
				}
				
				// build extra headers
				$cc = $bcc = array();
				foreach($this->_header_fields as $name => $body) {
					if (strtolower($name) == 'cc') {
						$cc[] = $body;
					}
					else if (strtolower($name) == 'bcc') {
						$bcc[] = $body;
					}
					else {
						//$headers[] = array($name, $body);
					}
				}
				
				// compile destination object
				$destination = array(
					'ToAddresses' => $to
				);
				if(count($cc) > 0) $destination['CcAddresses'] = $cc;
				if(count($bcc) > 0) $destination['BccAddresses'] = $bcc;
				
				// compile message object
				$message = array(
					'Subject' => array('Data' => $this->_subject),
					'Body' => array(
						'Text' => array('Data' => $this->_text_plain)
					),
				);
				// only set HTML body if it exists
				if(!empty($this->_text_html)) $message['Body']['Html'] = array('Data' => $this->_text_html);
				
				// compile extra options if they exist
				$opt = array();
				if(count($reply_to) > 0) $opt['ReplyToAddresses'] = $reply_to;
				if(count($opt) == 0) $opt = NULL;
				
				// create new wrapper with API keys
				$amazon_ses = new AmazonSES(
					Symphony::Configuration()->get('aws_key', 'email_aws_ses'),
					Symphony::Configuration()->get('aws_secret_key', 'email_aws_ses')
				);
				
				// send
				$response = $amazon_ses->send_email($from, $destination, $message, $opt);
				
				// handle bad responses (200 == OK)
				if($response->status != 200) {
					throw new EmailGatewayException(
						sprintf(
							'%s (%s: %s)',
							$response->body->Error->Message,
							$response->body->Error->Type,
							$response->body->Error->Code
						)
					);
				}
				
			}
			catch (Exception $e) {
				throw new EmailGatewayException($e->getMessage());
			}
			
			return TRUE;
			
		}

		/**
		 * The preferences to add to the preferences pane in the admin area.
		 *
		 * @return XMLElement
		 */
		public function getPreferencesPane(){

			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings pickable');
			$group->setAttribute('id', 'amazon_ses');
			$group->appendChild(new XMLElement('legend', __('Amazon Simple Email Service Email Gateway')));

			$div = new XMLElement('div');
			$div->setAttribute('class', 'group');
			$label = Widget::Label(__('From Name'));
			$label->appendChild(Widget::Input('settings[email_aws_ses][from_name]', Symphony::Configuration()->get('from_name', 'email_aws_ses')));
			$div->appendChild($label);
			
			$label = Widget::Label(__('From Address'));
			$label->appendChild(Widget::Input('settings[email_aws_ses][from_address]', Symphony::Configuration()->get('from_address', 'email_aws_ses')));
			$div->appendChild($label);
			$group->appendChild($div);

			$div = new XMLElement('div');
			$div->setAttribute('class', 'group');
			$label = Widget::Label(__('Amazon Web Services Key'));
			$label->appendChild(Widget::Input('settings[email_aws_ses][aws_key]', Symphony::Configuration()->get('aws_key', 'email_aws_ses')));
			$div->appendChild($label);

			$label = Widget::Label(__('Amazon Web Services Secret Key'));
			$label->appendChild(Widget::Input('settings[email_aws_ses][aws_secret_key]', Symphony::Configuration()->get('aws_secret_key', 'email_aws_ses')));
			$div->appendChild($label);
			$group->appendChild($div);

			return $group;
		}
	}
