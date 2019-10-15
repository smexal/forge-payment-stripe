<?php

namespace Forge\Modules\PaymentStripe;

use \Forge\Modules\ForgePayment\Payment;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Localization;
use \Forge\Core\Classes\Mail;
use \Forge\Core\Classes\Settings;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\Utils;

/**
 * Help the Translation Crawler
 * i('stripe', 'forge-payment')
 */

class Adapter {
    public static $id = 'stripe';
    private $orderId = null;
    private $item = null;
    private $payment = null;

    public function __construct($orderId) {
        $this->orderId = $orderId;
        $this->payment = Payment::getOrder($this->orderId);
    }

    public static function payView($parts) {
        require_once(MOD_ROOT.'forge-payment-stripe/library-external/init.php');

        $payment = Payment::getOrder($_GET['order']);
        $payment->setType('stripe');

        $stripe = array(
          "secret_key"      => Settings::get('forge-payment-stripe-private-key'),
          "publishable_key" => Settings::get('forge-payment-stripe-public-key')
        );

        \Stripe\Stripe::setApiKey($stripe['secret_key']);

        $token  = $_POST['stripeToken'];

        $customer = \Stripe\Customer::create(array(
            'email' => App::instance()->user->get('email'),
            'source'  => $token
        ));

        $charge = \Stripe\Charge::create(array(
            'customer' => $customer->id,
            'amount'   => $payment->getTotalAmount(true),
            'currency' => strtolower(Payment::getCurrency())
        ));

        App::instance()->addMessage(i('Your payment has been confirmed.', 'forge-payment'), "success");
        Payment::acceptOrder($_GET['order']);
        if(array_key_exists('redirectSuccess', $_SESSION)) {
            App::instance()->redirect($_SESSION['redirectSuccess']);
        } else {
            App::instance()->redirect(Utils::getUrl(''));
        }
    }

    public function infos() {
        $action = Utils::getUrl(array("pay", "stripe"), true, ['order' => $this->orderId]);
        $image = App::instance()->tm->theme->url()."assets/images/logo.png";
        return array(
            'raw' => 
            '<form action="'.$action.'" method="POST">
                <script
                    src="https://checkout.stripe.com/checkout.js" class="adapter stripe-button"
                    data-key="'.Settings::get('forge-payment-stripe-public-key').'"
                    data-amount="'.$this->payment->getTotalAmount(true).'"
                    data-name="'.Settings::get('title_'.Localization::getCurrentLanguage()).'"
                    data-description=""
                    data-image="'.$image.'"
                    data-locale="auto"
                    data-zip-code="false"
                    data-label="'.i('Pay with Credit Card – by stripe', 'forge-payment-stripe').'"
                    data-currency="'.strtolower(Payment::getCurrency()).'">
                </script>
            </form>'
        );
    }
}

?>
