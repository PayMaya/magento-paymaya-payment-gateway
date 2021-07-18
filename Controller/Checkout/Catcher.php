<?php

namespace PayMayaNexGen\Payment\Controller\Checkout;

class Catcher extends \Magento\Framework\App\Action\Action
{
    const CATCH_TYPE_SUCCESS = 'success';
    const CATCH_TYPE_FAIL = 'fail';
    const CATCH_TYPE_CANCEL = 'cancel';

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \PayMayaNexGen\Payment\Api\PayMayaClient $client,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->checkoutHelper = $checkoutHelper;
        $this->client = $client;
        $this->request = $request;
        $this->order = $order;
        $this->logger = $logger;
    }

    public function execute()
    {
            $catchType = $this->request->getParam('type');

        switch ($catchType) {
            case self::CATCH_TYPE_SUCCESS: {
                $session = $this->checkoutHelper->getCheckout();
                $incrementId = $session->getLastRealOrderId();
                $order = $this->order->loadByIncrementId($incrementId);

                if (!$order->getId()) {
                    $this->backToCart('No order for processing found');
                } else {
                    $this->checkoutHelper->getCheckout()->getQuote()->setIsActive(false)->save();
                    $this->_redirect('checkout/onepage/success');
                }

                break;
            }

            case self::CATCH_TYPE_FAIL: {
                $this->backToCart('Something has gone wrong with your payment. Please contact merchant.');
            }

            case self::CATCH_TYPE_CANCEL: {
                $this->backToCart();
            }
        }
    }

    public function backToCart($errorMessage = null) {
        $this->checkoutHelper->getCheckout()->restoreQuote();

        if ($errorMessage) {
            $this->messageManager->addErrorMessage(__($errorMessage));
        }

        $this->_redirect('checkout/cart');
    }
}
