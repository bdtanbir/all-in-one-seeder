<?php

namespace AllInOneSeeder\App\Factories;

/**
 * Self-contained fake data factory. No external library required.
 *
 * All methods are static so seeders can call FakeData::firstName() etc.
 * without instantiation.
 */
class FakeData
{
    // -------------------------------------------------------------------------
    // Static data pools
    // -------------------------------------------------------------------------

    private static array $firstNames = [
        'James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda',
        'William', 'Barbara', 'David', 'Elizabeth', 'Richard', 'Susan', 'Joseph', 'Jessica',
        'Thomas', 'Sarah', 'Charles', 'Karen', 'Christopher', 'Lisa', 'Daniel', 'Nancy',
        'Matthew', 'Betty', 'Anthony', 'Margaret', 'Mark', 'Sandra', 'Donald', 'Ashley',
        'Steven', 'Dorothy', 'Paul', 'Kimberly', 'Andrew', 'Emily', 'Joshua', 'Donna',
        'Kenneth', 'Michelle', 'Kevin', 'Carol', 'Brian', 'Amanda', 'George', 'Melissa',
        'Timothy', 'Deborah', 'Ronald', 'Stephanie', 'Edward', 'Rebecca', 'Jason', 'Sharon',
        'Jeffrey', 'Laura', 'Ryan', 'Cynthia', 'Jacob', 'Kathleen', 'Gary', 'Amy',
        'Nicholas', 'Angela', 'Eric', 'Shirley', 'Jonathan', 'Anna', 'Stephen', 'Brenda',
        'Larry', 'Pamela', 'Justin', 'Emma', 'Scott', 'Nicole', 'Brandon', 'Helen',
        'Benjamin', 'Samantha', 'Samuel', 'Katherine', 'Nathan', 'Christine', 'Tyler', 'Debra',
        'Alex', 'Rachel', 'Patrick', 'Carolyn', 'Ethan', 'Janet', 'Sean', 'Catherine',
    ];

