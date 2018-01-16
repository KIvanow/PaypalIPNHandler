# PayPalIPNHandler

PayPal Instant Payment Notifications are the almost ideal way to implement subscriptions for services when you live in countries that are not yet supported by [Stripe](https://stripe.com/), [Braintree](https://www.braintreepayments.com/en-bg?partner_source=BG_DT_SEA_GGL_TXT_RES_DEV_CPC_GW_YBR&gclid=CjwKCAiA4vbSBRBNEiwAMorER0eEfOGlNm3eVAS0ELbkimEDBxk0jxjd8T3SFS_OdW-Jj_Afw5OuIxoCHTIQAvD_BwE&gclsrc=aw.ds&dclid=CISN6uTt3NgCFUQC0wodo_gHXg&referrer=https%3A%2F%2Fwww.google.bg%2F) or for some other reason you can not use something else.

Documentation about Instant Payment Notifications can be found [here](https://developer.paypal.com/docs/classic/products/instant-payment-notification/)

You can track the history of your notifications [here](https://www.paypal.com/bg/cgi-bin/webscr?cmd=_display-ipns-history#). Once notification is send and received, you can resend it over and over for testing purposes.

It took me some time to tackle all of the documentation and messages from PayPal.

## Here is a simplified solution.
There are 2 sql tables - subscriptionCancelations and subscriptionUpdates.
Everytime user cancels the subscription, or the subscription is canceled because of a payment error, update is inserted in subscriptionCancelations. Everytime the user pays for your product, update is inserted in subscriptionUpdates and all of his cancelations are soft deleted.
To check if the user has an active subscription see if there are subscriptionUpdates with user email and if there are no subscriptionCancelations with the same email.

The fields in the tables can be changed for your needs.

##Files in the repo
 * PaypalIPNHandler.php - Basic controller, containing the logic
   - updateSubscription
   - cancelSubscription
   - isSubscriptionActive
   - receiveIPN - Function that expects the $_POST object you have received from PayPal
   - utility function (you can delete them if you want)
     - sendCancelationEmail 
     - sendPaymentProblemEmail
     - sendThankYouEmail
     - sendSubscriptionProblemEmail
 * PaypalIPNHandlerModel.php - Basic Model to contain the DB logic
   - getUserByEmail
   - isSubscriptionActive
   - addCancelation
   - removeCancelations
   - addSubscriptionUpdate
 * PaypalIPN.php - Paypal IPN class
 * DBPDO.php - Simple DB connection class
 * subscriptionCancelations.sql and subscriptionUpdates.sql - sql files to set up the two tables, mentioned earlier
 
 
