<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\GuestToCustomer\Observer;

use Exception;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use MagePal\GuestToCustomer\Helper\Data;

/**
 * Class CustomerRegisterSuccessObserver
 * @package MagePal\GuestToCustomer\Observer
 */
class CustomerRegisterSuccessObserver implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Data $helperData
     */
    public function __construct(
        CollectionFactory $orderCollectionFactory,
        Data $helperData
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->helperData = $helperData;
    }

    /**
     * @param EventObserver $observer
     * @throws Exception
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->helperData->isMergeIfCustomerCreate()) {
            return;
        }

        $customer = $observer->getEvent()->getCustomer();
        $orders = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_email', $customer->getEmail())
            ->addFieldToFilter('customer_id', ['null' => true]);
        foreach ($orders as $order) {
            try {
                $order->setCustomerId($customer->getId());
                $order->save();
            } catch (Exception $e) {
                //do nothing
            }
        }
    }
}
