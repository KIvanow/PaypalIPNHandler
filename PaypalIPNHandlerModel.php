<?php
    define('DATABASE_NAME', '');

    define('DATABASE_USER', '');
            
    define('DATABASE_PASS', '}');
            
    define('DATABASE_HOST', '');

    require_once './DBPDO.php';
           
    
    class PaypalIPNHandlerModel {
        function __construct() {
     	       $this->DB = new DBPDO();                        
        }

        public function getUserByEmail( $email ){
            
        }

        public function isSubscriptionActive( $paymentMail ){                                        
            $query = $this->DB->fetchAll('SELECT * FROM subscriptionCancelations WHERE deleted=0 AND paymentMail="'. $paymentMail .'"');
                      
            
            if( count( $query ) > 0 ){
                return false;
            } else {
                $query = $this->DB->fetchAll( 'SELECT * FROM subscriptionUpdates WHERE paymentMail="'. $paymentMail . '"');
                
                if( count( $query ) > 0 ){
                    return true;
                } else {
                    return false;
                }
            }
        }

        public function addCancelation( $paymentMail, $transactionId ){            
            $this->DB->execute( 'INSERT INTO subscriptionCancelations (paymentMail, transactionId, deleted) VALUES ("'.$paymentMail.'", "'. $transactionId .'", 0 )');
        }

        public function removeCancelations( $paymentMail ){
            $this->DB->execute( 'UPDATE subscriptionCancelations SET deleted=1 WHERE paymentMail="'. $paymentMail .'"');
        }

        public function addSubscriptionUpdate( 
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
        ){
            $this->DB->execute( 
                'INSERT INTO subscriptionUpdates 
                (
                    paymentMail, 
                    transactionId, 
                    firstName, 
                    lastName, 
                    userCountry, 
                    userCity, 
                    transactionType, 
                    transactionDate, 
                    subscriptionType, 
                    ipnTrackId, 
                    itemName
                ) 
                VALUES ("'
                    .$paymentMail.'", "'
                    .$transactionId.'", "'
                    .$firstName.'", "'
                    .$lastName.'", "'
                    .$userCountry.'", "'
                    .$userCity.'", "'
                    .$transactionType.'", "'
                    .$transactionDate.'", "'
                    .$subscriptionType.'", "'
                    .$ipnTrackId.'", "'
                    .$itemName
                .'" )'
            );
        }       
    }
?>
