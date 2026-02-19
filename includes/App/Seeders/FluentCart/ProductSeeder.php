<?php

namespace AllInOneSeeder\App\Seeders\FluentCart;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

/**
 * Seeds wp_posts (post_type='fluent-products') + fct_product_details
 * + fct_product_variations. All three inserts happen atomically per
 * product so FK references are always valid.
 */
class ProductSeeder extends AbstractSeeder
{
    private string $detailsTable;
    private string $variationsTable;

    public function __construct()
    {
        parent::__construct();
        $this->table           = $this->db->posts; // wp_posts
        $this->detailsTable    = $this->db->prefix . 'fct_product_details';
        $this->variationsTable = $this->db->prefix . 'fct_product_variations';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;
        $adminId        = $this->adminUserId();

        for ($i = 0; $i < $count; $i++) {
            $name            = FakeData::productName();
            $slug            = FakeData::slug($name) . '-' . ($i + 1);
            $createdAt       = $this->randDate('-2 years', 'now');
            $fulfillmentType = $this->weightedRandom([
                'digital'  => 50,
                'physical' => 35,
                'service'  => 15,
            ]);

            // 1. Insert into wp_posts
            $result = $this->db->insert($this->table, [
                'post_author'           => $adminId,
                'post_date'             => $createdAt,
                'post_date_gmt'         => $createdAt,
                'post_content'          => FakeData::productDescription(),
                'post_title'            => $name,
                'post_excerpt'          => FakeData::sentence(),
                'post_status'           => 'publish',
                'comment_status'        => 'closed',
                'ping_status'           => 'closed',
                'post_name'             => $slug,
                'post_modified'         => $createdAt,
                'post_modified_gmt'     => $createdAt,
                'post_type'             => 'fluent-products',
                'to_ping'               => '',
                'pinged'                => '',
                'post_content_filtered' => '',
            ]);

            if ($result === false) {
                continue;
            }

            $postId = (int) $this->db->insert_id;
            $this->inserted++;

            // 2. Insert 1–3 variations; track their prices for detail min/max
            $varCount      = rand(1, 3);
            $variationType = $varCount === 1 ? 'simple' : 'simple_variations';
            $prices        = [];
            $firstVarId    = null;

            for ($v = 0; $v < $varCount; $v++) {
                $title = $varCount === 1 ? 'Default' : FakeData::variationTitle();
                $price = round(rand(500, 99900) / 100, 2); // $5.00 – $999.00
                $prices[] = $price;

                $this->db->insert($this->variationsTable, [
                    'post_id'              => $postId,
                    'serial_index'         => $v + 1,
                    'variation_title'      => $title,
                    'variation_identifier' => FakeData::slug($title) . '-' . ($v + 1),
                    'payment_type'         => 'onetime',
                    'item_status'          => 'active',
                    'stock_status'         => 'in-stock',
                    'item_price'           => $price,
                    'item_cost'            => round($price * 0.6, 2),
                    'compare_price'        => round($price * 1.2, 2),
                    'fulfillment_type'     => $fulfillmentType,
                    'manage_stock'         => 0,
                    'sold_individually'    => 0,
                    'backorders'           => 0,
                    'total_stock'          => 0,
                    'on_hold'              => 0,
                    'committed'            => 0,
                    'available'            => 0,
                    'other_info'           => '{}',
                    'created_at'           => $createdAt,
                    'updated_at'           => $createdAt,
                ]);

                if ($v === 0) {
                    $firstVarId = (int) $this->db->insert_id;
                }
            }

            // 3. Insert product details with back-filled default_variation_id
            $this->db->insert($this->detailsTable, [
                'post_id'              => $postId,
                'fulfillment_type'     => $fulfillmentType,
                'min_price'            => min($prices),
                'max_price'            => max($prices),
                'default_variation_id' => $firstVarId,
                'default_media'        => '{}',
                'manage_stock'         => 0,
                'stock_availability'   => 'in-stock',
                'variation_type'       => $variationType,
                'manage_downloadable'  => 0,
                'other_info'           => '{}',
                'created_at'           => $createdAt,
                'updated_at'           => $createdAt,
            ]);
        }

        return $this->inserted;
    }

    public function truncate(): void
    {
        // Only delete fluent-products posts; never TRUNCATE the entire wp_posts table
        $this->db->query(
            "DELETE FROM `{$this->table}` WHERE post_type = 'fluent-products'"
        );
        $this->truncateTable($this->detailsTable);
        $this->truncateTable($this->variationsTable);
    }
}
