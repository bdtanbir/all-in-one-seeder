<?php

namespace AllInOneSeeder\App\Seeders\FluentCart;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class OrderSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fct_orders';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;

        $customerIds = $this->fetchIds($this->db->prefix . 'fct_customers');

        if (empty($customerIds)) {
            return 0;
        }

        $cols = [
            'status', 'parent_id', 'receipt_number', 'invoice_no',
            'fulfillment_type', 'type', 'mode', 'shipping_status',
            'customer_id', 'payment_method', 'payment_method_title',
            'payment_status', 'currency',
            'subtotal', 'coupon_discount_total', 'manual_discount_total',
            'shipping_total', 'shipping_tax', 'discount_tax',
            'tax_total', 'total_amount', 'total_paid', 'total_refund',
            'rate', 'tax_behavior',
            'note', 'ip_address',
            'completed_at', 'uuid', 'config',
            'created_at', 'updated_at',
        ];
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $status = $this->weightedRandom([
                'completed'  => 40,
                'processing' => 25,
                'on-hold'    => 15,
                'canceled'   => 10,
                'failed'     => 5,
                'pending'    => 5,
            ]);

            $fulfillmentType = $this->weightedRandom([
                'digital'  => 50,
                'physical' => 35,
                'service'  => 15,
            ]);

            $paymentData = FakeData::paymentMethod();

            // Derive payment status from order status
            if ($status === 'completed') {
                $paymentStatus = 'paid';
            } elseif ($status === 'processing') {
                $paymentStatus = $this->weightedRandom(['paid' => 60, 'pending' => 40]);
            } elseif ($status === 'canceled' || $status === 'failed') {
                $paymentStatus = 'failed';
            } else {
                $paymentStatus = 'pending';
            }

            // All monetary values stored as integers (cents; $1.00 = 100)
            $subtotal = rand(500, 100000);
            $tax      = (int) round($subtotal * 0.08);
            $shipping = $fulfillmentType === 'physical' ? rand(500, 2000) : 0;
            $total    = $subtotal + $tax + $shipping;
            $paid     = $paymentStatus === 'paid' ? $total : 0;

            $createdAt   = $this->randDate('-2 years', 'now');
            $completedAt = $status === 'completed' ? $createdAt : null;

            $rows[] = [
                'status'                => $status,
                'parent_id'             => 0,
                'receipt_number'        => FakeData::receiptNumber(),
                'invoice_no'            => FakeData::invoiceNumber(),
                'fulfillment_type'      => $fulfillmentType,
                'type'                  => 'payment',
                'mode'                  => 'live',
                'shipping_status'       => $fulfillmentType === 'physical' ? 'pending' : '',
                'customer_id'           => $this->randomElement($customerIds),
                'payment_method'        => $paymentData['method'],
                'payment_method_title'  => $paymentData['title'],
                'payment_status'        => $paymentStatus,
                'currency'              => 'USD',
                'subtotal'              => $subtotal,
                'coupon_discount_total' => 0,
                'manual_discount_total' => 0,
                'shipping_total'        => $shipping,
                'shipping_tax'          => 0,
                'discount_tax'          => 0,
                'tax_total'             => $tax,
                'total_amount'          => $total,
                'total_paid'            => $paid,
                'total_refund'          => 0,
                'rate'                  => 1.0,
                'tax_behavior'          => 0,
                'note'                  => FakeData::orderNote(),
                'ip_address'            => FakeData::ipAddress(),
                'completed_at'          => $completedAt,
                'uuid'                  => wp_generate_uuid4(),
                'config'                => json_encode(['source' => 'web']),
                'created_at'            => $createdAt,
                'updated_at'            => $createdAt,
            ];

            if (count($rows) === 200) {
                $this->insertBatch($rows, $cols);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            $this->insertBatch($rows, $cols);
        }

        return $this->inserted;
    }

    public function truncate(): void
    {
        $this->truncateTable($this->table);
    }
}
