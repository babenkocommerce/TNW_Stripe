<?php
/**
 * TNW_Stripe extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the OSL 3.0 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * @category  TNW
 * @package   TNW_Stripe
 * @copyright Copyright (c) 2017-2018
 * @license   Open Software License (OSL 3.0)
 */
namespace TNW\Stripe\Gateway\Request;

use TNW\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use TNW\Stripe\Helper\Payment\Formatter;
use TNW\Stripe\Gateway\Config\Config;

class PaymentDataBuilder implements BuilderInterface
{
    use Formatter;
  
    const AMOUNT = 'amount';
    const CURRENCY = 'currency';
    const DESCRIPTION = 'description';
    const CAPTURE = 'capture';
    const RECEIPT_EMAIL = 'receipt_email';
    const CONFIRM = 'confirm';
    const CONFIRMATION_METHOD = 'confirmation_method';
    


    /** @var SubjectReader  */
    private $subjectReader;

    /** @var Config  */
    private $config;

    /**
     * PaymentDataBuilder constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader,
        Config $config
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * @param array $subject
     * @return array
     */
    public function build(array $subject)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);
        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();

        $result = [
            self::AMOUNT => $this->formatPrice($this->subjectReader->readAmount($subject)),
            self::DESCRIPTION => $order->getOrderIncrementId(),
            self::CURRENCY => $order->getCurrencyCode(),
            self::CONFIRM => 'true',
            self::CONFIRMATION_METHOD => 'manual'
        ];

        if ($this->config->isReceiptEmailEnabled()) {
            $result[self::RECEIPT_EMAIL] = $payment->getOrder()->getCustomerEmail();
        }

        return $result;
    }
}
