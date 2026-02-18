<?php

namespace AllInOneSeeder\App\Seeders;

abstract class AbstractSeeder
{
    protected \wpdb $db;

    /** Full table name including WP prefix, e.g. wp_fc_subscribers */
    protected string $table = '';

    /** Running count of rows inserted in the current seed() call */
    protected int $inserted = 0;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    /**
     * Seed $count rows. Returns the number of rows successfully inserted.
     */
    abstract public function seed(int $count): int;

    /**
     * Hard-delete (TRUNCATE) the seeder's primary table.
     */
    abstract public function truncate(): void;

    // -------------------------------------------------------------------------
    // Single-row insert
    // -------------------------------------------------------------------------

    /**
     * Insert one row via wpdb::insert().
     * Increments $this->inserted on success.
     */
    protected function insert(array $data): bool
    {
        $result = $this->db->insert($this->table, $data);

        if ($result !== false) {
            $this->inserted++;
            return true;
        }

        return false;
    }

    /**
     * Return the auto-incremented ID of the last inserted row.
     */
    protected function lastInsertId(): int
    {
        return (int) $this->db->insert_id;
    }

    // -------------------------------------------------------------------------
    // Batch insert
    // -------------------------------------------------------------------------

    /**
     * Insert many rows in a single SQL statement for performance.
     *
     * @param  array[]   $rows     Each element is a column => value map.
     * @param  string[]  $columns  Ordered list of column names (must be keys in every row).
     * @return int Number of rows inserted.
     */
    protected function insertBatch(array $rows, array $columns): int
    {
        if (empty($rows)) {
            return 0;
        }

        $placeholders = [];
        $values       = [];

        foreach ($rows as $row) {
            $rowPlaceholders = [];

            foreach ($columns as $col) {
                $val = $row[$col] ?? null;

                if (is_null($val)) {
                    $rowPlaceholders[] = 'NULL';
                } elseif (is_int($val)) {
                    $rowPlaceholders[] = '%d';
                    $values[]          = $val;
                } elseif (is_float($val)) {
                    $rowPlaceholders[] = '%f';
                    $values[]          = $val;
                } else {
                    $rowPlaceholders[] = '%s';
                    $values[]          = (string) $val;
                }
            }

            $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }

        $cols = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));
        $sql  = "INSERT INTO `{$this->table}` ({$cols}) VALUES " . implode(', ', $placeholders);

        if (empty($values)) {
            $result = $this->db->query($sql);
        } else {
            $result = $this->db->query($this->db->prepare($sql, ...$values));
        }

        if ($result === false) {
            // Fall back to individual inserts
            $count = 0;
            foreach ($rows as $row) {
                if ($this->insert($row)) {
                    $count++;
                }
            }
            return $count;
        }

        $count = (int) $result;
        $this->inserted += $count;
        return $count;
    }

    // -------------------------------------------------------------------------
    // Table helpers
    // -------------------------------------------------------------------------

    /**
     * TRUNCATE a table by its full name.
     */
    protected function truncateTable(string $table): void
    {
        $this->db->query("TRUNCATE TABLE `{$table}`");
    }

    /**
     * Fetch all IDs from a table. Used by dependent seeders to look up
     * parent IDs without holding a full result set in memory.
     *
     * @return int[]
     */
    protected function fetchIds(string $table, int $limit = 0): array
    {
        $sql = "SELECT id FROM `{$table}` ORDER BY id ASC";

        if ($limit > 0) {
            $sql .= $this->db->prepare(' LIMIT %d', $limit);
        }

        $results = $this->db->get_col($sql);

        return array_map('intval', $results ?: []);
    }

    // -------------------------------------------------------------------------
    // Date / time helpers
    // -------------------------------------------------------------------------

    /**
     * Current datetime formatted for MySQL (uses WP local time).
     */
    protected function now(): string
    {
        return current_time('mysql');
    }

    /**
     * Random MySQL DATETIME between two date strings.
     */
    protected function randDate(string $start, string $end): string
    {
        $ts = rand(strtotime($start), strtotime($end));
        return date('Y-m-d H:i:s', $ts);
    }

    /**
     * Random MySQL DATE (no time component) between two date strings.
     */
    protected function randDateOnly(string $start, string $end): string
    {
        $ts = rand(strtotime($start), strtotime($end));
        return date('Y-m-d', $ts);
    }

    // -------------------------------------------------------------------------
    // Randomisation helpers
    // -------------------------------------------------------------------------

    /**
     * Return a random element from an array.
     */
    protected function randomElement(array $arr)
    {
        return $arr[array_rand($arr)];
    }

    /**
     * Return $n unique random elements from $arr (re-indexed).
     *
     * @return array
     */
    protected function randomSample(array $arr, int $n): array
    {
        if ($n >= count($arr)) {
            return array_values($arr);
        }

        shuffle($arr);

        return array_slice($arr, 0, $n);
    }

    /**
     * Weighted random selection.
     *
     * Example:
     *   $this->weightedRandom(['subscribed' => 70, 'unsubscribed' => 15, 'pending' => 15])
     *
     * @param  array<string,int> $weights  key => relative weight
     * @return string The selected key.
     */
    protected function weightedRandom(array $weights): string
    {
        $total      = array_sum($weights);
        $rand       = rand(1, $total);
        $cumulative = 0;

        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return (string) $key;
            }
        }

        return (string) array_key_last($weights);
    }

    /**
     * Return the ID of the first WordPress administrator, falling back to 1.
     */
    protected function adminUserId(): int
    {
        static $adminId = null;

        if ($adminId === null) {
            $users   = get_users([
                'role'    => 'administrator',
                'number'  => 1,
                'fields'  => 'ID',
                'orderby' => 'ID',
                'order'   => 'ASC',
            ]);
            $adminId = !empty($users) ? (int) $users[0] : 1;
        }

        return $adminId;
    }
}
