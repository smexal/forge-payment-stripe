<?php

namespace Forge\Modules\PaymentStripe;

use \Forge\Core\Abstracts\Module as AbstractModule;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\App\ModifyHandler;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\Logger;
use \Forge\Core\Classes\Settings;
use \Forge\Loader;


class Module extends AbstractModule {

    public function setup() {
        $this->settings = Settings::instance();
        $this->version = '1.0.0';
        $this->id = "forge-payment-stripe";
        $this->name = i('Stripe Payment', 'forge-payment-stripe');
        $this->description = i('Payment Adapter as extension for the forge payment to add Stripe Payment Methods.', 'forge-payment-stripe');
        $this->image = $this->url().'images/module-image.png';
    }

    public function start() {
        /**
         * The "forge-payment" plugin is required
         */
        if(! App::instance()->mm->isActive('forge-payment')) {
            Logger::error('forge-payment Plugin required for forge-payment-stripe');
            return;
        }

        App::instance()->tm->theme->addStyle(MOD_ROOT."forge-payment-stripe/css/forge-payment-stripe.less");

        ModifyHandler::instance()->add(
            'modify_forge_payment_adapters', 
            [$this, 'addStripeAdapter']
        );

        $this->settings();
    }

    public function addStripeAdapter($data) {
        $data[] = '\Forge\Modules\PaymentStripe\Adapter';
        return $data;
    }

    private function settings() {
        if (! Auth::allowed("manage.settings", true)) {
            return;
        }

        $this->settings->registerField(
            Fields::text(array(
                'key' => 'forge-payment-stripe-public-key',
                'label' => i('Stripe Public Key', 'forge-payment-stripe'),
                'hint' => i('Checkout stripe.com for more information.', 'forge-payment-stripe')
            ), Settings::get('forge-payment-stripe-public-key')),
            'forge-payment-stripe-public-key',
            'left',
            'forge-payment'
        );

        $this->settings->registerField(
            Fields::text(array(
                'key' => 'forge-payment-stripe-private-key',
                'label' => i('Stripe Private Key', 'forge-payment-stripe'),
                'hint' => i('Checkout stripe.com for more information.', 'forge-payment-stripe')
            ), Settings::get('forge-payment-stripe-private-key')),
            'forge-payment-stripe-private-key',
            'left',
            'forge-payment'
        );
    }

}

?>