    private static array $lastNames = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
        'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
        'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
        'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker',
        'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores',
        'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell',
        'Carter', 'Roberts', 'Turner', 'Phillips', 'Evans', 'Collins', 'Parker', 'Edwards',
        'Stewart', 'Morris', 'Morales', 'Murphy', 'Cook', 'Rogers', 'Gutierrez', 'Ortiz',
        'Morgan', 'Cooper', 'Peterson', 'Bailey', 'Reed', 'Kelly', 'Howard', 'Ramos',
        'Kim', 'Cox', 'Ward', 'Richardson', 'Watson', 'Brooks', 'Chavez', 'Wood', 'James',
        'Bennett', 'Gray', 'Mendoza', 'Ruiz', 'Hughes', 'Price', 'Alvarez', 'Castillo',
        'Sanders', 'Patel', 'Myers', 'Long', 'Ross', 'Foster', 'Jimenez', 'Powell',
    ];

    private static array $emailDomains = [
        'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com',
        'protonmail.com', 'mail.com', 'aol.com', 'live.com', 'msn.com',
        'example.com', 'testmail.dev', 'company.io', 'work.co', 'business.net',
    ];

    private static array $prefixes = ['Mr.', 'Ms.', 'Mrs.', 'Dr.', 'Prof.'];

    private static array $cities = [
        'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia',
        'San Antonio', 'San Diego', 'Dallas', 'San Jose', 'Austin', 'Jacksonville',
        'Fort Worth', 'Columbus', 'Indianapolis', 'Charlotte', 'San Francisco', 'Seattle',
        'Denver', 'Nashville', 'Oklahoma City', 'Las Vegas', 'Louisville', 'Portland',
        'Boston', 'Baltimore', 'Atlanta', 'Miami', 'Orlando', 'Minneapolis',
        'London', 'Manchester', 'Birmingham', 'Paris', 'Lyon', 'Berlin', 'Munich',
        'Madrid', 'Barcelona', 'Rome', 'Milan', 'Amsterdam', 'Brussels', 'Vienna',
        'Zurich', 'Stockholm', 'Oslo', 'Copenhagen', 'Toronto', 'Vancouver', 'Calgary',
        'Sydney', 'Melbourne', 'Brisbane', 'Tokyo', 'Singapore', 'Dubai', 'Mumbai',
        'São Paulo', 'Buenos Aires', 'Mexico City', 'Bogotá', 'Lima', 'Santiago',
    ];

    private static array $states = [
        'California', 'Texas', 'Florida', 'New York', 'Pennsylvania', 'Illinois',
        'Ohio', 'Georgia', 'North Carolina', 'Michigan', 'New Jersey', 'Virginia',
        'Washington', 'Arizona', 'Massachusetts', 'Tennessee', 'Indiana', 'Missouri',
        'Maryland', 'Wisconsin', 'Colorado', 'Minnesota', 'South Carolina', 'Alabama',
        'Louisiana', 'Kentucky', 'Oregon', 'Oklahoma', 'Connecticut', 'Utah',
        'Ontario', 'Quebec', 'British Columbia', 'Alberta',
        'England', 'Scotland', 'Wales', 'Bavaria', 'Catalonia', 'Lombardy',
        'New South Wales', 'Victoria', 'Queensland',
    ];

    private static array $countries = [
        'US', 'GB', 'CA', 'AU', 'DE', 'FR', 'ES', 'IT', 'NL', 'BE',
        'SE', 'NO', 'DK', 'FI', 'AT', 'CH', 'PL', 'PT', 'IE', 'NZ',
        'JP', 'SG', 'IN', 'BR', 'MX', 'AR', 'ZA', 'AE', 'NG', 'KE',
    ];

    private static array $industries = [
        'Technology', 'Healthcare', 'Finance', 'Retail', 'Manufacturing',
        'Education', 'Real Estate', 'Transportation', 'Energy', 'Media',
        'Consulting', 'Legal', 'Marketing', 'Construction', 'Agriculture',
        'Hospitality', 'Telecommunications', 'Pharmaceutical', 'Insurance', 'Automotive',
    ];

    private static array $companyAdjectives = [
        'Global', 'Advanced', 'Dynamic', 'Innovative', 'Strategic', 'Integrated',
        'Premier', 'Elite', 'Digital', 'Creative', 'Smart', 'Bright', 'Swift',
        'Modern', 'Future', 'Prime', 'Alpha', 'Apex', 'Summit', 'Pioneer',
        'Nexus', 'Vertex', 'Quantum', 'Synergy', 'Stellar',
    ];

    private static array $companyNouns = [
        'Solutions', 'Technologies', 'Systems', 'Group', 'Partners', 'Ventures',
        'Consulting', 'Services', 'Labs', 'Studio', 'Works', 'Network', 'Media',
        'Capital', 'Holdings', 'Enterprises', 'Corp', 'Associates', 'Agency', 'Hub',
    ];

    private static array $companyTypes = [
        'agency', 'vendor', 'customer', 'partner',
    ];

    private static array $streetNames = [
        'Main', 'Oak', 'Maple', 'Cedar', 'Pine', 'Elm', 'Washington', 'Park',
        'Lake', 'Hill', 'River', 'Spring', 'Sunset', 'Forest', 'Meadow',
        'Valley', 'Ridge', 'Highland', 'Willow', 'Birch', 'Chestnut', 'Walnut',
        'Lincoln', 'Franklin', 'Jefferson', 'Madison', 'Monroe', 'Adams',
    ];

    private static array $streetTypes = [
        'Street', 'Avenue', 'Boulevard', 'Drive', 'Road', 'Lane', 'Way', 'Court', 'Place',
    ];

    private static array $websiteTlds = [
        'acme', 'globex', 'initech', 'umbrella', 'hooli',
        'pied-piper', 'stark-industries', 'wayne-enterprises', 'cyberdyne', 'weyland',
        'soylent', 'tyrell', 'rekall', 'multipass', 'bluth',
    ];

    private static array $websiteExts = ['.com', '.io', '.co', '.net', '.org'];

    private static array $loremWords = [
        'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit',
        'sed', 'do', 'eiusmod', 'tempor', 'incididunt', 'labore', 'dolore', 'magna',
        'aliqua', 'enim', 'ad', 'minim', 'veniam', 'quis', 'nostrud', 'exercitation',
        'ullamco', 'laboris', 'nisi', 'aliquip', 'commodo', 'consequat', 'duis', 'aute',
        'irure', 'reprehenderit', 'voluptate', 'velit', 'esse', 'cillum', 'fugiat',
        'nulla', 'pariatur', 'excepteur', 'sint', 'occaecat', 'cupidatat', 'proident',
        'culpa', 'officia', 'deserunt', 'mollit', 'anim', 'laborum', 'perspiciatis',
        'unde', 'omnis', 'natus', 'voluptatem', 'accusantium', 'laudantium', 'totam',
        'aperiam', 'eaque', 'ipsa', 'quae', 'inventore', 'veritatis', 'architecto',
        'beatae', 'vitae', 'dicta', 'explicabo', 'nemo', 'ipsam', 'quia', 'aspernatur',
    ];

    private static array $campaignAdjectives = [
        'Exclusive', 'Limited', 'Special', 'Early Access', 'VIP', 'Member',
        'Holiday', 'Summer', 'Winter', 'Spring', 'Flash', 'Final', 'Big',
        'Year-End', 'Launch', 'New', 'Welcome', 'Loyalty', 'Re-engagement', 'Black Friday',
    ];

    private static array $campaignNouns = [
        'Offer', 'Sale', 'Deal', 'Update', 'Announcement', 'Invitation',
        'Newsletter', 'Promotion', 'Event', 'Webinar', 'Release', 'Collection',
        'Guide', 'Tips', 'Recap', 'Report', 'Preview', 'Reminder', 'Savings',
    ];

    private static array $subjectPhrases = [
        "Don't miss this",
        "You're invited to our",
        "Introducing the",
        "Last chance for our",
        "We thought you'd enjoy our",
        "A special offer just for you:",
        "Check out our new",
        "Important update about your",
        "Your exclusive access to",
        "Reminder about our",
        "Thank you — here's your",
        "Join us for the",
        "Get early access to the",
        "Discover our",
        "Here's what's new in our",
        "Your monthly",
        "We have a surprise for you:",
        "Action required:",
    ];

    private static array $funnelTitles = [
        'Welcome Sequence', 'Abandoned Cart Recovery', 'Post-Purchase Follow-Up',
        'Re-engagement Campaign', 'Lead Nurture Sequence', 'Onboarding Flow',
        'Trial Expiry Reminder', 'Upsell Automation', 'Win-Back Sequence',
        'Product Launch Drip', 'Newsletter Digest Flow', 'Churn Prevention',
        'Free Trial Nurture', 'Referral Thank You', 'Anniversary Campaign',
    ];

    private static array $triggerNames = [
        'fluentcrm_contact_created',
        'fluentcrm_list_applied',
        'fluentcrm_tag_applied',
        'fluentcrm_contact_updated',
        'woo_order_placed',
        'woo_order_completed',
        'user_registered',
        'learndash_course_completed',
    ];

    private static array $actionNames = [
        'send_email',
        'wait',
        'add_tag',
        'remove_tag',
        'add_to_list',
        'remove_from_list',
        'update_contact_property',
    ];

    private static array $noteTitles = [
        'Follow-up required', 'Interested in upgrade', 'Contacted via phone',
        'Sent proposal', 'Demo scheduled', 'Waiting for response',
        'Deal closed', 'Onboarding started', 'Support ticket raised',
        'Referred by partner', 'Trial extended', 'Payment pending',
        'Meeting notes', 'Call summary', 'Email bounced',
    ];

    private static array $listNames = [
        'Newsletter Subscribers', 'VIP Customers', 'Cold Leads', 'Trial Users',
        'Churned Customers', 'Onboarding', 'Enterprise Accounts', 'SMB Segment',
        'Webinar Attendees', 'Free Plan Users', 'Paid Users', 'Beta Testers',
        'Partner Network', 'Referral Program', 'Re-engagement List',
    ];

    private static array $tagNames = [
        'hot-lead', 'churned', 'enterprise', 'trial', 'beta-user',
        'newsletter-opt-in', 'vip', 'engaged', 'cold-lead', 'partner',
        'high-value', 'at-risk', 'new-contact', 'qualified', 'onboarded',
        'referral', 'unresponsive', 'demo-requested', 'free-plan', 'paid',
    ];

    private static array $sources = [
        'web', 'import', 'api', 'woocommerce', 'form', 'landing-page',
        'referral', 'social', 'email-campaign', 'manual',
    ];

    // -------------------------------------------------------------------------
    // Public API — People
    // -------------------------------------------------------------------------

    public static function firstName(): string
    {
        return self::pick(self::$firstNames);
    }

    public static function lastName(): string
    {
        return self::pick(self::$lastNames);
    }

    public static function prefix(): string
    {
        return self::pick(self::$prefixes);
    }

    /**
     * Generates a unique email address safe for repeated seeder runs.
     * Uses a static counter combined with microtime entropy.
     */
    public static function email(string $base = ''): string
    {
        static $counter = 0;
        $counter++;

        if (!$base) {
            $base = strtolower(self::pick(self::$firstNames) . '.' . self::pick(self::$lastNames));
        }

        $base = preg_replace('/[^a-z0-9._-]/', '', strtolower($base));

        // Counter + partial microtime ensures uniqueness within and across runs
        $unique = $counter . base_convert((int) (microtime(true) * 1000) % 1000000, 10, 36);

        return $base . '.' . $unique . '@' . self::pick(self::$emailDomains);
    }

    public static function phone(): string
    {
        return sprintf('+1 (%d) %d-%04d', rand(200, 999), rand(200, 999), rand(0, 9999));
    }

    // -------------------------------------------------------------------------
    // Public API — Location
    // -------------------------------------------------------------------------

    public static function city(): string
    {
        return self::pick(self::$cities);
    }

    public static function state(): string
    {
        return self::pick(self::$states);
    }

    public static function country(): string
    {
        return self::pick(self::$countries);
    }

    public static function postalCode(): string
    {
        return (string) rand(10000, 99999);
    }

    public static function addressLine(): string
    {
        return rand(1, 9999) . ' ' . self::pick(self::$streetNames) . ' ' . self::pick(self::$streetTypes);
    }

    // -------------------------------------------------------------------------
    // Public API — Company
    // -------------------------------------------------------------------------

    public static function companyName(): string
    {
        return self::pick(self::$companyAdjectives) . ' ' . self::pick(self::$companyNouns);
    }

    public static function companyType(): string
    {
        return self::pick(self::$companyTypes);
    }

    public static function industry(): string
    {
        return self::pick(self::$industries);
    }

    public static function website(): string
    {
        return 'https://www.' . self::pick(self::$websiteTlds) . self::pick(self::$websiteExts);
    }

    public static function url(): string
    {
        $paths = ['/blog/', '/products/', '/services/', '/about/', '/pricing/'];
        return self::website() . self::pick($paths) . self::slug(self::words(rand(2, 3)));
    }

    // -------------------------------------------------------------------------
    // Public API — Text
    // -------------------------------------------------------------------------

    /**
     * Return $count random lorem words joined by spaces (no capitalisation/period).
     */
    public static function words(int $count = 4): string
    {
        $picked = [];
        for ($i = 0; $i < $count; $i++) {
            $picked[] = self::pick(self::$loremWords);
        }
        return implode(' ', $picked);
    }

    public static function sentence(int $words = 8): string
    {
        return ucfirst(self::words($words)) . '.';
    }

    public static function paragraph(int $sentences = 3): string
    {
        $parts = [];
        for ($i = 0; $i < $sentences; $i++) {
            $parts[] = self::sentence(rand(6, 14));
        }
        return implode(' ', $parts);
    }

    public static function slug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }

    // -------------------------------------------------------------------------
    // Public API — CRM-specific
    // -------------------------------------------------------------------------

    public static function campaignTitle(): string
    {
        return self::pick(self::$campaignAdjectives) . ' ' . self::pick(self::$campaignNouns) . ' ' . date('Y');
    }

    public static function emailSubject(): string
    {
        return self::pick(self::$subjectPhrases) . ' ' . strtolower(self::pick(self::$campaignNouns));
    }

    public static function preHeader(): string
    {
        return ucfirst(self::words(rand(8, 14)));
    }

    public static function listName(): string
    {
        return self::pick(self::$listNames);
    }

    public static function tagName(): string
    {
        return self::pick(self::$tagNames);
    }

    public static function source(): string
    {
        return self::pick(self::$sources);
    }

    public static function funnelTitle(): string
    {
        return self::pick(self::$funnelTitles);
    }

    public static function triggerName(): string
    {
        return self::pick(self::$triggerNames);
    }

    public static function actionName(): string
    {
        return self::pick(self::$actionNames);
    }

    public static function noteTitle(): string
    {
        return self::pick(self::$noteTitles);
    }

    /**
     * Minimal HTML email body suitable for seeding campaigns.
     */
    public static function loremHtml(): string
    {
        $title = self::campaignTitle();
        $para1 = self::paragraph(2);
        $para2 = self::paragraph(2);
        $cta   = self::website() . '/offer';

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>{$title}</title></head>
<body style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;">
  <h1 style="color:#333;">{$title}</h1>
  <p>{$para1}</p>
  <p>{$para2}</p>
  <p style="text-align:center;margin:30px 0;">
    <a href="{$cta}" style="background:#0073aa;color:#fff;padding:12px 28px;text-decoration:none;border-radius:4px;font-weight:bold;">
      Learn More
    </a>
  </p>
  <p style="font-size:12px;color:#999;margin-top:40px;">
    You received this email because you are subscribed to our mailing list.<br>
    <a href="{{unsubscribe_url}}">Unsubscribe</a>
  </p>
</body>
</html>
HTML;
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    /** @internal */
    private static function pick(array $arr): string
    {
        return $arr[array_rand($arr)];
    }
}
