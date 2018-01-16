<?php
    require_once './PaypalIPNHandlerModel.php';
    
    class PaypalIPNHandler{
        function __construct() {     	 
            $this->PaypalIPNHanlderModel = new PaypalIPNHanlderModel();                        
        }
        
        public function updateSubscription( $paymentData ){
            $paymentMail = $paymentData['payer_email'];
            $transactionId = isset( $paymentData['txn_id'] ) ? $paymentData['txn_id'] : '';
            $firstName = $paymentData[ 'first_name' ];
            $lastName = $paymentData[ 'last_name' ];
            $userCountry = $paymentData[ 'address_country_code' ];
            $userCiy = $paymentData['address_city'];
            $transactionType = isset( $paymentData['txn_type'] ) ? $paymentData['txn_type'] : '';
            $transactionDate = isset( $paymentData['payment_date'] ) ? $paymentData['payment_date'] : '';
            $subscriptionType = $paymentData['option_selection1'];
            $ipnTrackId = $paymentData['ipn_track_id'];
            $itemName = $paymentData['item_name'];

            // check if there is an user with the same email, as the one, used for the payment
            if( count( $this->PaypalIPNHanlderModel->getUserByEmail( $paymentMail ) ) > 0 ){
                $this->PaypalIPNHanlderModel->addSubscriptionUpdate( 
                    $paymentMail,
                    $transactionId,
                    $firstName,
                    $lastName,
                    $userCountry,
                    $userCiy,
                    $transactionType,
                    $transactionDate,
                    $subscriptionType,
                    $ipnTrackId,
                    $itemName
                );

                $this->PaypalIPNHanlderModel->removeCancelations( $paymentMail );

                return true;
            } else {
                return false;
            }
        }

        private function sendCancelationEmail($userEmail, $firstName, $lastName, $notificationData ){
            
        }

        private function cancelSubscription( $paymentData ){
            $paymentMail = $paymentData['payer_email'];
            $transactionId = isset( $paymentData['txn_id'] ) ? $paymentData['txn_id'] : '';	
            $this->PaypalIPNHanlderModel->addCancelation( 
                $paymentMail,
                $transactionId
            );
        }

        public function isSubscriptionActive( $userEmail ){
            return $this->PaypalIPNHanlderModel->isSubscriptionActive($userEmail);
        }

        public function sendPaymentProblemEmail( $userEmail, $firstName, $lastName, $notificationData ){
            
        }

        private function sendThankYouEmail( $userEmail, $firstName, $lastName ){		
        }

        private function sendSubscriptionProblemEmail( $userEmail, $firstName, $lastName ){
            
        }

        public function receiveIPN(){		
            $ipn = new PaypalIPN();

            $verified = $ipn->verifyIPN();
            $data_text = "";

            foreach ($_POST as $key => $value) {
                $data_text .= $key . " = " . $value . "\r\n";
            }

            $ipnType = $_POST['txn_type'];

            $timeStamp = time();
            $humanizedTimestamp = date( "Y-m-d:m-s", $timeStamp );		
            

            switch( $ipnType ){
                case 'subscr_payment':
                case 'recurring_payment':				
                    $subscriptionOK = $this->updateSubscription( $_POST );				
                    if( $subscriptionOK ){					
                        $this->sendThankYouEmail( $_POST['payer_email'], $_POST['first_name'], $_POST['last_name'] );
                    } else {										
                        $this->sendSubscriptionProblemEmail( $_POST['payer_email'], $_POST['first_name'], $_POST['last_name'] );
                    }				
                    break;
                case 'subscr_cancel':
                case 'recurring_payment_profile_cancel':							
                    $this->sendCancelationEmail( $_POST['payer_email'], $_POST['first_name'], $_POST['last_name'], $data_text );
                    $this->cancelSubscription( $_POST );				
                    break;
                case 'subscr_failed':
                case 'recurring_payment_expired':
                case 'recurring_payment_failed':			
                case 'recurring_payment_skipped':
                case 'recurring_payment_suspended':
                case 'recurring_payment_suspended_due_to_max_failed_payment':											
                    $this->sendPaymentProblemEmail( $_POST['payer_email'], $_POST['first_name'], $_POST['last_name'], $data_text );
                    $this->cancelSubscription( $_POST );				
                    break;
                case 'subscr_signup':				
                    break;
                default:				
                    // you can write in a log everything that hasn't been catched by the switch
            }				
        
            // Reply with an empty 200 response to indicate to paypal the IPN was received correctly
            $this->output->set_header('HTTP/1.0 200 OK');
        }
    }

?>