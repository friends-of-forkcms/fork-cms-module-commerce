<?php

namespace Frontend\Modules\Commerce\Actions;

use Common\Core\Cookie;
use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Form as FrontendForm;
use Frontend\Core\Engine\Language as FL;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Modules\Commerce\Engine\Model as FrontendCommerceModel;

/**
 * This is the personal-data-action (default), it will display a personal data form.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class PersonalData extends FrontendBaseBlock
{
    /**
     * The url to checkout page.
     */
    private string $checkoutUrl;

    /**
     * The order id in cookie.
     */
    private int $cookieOrderId;

    /**
     * Execute the action.
     */
    public function execute(): void
    {
        parent::execute();

        $this->loadTemplate();
        $this->getData();

        $this->loadForm();
        $this->validateForm();

        $this->parse();
    }

    /**
     * Load the data, don't forget to validate the incoming data.
     */
    private function getData(): void
    {
        // get order
        $this->cookieOrderId = $this->get('fork.cookie')->get('order_id');

        // set checkout url
        $this->checkoutUrl = FrontendNavigation::getURLForBlock('Commerce', 'Checkout');
    }

    /**
     * Load the form.
     */
    private function loadForm()
    {
        // create form
        $this->frm = new FrontendForm('personalDataForm');

        // init vars
        /** @var Cookie $cookie */
        $cookie = $this->get('fork.cookie');
        $email = $cookie->get('email');
        $fname = $cookie->get('fname');
        $lname = $cookie->get('lname');
        $address = $cookie->get('address');
        $hnumber = $cookie->get('hnumber');
        $postal = $cookie->get('postal');
        $hometown = $cookie->get('hometown');

        // create elements
        $this->frm->addText('email', $email)->setAttributes(['required' => null, 'type' => 'email']);
        $this->frm->addText('fname', $fname, null)->setAttributes(['required' => null]);
        $this->frm->addText('lname', $lname, null)->setAttributes(['required' => null]);
        $this->frm->addText('address', $address, null)->setAttributes(['required' => null]);
        $this->frm->addText('hnumber', $hnumber, null)->setAttributes(['required' => null]);
        $this->frm->addText('postal', $postal, null)->setAttributes(['required' => null]);
        $this->frm->addText('hometown', $hometown, null)->setAttributes(['required' => null]);

        $this->frm->addTextarea('message');
    }

    /**
     * Validate the form.
     */
    private function validateForm()
    {
        // is the form submitted
        if ($this->frm->isSubmitted()) {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

            // validate required fields
            $this->frm->getField('email')->isEmail(FL::err('EmailIsRequired'));
            $this->frm->getField('fname')->isFilled(FL::err('MessageIsRequired'));
            $this->frm->getField('lname')->isFilled(FL::err('MessageIsRequired'));
            $this->frm->getField('address')->isFilled(FL::err('MessageIsRequired'));
            $this->frm->getField('hnumber')->isFilled(FL::err('MessageIsRequired'));
            $this->frm->getField('postal')->isFilled(FL::err('MessageIsRequired'));
            $this->frm->getField('hometown')->isFilled(FL::err('MessageIsRequired'));

            // correct?
            if ($this->frm->isCorrect()) {
                // build array
                $order['email'] = $this->frm->getField('email')->getValue();
                $order['fname'] = $this->frm->getField('fname')->getValue();
                $order['lname'] = $this->frm->getField('lname')->getValue();
                $order['address'] = $this->frm->getField('address')->getValue();
                $order['hnumber'] = $this->frm->getField('hnumber')->getValue();
                $order['postal'] = $this->frm->getField('postal')->getValue();
                $order['hometown'] = $this->frm->getField('hometown')->getValue();
                $order['status'] = 'moderation';

                // insert values in database
                FrontendCommerceModel::updateOrder($order, $this->cookieOrderId);

                // delete cookie
                $argument = 'order_id';
                unset($_COOKIE[(string) $argument]);
                setcookie((string) $argument, null, 1, '/');

                // set cookies person --> optional
                Cookie::set('email', $order['email']);
                Cookie::set('fname', $order['fname']);
                Cookie::set('lname', $order['lname']);
                Cookie::set('address', $order['address']);
                Cookie::set('hnumber', $order['hnumber']);
                Cookie::set('postal', $order['postal']);
                Cookie::set('hometown', $order['hometown']);
                Cookie::set('status', $order['status']);

                // trigger event
                FrontendModel::triggerEvent('Commerce', 'after_add_order', ['order' => $order]);

                $url = FrontendNavigation::getURLForBlock('Commerce', 'OrderReceived');
                $this->redirect($url);
            }
        }
    }

    /**
     * Parse the page.
     */
    protected function parse()
    {
        // add css
        $this->header->addCSS('/src/Frontend/Modules/'.$this->getModule().'/Layout/Css/Commerce.css');

        // url to checkout page
        $this->tpl->assign('checkoutUrl', $this->checkoutUrl);

        // parse the form
        $this->frm->parse($this->tpl);
    }
}
