<?php

namespace Iugu\Payment\Gateway\Request;

use Exception;
use Iugu\Payment\Gateway\SubjectReader;
use Iugu\Payment\Helper\IuguHelper;
use Magento\Checkout\Model\Cart;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order\Item;

class ItemsDataBuilder implements BuilderInterface
{
    const ITEMS = 'items';

    const DESCRIPTION = 'description';

    const QTY = 'quantity';

    const PRICE = 'price_cents';

    const DISCOUNT = 'discount_cents';

    const TAX = 'tax_cents';

    /**
     * @var IuguHelper
     */
    private $iuguHelper;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @param IuguHelper $iuguHelper
     * @param SubjectReader $subjectReader
     * @param Cart $cart
     */
    public function __construct(
        IuguHelper $iuguHelper,
        SubjectReader $subjectReader,
        Cart $cart
    ) {
        $this->iuguHelper = $iuguHelper;
        $this->subjectReader = $subjectReader;
        $this->cart = $cart;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws Exception
     */
    public function build(array $buildSubject)
    {
        /** @var PaymentDataObjectInterface $payment */
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $order = $paymentDO->getOrder();

        $items = [];
        $total = 0;
        $discount = 0;
        $tax = 0;
        /** @var Item $item */
        foreach ($order->getItems() as $item) {
            $discount += $item->getDiscountAmount();
            if ($item->getParentItem()) {
                continue;
            }
            $items[] = [
                self::DESCRIPTION => $item->getName(),
                self::QTY => $item->getQtyOrdered(),
                self::PRICE => $this->iuguHelper->formatPriceCents($item->getPrice())
            ];
            $total += $item->getRowTotal();
            $tax += $item->getTaxAmount();
        }

        if ($tax > 0) {
            $items[] = [
                self::DESCRIPTION => __('Tax'),
                self::QTY => 1,
                self::PRICE => $this->iuguHelper->formatPriceCents($tax)
            ];
        }

        $shipping = $this->cart->getQuote()->getShippingAddress()->getShippingAmount();
        if ($shipping) {
            $items[] = [
                self::DESCRIPTION => __('Shipping'),
                self::QTY => 1,
                self::PRICE => $this->iuguHelper->formatPriceCents($shipping)
            ];
        }

        $totals = $this->cart->getQuote()->getTotals();

        //Installment Fee
        $installmentFee = 0;
        if (!empty($totals['installment_fee']) && (bool) $totals['installment_fee']->getValue()) {
            $installmentFee = $totals['installment_fee']->getValue();
            $items[] = [
                self::DESCRIPTION => __('Installment Fee'),
                self::QTY => 1,
                self::PRICE => $this->iuguHelper->formatPriceCents($installmentFee)
            ];
        }

        $subtotal = $total - $discount + $shipping + $tax + $installmentFee;

        if (number_format($subtotal, 2) > number_format($order->getGrandTotalAmount(), 2)) {
            $others = $order->getGrandTotalAmount() - $subtotal;
            $items[] = [
                self::DESCRIPTION => __('Others'),
                self::QTY => 1,
                self::PRICE => $this->iuguHelper->formatPriceCents($others)
            ];
        }

        return [
            self::ITEMS => $items,
            self::DISCOUNT => $this->iuguHelper->formatPriceCents($discount)
        ];
    }
}
