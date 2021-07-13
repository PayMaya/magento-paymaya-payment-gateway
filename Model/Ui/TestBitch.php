<?php


namespace PayMayaNexGen\Payment\Model\Ui;

use Magento\Framework\Exception\LocalizedException;

abstract class TestBitch extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $type = '';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = '';

    /**
     * @var string
     */
    protected $_infoBlockType = 'PayMayaNexGen\Payment\Block\Info';

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_isGateway = true;
    protected $_isInitializeNeeded = true;
    protected $_canUseInternal = false;
    protected $_canFetchTransactionInfo = true;
    protected $_canUseForMultishipping = true;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \PayMaya\Payment\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Payment\Model\Method\Logger
     */
    protected $logger;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger2;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Helper\Errors
     */
    protected $errorHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $_responseFactory;


    /**
     * Constructor
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magento\Framework\UrlInterface
     * @param \Magento\Framework\App\ResponseFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        array $data = []
    )
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/system.log');
        $logger2 = new \Zend\Log\Logger();
        $logger2->addWriter($writer);

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;

        $this->logger = $logger;
        $this->logger2 = $logger2;
        $this->request = $request;
        $this->checkoutHelper = $checkoutHelper;
        $this->scopeConfig = $scopeConfig;
        $this->_url = $url;
        $this->_responseFactory = $responseFactory;
    }

    /**
     * Check whether payment method can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return true;
    }

    public function getMetadata()
    {
        return [
            'Order #' => $this->order->getIncrementId()
        ];
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        //echo "<pre>paymentmethods::assigndata";
        parent::assignData($data);

        return $this;
    }

    public function getBillingDetails()
    {
        //echo "<pre>paymentmethods::getbillingdetails";
        $address = $this->order->getBillingAddress();

        return [
            'address' => [
                'line1' => $address->getStreetLine(1),
                'line2' => $address->getStreetLine(2),
                'city' => $address->getCity(),
                'state' => $address->getRegion(),
                'postal_code' => $address->getPostcode(),
                'country' => $address->getCountryId()
            ],
            'name' => $this->order->getCustomerName(),
            'email' => $address->getEmail(),
            'phone' => $address->getTelephone()
        ];
    }

    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @api
     */
    public function initialize($paymentAction, $stateObject)
    {
        //echo "<pre> 2: paymentmentods::initialize";
        /**
         * @var \Magento\Quote\Model\Quote\Payment $info
         */
        $info = $this->getInfoInstance();
        //$additionalInfo = $info->getAdditionalInformation();


        /*if (!isset($additionalInfo['paymentMethodId'])) {
            throw new \Exception('Unable to retrieve paymentMethodId');
        }*/

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $this->order = $info->getOrder();
        //echo "<br/>this order: ";
        //print_r($info->getOrder()->getId());

        // Prepare Order
        $this->order->setCanSendNewEmailFlag(false);

        return $this;
    }

    public function getId()
    {
        return $this->_code;
    }
}
