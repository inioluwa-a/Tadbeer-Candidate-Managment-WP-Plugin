<?php
/*
Plugin Name: Team Member Manager
Description: A custom plugin to manage team members with custom post types, image upload, and more.
Version: 1.2
Author: Peace Mathew
Author URI: http://peacemathew.com.ng
License: GPL2
*/


// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue jQuery
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('jquery');
});


add_action('wp_head', function () {
    if (!is_admin()) {
?>
        <script type="text/javascript">
            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        </script>
    <?php
    }
});


// Enqueue Styles
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'team-member-css',
        plugins_url('team-member-css.css', __FILE__),
        [],
        filemtime(plugin_dir_path(__FILE__) . 'team-member-css.css')
    );
});



add_action('wp_enqueue_scripts', function () {
    // Only load on pages with the shortcode
    if (is_singular() && has_shortcode(get_post(get_queried_object_id())->post_content, 'view_all_candidates')) {
        wp_enqueue_script(
            'view-all-candidates-js',
            plugins_url('assets/js/view-all-candidates.js', __FILE__),
            array('jquery'),
            filemtime(plugin_dir_path(__FILE__) . 'assets/js/view-all-candidates.js'),
            true
        );
    }
});

// Enqueue Scripts
function enqueue_passport_scan_scripts()
{
    global $post;
    if (isset($post->post_content) && has_shortcode($post->post_content, 'add_team_member_form')) {

        // Include Tesseract & OpenCV
        wp_enqueue_script('tesseract-js', 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js', [], '5.0.4', true);
        wp_enqueue_script('opencv-js', 'https://docs.opencv.org/4.x/opencv.js', [], null, true);

        // Include the MRZ Scanner bundle (latest)
        wp_enqueue_script(
            'dynamsoft-mrz-scanner',
            'https://cdn.jsdelivr.net/npm/dynamsoft-mrz-scanner@2.1.0/dist/mrz-scanner.bundle.js',
            [], // no dependencies required
            '2.1.0',
            true
        );

        // passport-scan.js
        wp_enqueue_script(
            'passport-scan',
            plugin_dir_url(__FILE__) . 'assets/js/passport-scan.js',
            ['jquery', 'dynamsoft-mrz-scanner', 'tesseract-js', 'opencv-js'],
            '1.0.0',
            true
        );

        wp_localize_script('passport-scan', 'passportScan', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('passport_scan_nonce')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_passport_scan_scripts');


// Function to translate personality traits to Arabic
function translate_personality_to_arabic($trait)
{
    $translations = array(
        'Friendly' => 'ودود',
        'Hardworking' => 'مجتهد',
        'Patient' => 'صبور',
        'Honest' => 'صادق',
        'Reliable' => 'موثوق به',
        'Punctual' => 'ملتزم بالمواعيد',
        'Clean' => 'نظيف',
        'Organized' => 'منظم',
        'Respectful' => 'محترم',
        'Flexible' => 'مرن',
        'Caring' => 'حنون',
        'Energetic' => 'نشيط',
        'Calm' => 'هادئ',
        'Cheerful' => 'مرح',
        'Responsible' => 'مسؤول',
        'Trustworthy' => 'جدير بالثقة',
        'Polite' => 'مهذب',
        'Helpful' => 'مفيد',
        'Obedient' => 'مطيع',
        'Loyal' => 'مخلص',
        'Kind' => 'لطيف',
        'Gentle' => 'رقيق',
        'Quick Learner' => 'سريع التعلم',
        'Detail-oriented' => 'يهتم بالتفاصيل',
        'Independent' => 'مستقل'
    );
    return isset($translations[$trait]) ? $translations[$trait] : $trait;
}

// Function to translate civil status to Arabic
function translate_civil_status_to_arabic($status)
{
    $translations = array(
        'Single' => 'أعزب',
        'Married' => 'متزوج',
        'Divorced' => 'مطلق',
        'Widowed' => 'أرمل'
    );
    return isset($translations[$status]) ? $translations[$status] : $status;
}
// Function to translate job titles to Arabic
function translate_job_to_arabic($job)
{
    $translations = array(
        'Housemaid' => 'عاملة منزلية',
        'Sailor' => 'بحار',
        'Security Guard' => 'حارس أمن',
        'Housekeeper' => 'مدبرة منزل',
        'Cook' => 'طباخ',
        'Nanny/Babysitter' => 'مربية أطفال',
        'Farmer' => 'مزارع',
        'Gardener' => 'بستاني',
        'Personal driver' => 'سائق خاص',
        'Maid' => 'خادمة'
    );
    return isset($translations[$job]) ? $translations[$job] : $job;
}
// Function to translate                        
function translate_nationality_to_arabic($nationality)
{
    $translations = array(
        'Ethiopia' => 'إثيوبيا',
        'Uganda' => 'أوغندا',
        'Philippines' => 'الفلبين',
        'Kenya' => 'كينيا',
        'Indonesia' => 'إندونيسيا',
        'Sri Lanka' => 'سريلانكا',
        'Vietnam' => 'فيتنام',
        'Nepal' => 'نيبال',
        'Ghana' => 'غانا',
        'Myanmar' => 'ميانمار',
        'Bangladesh' => 'بنغلاديش',
        'Nigeria' => 'نيجيريا'
    );
    return isset($translations[$nationality]) ? $translations[$nationality] : $nationality;
}
// Function to translate package types to Arabic
function translate_package_to_arabic($package)
{
    $translations = array(
        'Traditional' => 'تقليدي',
        'Temporary' => 'مؤقت',
        'Long time live in' => 'إقامة طويلة المدى',
        'Flexible' => 'مرن'
    );
    return isset($translations[$package]) ? $translations[$package] : $package;
}
// Function to translate skills to Arabic
function translate_skill_to_arabic($skill)
{
    $translations = array(
        // Main Skills
        'Baby Care' => 'رعاية الأطفال الرضع',
        'Child Care' => 'رعاية الأطفال',
        'Teen Care' => 'رعاية المراهقين',
        'Elderly Care' => 'رعاية المسنين',
        'Pet Care' => 'رعاية الحيوانات الأليفة',
        'Tutoring' => 'تدريس خصوصي',
        'Housekeeping' => 'تدبير منزلي',
        'Cooking' => 'طبخ',
        'Driving' => 'قيادة',

        // Other Skills
        'Banking' => 'خدمات مصرفية',
        'Caregiver' => 'مقدم رعاية',
        'Car wash' => 'غسيل سيارات',
        'Computer' => 'حاسوب',
        'Driving Licence' => 'رخصة قيادة',
        'First Aid' => 'إسعافات أولية',
        'Gardening' => 'بستنة',
        'Handyman' => 'صيانة منزلية',
        'Housework' => 'أعمال منزلية',
        'Sewing' => 'خياطة',
        'Swimming' => 'سباحة'
    );
    return isset($translations[$skill]) ? $translations[$skill] : $skill;
}
// Function to translate languages to Arabic
function translate_language_to_arabic($language)
{
    $translations = array(
        'English' => 'الإنجليزية',
        'Arabic' => 'العربية',
        'French' => 'الفرنسية',
        'Chinese' => 'الصينية',
        'Spanish' => 'الإسبانية',
        'Hindi' => 'الهندية',
        'Portuguese' => 'البرتغالية',
        'Bengali' => 'البنغالية',
        'Russian' => 'الروسية',
        'Amharic' => 'الأمهرية',
        'Tagalog' => 'التاغالوغية',
        'Indonesian' => 'الإندونيسية',
        'Swahili' => 'السواحيلية',
        'Hausa' => 'الهوسا',
        'Yoruba' => 'اليوروبا',
        'Igbo' => 'الإيغبو',
        'Oromo' => 'الأورومو',
        'Urdu' => 'الأوردو',
        'Japanese' => 'اليابانية',
        'Punjabi' => 'البنجابية',
        'Vietnamese' => 'الفيتنامية',
        'Marathi' => 'المراثية'
    );
    return isset($translations[$language]) ? $translations[$language] : $language;
}
// Function to translate country names to Arabic
function translate_country_to_arabic($country)
{
    $translations = array(
        // Gulf Countries
        'Saudi Arabia' => 'المملكة العربية السعودية',
        'UAE' => 'الإمارات العربية المتحدة',
        'Qatar' => 'قطر',
        'Kuwait' => 'الكويت',
        'Oman' => 'عمان',
        'Bahrain' => 'البحرين',


        'Iraq' => 'العراق',
        'Lebanon' => 'لبنان',
        'Beirut' => 'بيروت',

        // Asian Countries
        'Singapore' => 'سنغافورة',
        'Hong Kong' => 'هونغ كونغ',
        'Malaysia' => 'ماليزيا'
    );
    return isset($translations[$country]) ? $translations[$country] : $country;
}

function add_team_member_list_styles()
{
    ?>
    <style>
        .team-members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            /*gap: 20px;*/
            padding: 20px;
            /*max-width: 1200px;*/
            margin: 0 auto;
        }

        .team-member-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .team-member-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .team-member-image {
            width: 90%;
            height: 200px;
            overflow: hidden;
        }

        .team-member-image img {
            height: 100%;
            object-fit: cover;
        }

        .team-member-info {
            padding: 15px;
        }

        .team-member-info strong {
            font-size: 1.2em;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        .team-member-info small {
            color: #666;
            display: block;
            margin-bottom: 10px;
        }

        .team-member-info span {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            width: 100%;
        }

        .category {
            margin: 15px 0;
        }

        .category p {
            font-weight: bold;
            color: #444;
            margin-bottom: 5px;
        }

        .category ol {
            margin: 0;
            padding-left: 20px;
        }

        .category li {
            color: #666;
            margin-bottom: 3px;
        }

        .team-member-actions {
            padding: 15px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }

        .team-member-actions a {
            text-decoration: none;
            color: #3498db;
            transition: color 0.2s;
        }

        .team-member-actions a:hover {
            color: #2980b9;
        }

        .video-container {
            margin: 15px 0;
            border-radius: 8px;
            overflow: hidden;
        }

        .search-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        #member-search {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 300px;
            font-size: 14px;
        }

        .member-count {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }

        .loading {
            position: relative;
            opacity: 0.6;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
        }
    </style>
<?php
}
add_action('wp_head', 'add_team_member_list_styles');


// Function to get video embed URL - Updated to support YouTube Shorts
// Update the get_video_embed_url function
function get_video_embed_url($url)
{
    // YouTube Shorts support
    if (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    // Standard YouTube
    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
    } elseif (strpos($url, 'vimeo.com') !== false) {
        if (preg_match('/vimeo\.com\/(?:.*\/)?([0-9]+)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }
    } elseif (preg_match('/\.mp4(\?.*)?$/i', $url)) {
        return $url;
    } elseif (preg_match('/https?:\/\/jmp\.sh\/[\w]+/i', $url)) {
        return $url;
    }
    return '';
}

// Function to validate video URL function 
function validate_video_url($url)
{
    if (empty($url)) {
        return true; // Empty URL is valid (video is optional)
    }
    // Updated patterns
    $youtube_pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|shorts\/)|youtu\.be\/)[a-zA-Z0-9_-]+/';
    // Accept any vimeo.com URL with a numeric ID
    $vimeo_pattern = '/vimeo\.com\/(?:.*\/)?([0-9]+)/';

    if (preg_match($youtube_pattern, $url) || preg_match($vimeo_pattern, $url)) {
        return true;
    }
    return false;
}



// Function to register the 'team_member' custom post type
function register_team_member_post_type()
{
    $args = array(
        'labels' => array(
            'name'                  => 'Team Members',
            'singular_name'          => 'Team Member',
            'add_new'               => 'Add New Team Member',
            'edit_item'             => 'Edit Team Member',
            'new_item'              => 'New Team Member',
            'view_item'             => 'View Team Member',
            'search_items'          => 'Search Team Members',
            'not_found'             => 'No Team Members Found',
            'not_found_in_trash'    => 'No Team Members Found in Trash',
            'all_items'             => 'All Team Members',
            'archives'              => 'Team Member Archives',
        ),
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-groups',
        'supports'           => array('title', 'thumbnail', 'editor', 'author'),
        'capability_type'    => 'team_member',
        'map_meta_cap'       => true,
        'capabilities'       => array(
            'edit_posts'             => 'edit_team_members',
            'edit_others_posts'      => 'edit_others_team_members',
            'publish_posts'          => 'publish_team_members',
            'read_private_posts'     => 'read_private_team_members',
            'delete_posts'           => 'delete_team_members',
            'delete_others_posts'    => 'delete_others_team_members',
            'delete_private_posts'   => 'delete_private_team_members',
            'delete_published_posts' => 'delete_published_team_members',
        ),
    );
    register_post_type('team_member', $args);
}
add_action('init', 'register_team_member_post_type');

// Function to assign capabilities to the Administrator role
function add_team_member_capabilities()
{
    $role = get_role('administrator');
    if ($role) {
        $role->add_cap('edit_team_members');
        $role->add_cap('edit_others_team_members');
        $role->add_cap('publish_team_members');
        $role->add_cap('read_private_team_members');
        $role->add_cap('delete_team_members');
        $role->add_cap('delete_others_team_members');
        $role->add_cap('delete_private_team_members');
        $role->add_cap('delete_published_team_members');
    }
}
add_action('admin_init', 'add_team_member_capabilities');

// ✅ Add Author Column to Admin Panel
function add_team_member_columns($columns)
{
    $columns['author'] = 'Posted By'; // Add new column for author
    return $columns;
}
add_filter('manage_team_member_posts_columns', 'add_team_member_columns');

// ✅ Display Author Name in the Custom Column
function display_team_member_column_data($column, $post_id)
{
    if ($column === 'author') {
        $author_id = get_post_field('post_author', $post_id);
        $author_name = get_the_author_meta('display_name', $author_id);
        echo esc_html($author_name);
    }
}
add_action('manage_team_member_posts_custom_column', 'display_team_member_column_data', 10, 2);


// Front-End Form to Add Team Members

function add_team_member_form($atts = [])
{
    global $wp;
    // Ensure WordPress functions are available if this is called early
    if (! function_exists('is_user_logged_in')) {
        require_once ABSPATH . 'wp-includes/pluggable.php';
    }

    // Get current page URL for redirects
    $current_page_url = home_url($wp->request);

    // Support edit_id from URL if not passed as shortcode attribute
    if (empty($atts['edit_id']) && isset($_GET['edit_id'])) {
        $atts['edit_id'] = intval($_GET['edit_id']);
    }

    $atts = shortcode_atts(['edit_id' => 0], $atts);
    $edit_id = intval($atts['edit_id']);
    $is_edit = $edit_id > 0;
    $post = $is_edit ? get_post($edit_id) : null;

    // Prefill variables - initialized here for both display and processing
    $passport_nationality = $passport_number = $passport_issue_date = $passport_expiry_date = $passport_place_of_issue = '';
    $name = $nationality = $age = $bio = $package = $job = $mskills = $cskills = $oskills = $personality = '';
    $qualification = $experience = $languages = $education = $religion = $place_of_birth = $civil_status = '';
    $mobile = $tel_no = $po_box = $location = $video = $whatsapp_no = $email = $phone = $address = $city = $state = $country = $salary = '';
    $company_name = $company_tagline = $company_phone = '';
    $weight = $height = '';
    $status = 'available';
    $passport_scan_text = ''; // Initialize for display

    $image_id = '';
    $image_url = '';
    $full_profile_image_id = '';
    $full_profile_image_url = '';
    $company_logo_id = '';
    $company_logo_url = '';
    $country_experience = []; // Initialize for display

    $error_message = '';
    $success_message = '';

    // Check if user is logged in early
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to manage team members.</p>';
    }

    // If editing, fetch existing data to pre-fill the form
    if ($is_edit && $post && get_post_type($edit_id) === 'team_member' && $post->post_author == get_current_user_id()) {

        // Migrate old meta keys to new keys if new keys are empty
        // This block should ideally run once on migration or be part of an update script,
        // not on every form load or submission. But if it's crucial here, keep it.
        $meta_map = [
            'team_member_name'              => 'name', // Example: mapping old 'name' to new 'team_member_name'
            'team_member_nationality'       => 'nationality',
            'team_member_age'               => 'age',
            'team_member_bio'               => 'bio',
            'team_member_package'           => 'package',
            'team_member_job'               => 'job',
            'team_member_mskills'           => 'mskills',
            'team_member_cskills'           => 'cskills',
            'team_member_oskills'           => 'oskills',
            'team_member_personality'       => 'personality',
            'team_member_status'            => 'status',
            'team_member_email'             => 'email',
            'team_member_phone'             => 'phone',
            'team_member_address'           => 'address',
            'team_member_city'              => 'city',
            'team_member_state'             => 'state',
            'team_member_country'           => 'country',
            'team_member_salary'            => 'salary',
            'team_member_whatsapp_no'       => 'whatsapp_no',
            'team_member_po_box'            => 'po_box',
            'team_member_company_name'      => 'company_name',
            'team_member_company_tagline'   => 'company_tagline',
            'team_member_company_phone'     => 'company_phone',
            'team_member_video'             => 'video',
            'team_member_weight'            => 'weight',
            'team_member_height'            => 'height',
            'team_member_qualification'     => 'qualification',
            'team_member_experience'        => 'experience',
            'team_member_languages'         => 'languages',
            'team_member_education'         => 'education',
            'team_member_religion'          => 'religion',
            'team_member_place_of_birth'    => 'place_of_birth',
            'team_member_civil_status'      => 'civil_status',
            'team_member_passport_nationality' => 'passport_nationality',
            'team_member_passport_number'   => 'passport_number',
            'team_member_passport_issue_date' => 'passport_issue_date',
            'team_member_passport_expiry_date' => 'passport_expiry_date',
            'team_member_passport_place_of_issue' => 'passport_place_of_issue',
        ];
        foreach ($meta_map as $new_key => $old_key) {
            $new_val = get_post_meta($edit_id, $new_key, true);
            if (empty($new_val)) {
                $old_val = get_post_meta($edit_id, $old_key, true);
                if (!empty($old_val)) {
                    update_post_meta($edit_id, $new_key, $old_val);
                }
            }
        }

        // Fetch all meta data
        $name                = get_post_meta($edit_id, 'team_member_name', true);
        $nationality         = get_post_meta($edit_id, 'team_member_nationality', true);
        $age                 = get_post_meta($edit_id, 'team_member_age', true);
        $bio                 = get_post_meta($edit_id, 'team_member_bio', true);
        $package             = get_post_meta($edit_id, 'team_member_package', true);
        $qualification       = get_post_meta($edit_id, 'team_member_qualification', true);
        $experience          = get_post_meta($edit_id, 'team_member_experience', true);
        $languages           = get_post_meta($edit_id, 'team_member_languages', true);
        $education           = get_post_meta($edit_id, 'team_member_education', true);
        $religion            = get_post_meta($edit_id, 'team_member_religion', true);
        $place_of_birth      = get_post_meta($edit_id, 'team_member_place_of_birth', true);
        $civil_status        = get_post_meta($edit_id, 'team_member_civil_status', true);
        $job                 = get_post_meta($edit_id, 'team_member_job', true);
        $mskills             = get_post_meta($edit_id, 'team_member_mskills', true);
        $cskills             = get_post_meta($edit_id, 'team_member_cskills', true);
        $oskills             = get_post_meta($edit_id, 'team_member_oskills', true);
        $personality         = get_post_meta($edit_id, 'team_member_personality', true);
        $status              = get_post_meta($edit_id, 'team_member_status', true) ?: 'available';
        $email               = get_post_meta($edit_id, 'team_member_email', true);
        $phone               = get_post_meta($edit_id, 'team_member_phone', true);
        $address             = get_post_meta($edit_id, 'team_member_address', true);
        $city                = get_post_meta($edit_id, 'team_member_city', true);
        $state               = get_post_meta($edit_id, 'team_member_state', true);
        $country             = get_post_meta($edit_id, 'team_member_country', true);
        $salary              = get_post_meta($edit_id, 'team_member_salary', true);
        $whatsapp_no         = get_post_meta($edit_id, 'team_member_whatsapp_no', true);
        $po_box              = get_post_meta($edit_id, 'team_member_po_box', true);
        $company_name        = get_post_meta($edit_id, 'team_member_company_name', true);
        $company_tagline     = get_post_meta($edit_id, 'team_member_company_tagline', true);
        $company_phone       = get_post_meta($edit_id, 'team_member_company_phone', true);
        $video               = get_post_meta($edit_id, 'team_member_video', true);
        $weight              = get_post_meta($edit_id, 'team_member_weight', true);
        $height              = get_post_meta($edit_id, 'team_member_height', true);
        $passport_nationality    = get_post_meta($edit_id, 'team_member_passport_nationality', true);
        $passport_number         = get_post_meta($edit_id, 'team_member_passport_number', true);
        $passport_issue_date     = get_post_meta($edit_id, 'team_member_passport_issue_date', true);
        $passport_expiry_date    = get_post_meta($edit_id, 'team_member_passport_expiry_date', true);
        $passport_place_of_issue = get_post_meta($edit_id, 'team_member_passport_place_of_issue', true);
        $passport_scan_text      = get_post_meta($edit_id, 'team_member_passport_text', true);
        $country_experience      = get_post_meta($edit_id, 'country_experience', true) ?: [];


        // Images
        $image_id               = get_post_meta($edit_id, 'team_member_image', true);
        $image_url              = $image_id ? wp_get_attachment_url($image_id) : '';
        if (!$full_profile_image_id_new) {
                    $full_profile_image_id_new = get_post_meta($post_id, 'team_member_full_profile_image', true);
                }
        $full_profile_image_id  = get_post_meta($edit_id, 'team_member_full_profile_image', true);
        $full_profile_image_url = $full_profile_image_id ? wp_get_attachment_url($full_profile_image_id) : '';
        $company_logo_id        = get_post_meta($edit_id, 'team_member_logo_url', true);
        $company_logo_url       = $company_logo_id ? wp_get_attachment_url($company_logo_id) : '';
    } else if (!$is_edit) {
        // If not editing (new entry), get the current user's WhatsApp number for prefill
        $user_id = get_current_user_id();
        $whatsapp_no = get_user_meta($user_id, 'whatsapp_number', true);
    }


    // --- FORM SUBMISSION HANDLING ---
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_team_member'])) {
         $error_message = '';
        // Verify nonce
        if (!isset($_POST['team_member_nonce']) || !wp_verify_nonce($_POST['team_member_nonce'], 'add_team_member')) {
            wp_die('Security check failed');
        }

       // Clean output buffer only before redirect
        while (ob_get_level()) {
            ob_end_clean();
        }

       
        // Sanitize and assign submitted data
        $name                = sanitize_text_field($_POST['team_member_name']);
        $nationality         = sanitize_text_field($_POST['team_member_nationality']);
        $age                 = sanitize_text_field($_POST['team_member_age']);
        $bio                 = sanitize_textarea_field($_POST['team_member_bio']); // Use textarea for bio
        $package             = sanitize_text_field($_POST['team_member_package']);

        $qualification       = sanitize_text_field($_POST['team_member_qualification']);
        $experience          = sanitize_text_field($_POST['team_member_experience']);
        $languages           = sanitize_text_field($_POST['team_member_languages']);
        $education           = sanitize_text_field($_POST['team_member_education']);
        $religion            = sanitize_text_field($_POST['team_member_religion']);
        $place_of_birth      = sanitize_text_field($_POST['team_member_place_of_birth']);
        $civil_status        = sanitize_text_field($_POST['team_member_civil_status']);

        // Skills - handle multiple selections if applicable (checkboxes/multiselect)
        $job = !empty($_POST['team_member_job']) ? (is_array($_POST['team_member_job']) ? implode(', ', array_map('sanitize_text_field', $_POST['team_member_job'])) : sanitize_text_field($_POST['team_member_job'])) : '';
        $mskills = !empty($_POST['team_member_mskills']) ? (is_array($_POST['team_member_mskills']) ? implode(', ', array_map('sanitize_text_field', $_POST['team_member_mskills'])) : sanitize_text_field($_POST['team_member_mskills'])) : '';
        $cskills = !empty($_POST['team_member_cskills']) ? (is_array($_POST['team_member_cskills']) ? implode(', ', array_map('sanitize_text_field', $_POST['team_member_cskills'])) : sanitize_text_field($_POST['team_member_cskills'])) : '';
        $oskills = !empty($_POST['team_member_oskills']) ? (is_array($_POST['team_member_oskills']) ? implode(', ', array_map('sanitize_text_field', $_POST['team_member_oskills'])) : sanitize_text_field($_POST['team_member_oskills'])) : '';
        $personality = !empty($_POST['team_member_personality']) ? (is_array($_POST['team_member_personality']) ? implode(', ', array_map('sanitize_text_field', $_POST['team_member_personality'])) : sanitize_text_field($_POST['team_member_personality'])) : '';

        $status              = sanitize_text_field($_POST['team_member_status']);

        $email               = sanitize_email($_POST['team_member_email']);
        $phone               = sanitize_text_field($_POST['team_member_phone']);
        $address             = sanitize_text_field($_POST['team_member_address']);
        $city                = sanitize_text_field($_POST['team_member_city']);
        $state               = sanitize_text_field($_POST['team_member_state']);
        $country             = sanitize_text_field($_POST['team_member_country']);
        $salary              = sanitize_text_field($_POST['team_member_salary']);
        $whatsapp_no         = sanitize_text_field($_POST['team_member_whatsapp_no']);
        $po_box              = sanitize_text_field($_POST['team_member_po_box']);

        $company_name        = sanitize_text_field($_POST['team_member_company_name']);
        $company_tagline     = sanitize_text_field($_POST['team_member_company_tagline']);
        $company_phone       = sanitize_text_field($_POST['team_member_company_phone']);

        $video               = sanitize_text_field($_POST['team_member_video']);
        $weight              = sanitize_text_field($_POST['team_member_weight']);
        $height              = sanitize_text_field($_POST['team_member_height']);

        // Passport details
        $passport_nationality    = sanitize_text_field($_POST['team_member_passport_nationality']);
        $passport_number         = sanitize_text_field($_POST['team_member_passport_number']);
        $passport_issue_date     = sanitize_text_field($_POST['team_member_passport_issue_date']);
        $passport_expiry_date    = sanitize_text_field($_POST['team_member_passport_expiry_date']);
        $passport_place_of_issue = sanitize_text_field($_POST['team_member_passport_place_of_issue']);
        $passport_scan_text      = isset($_POST['team_member_passport_text']) ? sanitize_textarea_field($_POST['team_member_passport_text']) : '';


        // Country Experience
        $country_experience = array();
        if (!empty($_POST['team_member_countries']) && !empty($_POST['country_years'])) {
            foreach ($_POST['team_member_countries'] as $country_val) { // Use a different variable name
                $sanitized_country = sanitize_text_field($country_val);
                if (isset($_POST['country_years'][$sanitized_country])) {
                    $years = intval($_POST['country_years'][$sanitized_country]);
                    $country_experience[$sanitized_country] = $years;
                }
            }
        }

        $user_id = get_current_user_id();

        // Prevent duplicates by name, email, or passport number
        $meta_query = array('relation' => 'OR');
        $has_check_fields = false;

        if (!empty($name)) {
            $meta_query[] = array('key' => 'team_member_name', 'value' => $name, 'compare' => '=');
            $has_check_fields = true;
        }
        if (!empty($passport_number)) {
            $meta_query[] = array('key' => 'team_member_passport_number', 'value' => $passport_number, 'compare' => '=');
            $has_check_fields = true;
        }
        // Only run duplicate check if we have fields to check
         $existing = false;
        if ($has_check_fields) {
            $args = array(
                'post_type'   => 'team_member',
                'post_status' => 'publish',
                'fields'      => 'ids',
                'meta_query'  => $meta_query
            );

            // If editing, exclude current post from duplicate check
            if ($is_edit) {
                $args['post__not_in'] = array($edit_id);
            }

            $existing = get_posts($args);

            if ($existing) {
                $error_message = 'A CV with this name or passport number already exists.';
            }
        } 

        // Only proceed if no duplicates found
        if (!$existing && empty($error_message)) {
            if ($is_edit) {
                $post_id = wp_update_post(array(
                    'ID'          => $edit_id,
                    'post_title'  => $name,
                    'post_type'   => 'team_member',
                    'post_status' => 'publish',
                    'post_author' => $user_id,
                ));
            } else {
                // Create new post
                $post_id = wp_insert_post(array(
                    'post_title'  => $name,
                    'post_type'   => 'team_member',
                    'post_status' => 'publish',
                    'post_author' => $user_id,
                ));
            }

            if (is_wp_error($post_id)) {
                $error_message = 'Error saving team member: ' . $post_id->get_error_message();
            } else {
                // Update post meta

                update_post_meta($post_id, 'name', $name);
                update_post_meta($post_id, 'team_member_name', $name);

                // Save nationality to all meta keys for backward compatibility
                update_post_meta($post_id, 'nationality', $nationality);
                update_post_meta($post_id, 'team_member_nationality', $nationality);
                update_post_meta($post_id, 'passport_nationality', $nationality);
                update_post_meta($post_id, 'team_member_passport_nationality', $nationality);

                update_post_meta($post_id, 'age', $age);
                update_post_meta($post_id, 'team_member_age', $age);

                update_post_meta($post_id, 'bio', $bio);
                update_post_meta($post_id, 'team_member_bio', $bio);

                update_post_meta($post_id, 'package', $package);
                update_post_meta($post_id, 'team_member_package', $package);

                update_post_meta($post_id, 'qualification', $qualification);
                update_post_meta($post_id, 'team_member_qualification', $qualification);

                update_post_meta($post_id, 'experience', $experience);
                update_post_meta($post_id, 'team_member_experience', $experience);

                update_post_meta($post_id, 'languages', $languages);
                update_post_meta($post_id, 'team_member_languages', $languages);

                update_post_meta($post_id, 'education', $education);
                update_post_meta($post_id, 'team_member_education', $education);

                update_post_meta($post_id, 'religion', $religion);
                update_post_meta($post_id, 'team_member_religion', $religion);

                update_post_meta($post_id, 'place_of_birth', $place_of_birth);
                update_post_meta($post_id, 'team_member_place_of_birth', $place_of_birth);

                update_post_meta($post_id, 'civil_status', $civil_status);
                update_post_meta($post_id, 'team_member_civil_status', $civil_status);

                update_post_meta($post_id, 'job', $job);
                update_post_meta($post_id, 'team_member_job', $job);

                update_post_meta($post_id, 'mskills', $mskills);
                update_post_meta($post_id, 'team_member_mskills', $mskills);

                update_post_meta($post_id, 'cskills', $cskills);
                update_post_meta($post_id, 'team_member_cskills', $cskills);

                update_post_meta($post_id, 'oskills', $oskills);
                update_post_meta($post_id, 'team_member_oskills', $oskills);

                update_post_meta($post_id, 'personality', $personality);
                update_post_meta($post_id, 'team_member_personality', $personality);

                update_post_meta($post_id, 'status', $status);
                update_post_meta($post_id, 'team_member_status', $status);

                update_post_meta($post_id, 'email', $email);
                update_post_meta($post_id, 'team_member_email', $email);

                update_post_meta($post_id, 'phone', $phone);
                update_post_meta($post_id, 'team_member_phone', $phone);

                update_post_meta($post_id, 'address', $address);
                update_post_meta($post_id, 'team_member_address', $address);

                update_post_meta($post_id, 'city', $city);
                update_post_meta($post_id, 'team_member_city', $city);

                update_post_meta($post_id, 'state', $state);
                update_post_meta($post_id, 'team_member_state', $state);

                update_post_meta($post_id, 'country', $country);
                update_post_meta($post_id, 'team_member_country', $country);

                update_post_meta($post_id, 'salary', $salary);
                update_post_meta($post_id, 'team_member_salary', $salary);

                update_post_meta($post_id, 'whatsapp_no', $whatsapp_no);
                update_post_meta($post_id, 'team_member_whatsapp_no', $whatsapp_no);

                update_post_meta($post_id, 'po_box', $po_box);
                update_post_meta($post_id, 'team_member_po_box', $po_box);

                update_post_meta($post_id, 'company_name', $company_name);
                update_post_meta($post_id, 'team_member_company_name', $company_name);

                update_post_meta($post_id, 'company_tagline', $company_tagline);
                update_post_meta($post_id, 'team_member_company_tagline', $company_tagline);

                update_post_meta($post_id, 'company_phone', $company_phone);
                update_post_meta($post_id, 'team_member_company_phone', $company_phone);

                update_post_meta($post_id, 'video', $video);
                update_post_meta($post_id, 'team_member_video', $video);

                update_post_meta($post_id, 'weight', $weight);
                update_post_meta($post_id, 'team_member_weight', $weight);

                update_post_meta($post_id, 'height', $height);
                update_post_meta($post_id, 'team_member_height', $height);

                // Passport fields
                update_post_meta($post_id, 'passport_nationality', $passport_nationality);
                update_post_meta($post_id, 'team_member_passport_nationality', $passport_nationality);

                update_post_meta($post_id, 'passport_number', $passport_number);
                update_post_meta($post_id, 'team_member_passport_number', $passport_number);

                update_post_meta($post_id, 'passport_issue_date', $passport_issue_date);
                update_post_meta($post_id, 'team_member_passport_issue_date', $passport_issue_date);

                update_post_meta($post_id, 'passport_expiry_date', $passport_expiry_date);
                update_post_meta($post_id, 'team_member_passport_expiry_date', $passport_expiry_date);

                update_post_meta($post_id, 'passport_place_of_issue', $passport_place_of_issue);
                update_post_meta($post_id, 'team_member_passport_place_of_issue', $passport_place_of_issue);

                update_post_meta($post_id, 'passport_text', $passport_scan_text);
                update_post_meta($post_id, 'team_member_passport_text', $passport_scan_text);

                // Country Experience update
                update_post_meta($post_id, 'country_experience', $country_experience);

                // --- Handle file uploads (Passport Scan, Profile Image, Full Profile Image, Company Logo) ---
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php'); // Essential for wp_handle_upload

                $upload_errors = [];

                // Helper function for image uploads to reduce repetition
                $handle_image_upload = function ($file_input_name, $meta_key, $post_id) use (&$upload_errors) {
                    if (!empty($_FILES[$file_input_name]['name'])) {
                        // Delete old file if it exists
                        $old_attachment_id = get_post_meta($post_id, $meta_key, true);
                        if ($old_attachment_id) {
                            wp_delete_attachment($old_attachment_id, true);
                        }

                        $uploaded_file = wp_handle_upload($_FILES[$file_input_name], array('test_form' => false));

                        if (isset($uploaded_file['error'])) {
                            $upload_errors[] = 'Error uploading ' . $file_input_name . ': ' . $uploaded_file['error'];
                            return null;
                        }

                        if (!in_array($uploaded_file['type'], ['image/jpeg', 'image/png', 'image/gif'])) { // Added gif as it's common
                            $upload_errors[] = 'Invalid file type for ' . $file_input_name . '. Only JPEG, PNG, GIF are allowed.';
                            return null;
                        }

                        if ($uploaded_file['size'] > 2 * 1024 * 1024) { // 2MB limit
                            $upload_errors[] = $file_input_name . ' file size exceeds the limit of 2MB.';
                            return null;
                        }

                        if (isset($uploaded_file['file'])) {
                            $file_path = $uploaded_file['file'];
                            $file_name = basename($file_path);
                            $attachment = array(
                                'guid'           => $uploaded_file['url'],
                                'post_mime_type' => $uploaded_file['type'],
                                'post_title'     => $file_name,
                                'post_content'   => '',
                                'post_status'    => 'inherit'
                            );

                            $attach_id = wp_insert_attachment($attachment, $file_path, $post_id);
                            if (!is_wp_error($attach_id)) {
                                update_post_meta($post_id, $meta_key, $attach_id);
                                wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $file_path));

                                // Call image enhancement function if it exists
                                if (function_exists('enhance_image_with_rapidapi')) {
                                    enhance_image_with_rapidapi($attach_id);
                                }
                                return $attach_id;
                            } else {
                                $upload_errors[] = 'Error saving ' . $file_input_name . ' attachment: ' . $attach_id->get_error_message();
                                return null;
                            }
                        }
                    }
                    return get_post_meta($post_id, $meta_key, true); // Retain existing if no new file uploaded
                };

                // Process each image upload
                $passport_scan_id_new     = $handle_image_upload('team_member_passport_scan', 'team_member_passport_scan', $post_id);
                $image_id_new             = $handle_image_upload('team_member_image', 'team_member_image', $post_id);
                $full_profile_image_id_new = $handle_image_upload('team_member_full_profile_image', 'team_member_full_profile_image', $post_id);
                $company_logo_id_new      = $handle_image_upload('team_member_logo_url', 'team_member_logo_url', $post_id); // Ensure this meta key matches your existing usage


                // Update the variables used for display in case of successful upload
                $image_id = $image_id_new;
                $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
                $full_profile_image_id = $full_profile_image_id_new;
                $full_profile_image_url = $full_profile_image_id ? wp_get_attachment_url($full_profile_image_id) : '';
                $company_logo_id = $company_logo_id_new;
                $company_logo_url = $company_logo_id ? wp_get_attachment_url($company_logo_id) : '';

                if (!empty($upload_errors)) {
                    $error_message = implode('<br>', $upload_errors);
                } else {
                                   
                    // Build clean redirect URL
                    $redirect_url = remove_query_arg(array('edit_id', 'edit'), $_SERVER['REQUEST_URI']);
                    $redirect_url = add_query_arg('success', 1, $redirect_url);
                    

                    // Ensure clean redirect
                    wp_safe_redirect($redirect_url);
                    exit;
                

                }
            }
        }
    }


    // Display messages after potential redirect or if there's an error
    if (isset($_GET['success']) && $_GET['success'] == 1) {
        $success_message = $is_edit ? 'Team Member updated successfully!' : 'Team Member added successfully!';
    }


    ob_start();
    ?><style>
        /*  Highlight missing fields */
        .highlight-missing {
            border: 2px solid #e53935 !important;
            background: #fff8f8 !important;
        }

        /* Team member form styles */

        .team-member-form {
            max-width: 100%;
            margin: 0 auto;
            margin-bottom: 24px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .team-member-form h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
        }

        .team-member-form label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #34495e;
        }

        .team-member-form input[type="text"],
        .team-member-form input[type="email"],
        .team-member-form input[type="number"],
        .team-member-form input[type="file"],
        .team-member-form select,
        .team-member-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fff;
        }

        .team-member-form .form-section {
            margin-bottom: 25px;
            padding: 20px;
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .team-member-form .form-section h3 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }

        .team-member-form button[type="submit"],
        #whatsapp-form button[type="submit"] {
            background: #3498db;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            width: 100%;
            transition: background 0.3s;
        }

        .team-member-form button[type="submit"]:hover {
            background: #2980b9;
        }

        .team-member-form .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .team-member-form .form-row>div {
            flex: 1;
        }

        .team-member-form .dropdown-section {
            position: relative;
            margin-bottom: 15px;
        }

        .team-member-form .dropdown-button {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            width: 100%;
            text-align: left;
            cursor: pointer;
            border-radius: 4px;
            color: #34495e;
            font-weight: bold;
        }

        .team-member-form .dropdown-content {
            display: none;
            position: absolute;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            z-index: 1;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .team-member-form .dropdown-content label {
            display: block;
            padding: 8px 15px;
            margin: 0;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .team-member-form .dropdown-content label:hover {
            background: #f8f9fa;
        }

        .team-member-form .dropdown-content label:last-child {
            border-bottom: none;
        }

        .team-member-form .passport-section {
            margin-top: 0px;
            padding: 20px;
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .team-member-form .passport-fields {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .team-member-form .form-row {
                flex-direction: column;
            }

            .team-member-form .passport-fields {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .team-member-form .passport-fields {
                grid-template-columns: 1fr;
            }
        }

        .form-loading {
            opacity: 0.5;
            pointer-events: none;
        }

        .spinner {
            display: none;
            margin-left: 10px;
        }

        .form-loading .spinner {
            display: inline-block;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 10px auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>


    <form method="POST" enctype="multipart/form-data" class="team-member-form" id="add-team-member-form">
        <?php if ($is_edit): ?>
            <div style="background:#e3f2fd;color:#1565c0;padding:10px 20px;border-radius:6px;margin-bottom:20px;font-weight:bold;">
                You are editing an existing candidate.
            </div>
        <?php endif; ?>

        <!-- Add this div just after the opening <form> tag -->
        <div id="form-error-message" class="error-message" style="display:none;"></div>

        <script>
            document.querySelector('.team-member-form').addEventListener('submit', function(e) {
                const requiredFields = this.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = 'red';
                    } else {
                        field.style.borderColor = '';
                    }
                });

                // Show error message at the top if not valid
                const errorDiv = document.getElementById('form-error-message');
                if (!isValid) {
                    e.preventDefault();
                    errorDiv.textContent = 'Please fill in all required fields before submitting the form.';
                    errorDiv.style.display = 'block';
                    // Optionally scroll to top of form
                    this.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                } else {
                    errorDiv.style.display = 'none';
                }
            });
        </script>
        <?php wp_nonce_field('add_team_member', 'team_member_nonce'); ?>

        <?php
        // Determine message type and text
        $popup_type = '';
        $popup_message = '';
        if (!empty($success_message)) {
            $popup_type = 'success';
            $popup_message = $success_message;
        } elseif (!empty($error_message)) {
            $popup_type = 'error';
            $popup_message = $error_message;
        } elseif (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
            $popup_type = 'deleted';
            $popup_message = 'Team Member deleted successfully!';
        } elseif (isset($_GET['updated']) && $_GET['updated'] == 1) {
            $popup_type = 'updated';
            $popup_message = 'Team Member updated successfully!';
        }
        ?>
        <div id="team-member-popup" class="team-member-popup" style="display:<?php echo $popup_type ? 'flex' : 'none'; ?>">
            <div class="popup-content <?php echo esc_attr($popup_type); ?>">
                <span class="popup-close" onclick="document.getElementById('team-member-popup').style.display='none'">&times;</span>
                <div class="popup-icon">
                    <?php
                    if ($popup_type == 'success' || $popup_type == 'updated') echo '&#10004;'; // checkmark
                    elseif ($popup_type == 'deleted') echo '&#128465;'; // trash
                    elseif ($popup_type == 'error') echo '&#9888;'; // warning
                    ?>
                </div>
                <div class="popup-message"><?php echo esc_html($popup_message); ?></div>
                <button class="popup-close-btn" onclick="document.getElementById('team-member-popup').style.display='none'">OK</button>
            </div>
        </div>
        <style>
            .team-member-popup {
                position: fixed;
                z-index: 9999;
                left: 0;
                top: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.3);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .team-member-popup .popup-content {
                background: #fff;
                border-radius: 12px;
                padding: 40px 30px 30px 30px;
                text-align: center;
                box-shadow: 0 8px 32px rgba(44, 62, 80, 0.18);
                position: relative;
                min-width: 320px;
                max-width: 90vw;
            }

            .team-member-popup .popup-icon {
                font-size: 48px;
                margin-bottom: 12px;
            }

            .team-member-popup .popup-content.success .popup-icon,
            .team-member-popup .popup-content.updated .popup-icon {
                color: #27ae60;
            }

            .team-member-popup .popup-content.error .popup-icon {
                color: #c62828;
            }

            .team-member-popup .popup-content.deleted .popup-icon {
                color: #e67e22;
            }

            .team-member-popup .popup-message {
                font-size: 20px;
                color: #222;
                margin-bottom: 10px;
            }

            .team-member-popup .popup-close {
                position: absolute;
                top: 10px;
                right: 18px;
                font-size: 28px;
                color: #aaa;
                cursor: pointer;
            }

            .popup-close-btn {
                margin-top: 10px;
                padding: 8px 24px;
                background: #3498db;
                color: #fff;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                cursor: pointer;
            }

            .popup-close-btn:hover {
                background: #2980b9;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var popup = document.getElementById('team-member-popup');
                if (popup && popup.style.display === 'flex') {
                    setTimeout(function() {
                        popup.style.display = 'none';
                    }, 4000); // auto-hide after 4 seconds
                }
            });
        </script>
        <style>
            .error-message {
                background: #ffebee;
                color: #c62828;
                border: 1px solid #ef9a9a;
                padding: 12px 18px;
                border-radius: 6px;
                margin-bottom: 18px;
                font-weight: 600;
                font-size: 1.1em;
                box-shadow: 0 2px 6px rgba(198, 40, 40, 0.05);
                text-align: center;
            }

            .success-message {
                background: #e8f5e9;
                color: #2e7d32;
                border: 1px solid #a5d6a7;
                padding: 12px 18px;
                border-radius: 6px;
                margin-bottom: 18px;
                font-weight: 600;
                font-size: 1.1em;
                box-shadow: 0 2px 6px rgba(46, 125, 50, 0.05);
                text-align: center;
            }


            .passport-scan-section {
                margin-bottom: 25px;
                padding: 20px;
                background: #fff;
                border-radius: 6px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .passport-preview {
                margin-top: 15px;
                text-align: center;
            }

            .passport-preview-container {
                margin-top: 15px;
                text-align: center;
            }

            #passport-preview {
                max-width: 100%;
                border-radius: 4px;
                margin-bottom: 10px;

            }


            .scan-status {
                padding: 10px;
                margin: 10px 0;
                border-radius: 4px;

            }


            .scan-status.error {
                background-color: #ffebee;
                color: #c62828;
                border: 1px solid #ef9a9a;
            }

            .scan-status.success {
                background-color: #e8f5e9;
                color: #2e7d32;
                border: 1px solid #a5d6a7;
            }

            .scan-status.processing {
                background-color: #e3f2fd;
                color: #1565c0;
                border: 1px solid #90caf9;
            }

            .reset-button {
                width: 250px;
                background: #f8f9fa;
                border: 1px solid #ddd;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                margin-top: 10px;
                transition: all 0.3s ease;
            }

            .reset-button:hover {
                background: #e9ecef;
            }

            .country-experience-item {
                display: flex;
                align-items: center;
                padding: 8px 15px;
                border-bottom: 1px solid #eee;
            }

            .country-experience-item .country-name {
                flex: 1;
                margin: 0 10px;
            }

            .year-input {
                width: 80px;
            }

            .year-input input {
                width: 100%;
                padding: 4px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            .field-description {
                font-size: 0.85em;
                color: #666;
                margin-top: 4px;
                margin-bottom: 8px;
            }

            .form-row {
                display: flex;
                gap: 15px;
                margin-bottom: 15px;
                flex-wrap: wrap;
            }

            .form-row>div {
                flex: 1;
                min-width: 200px;
            }

            .form-row select {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            /* Popup Styles */
            .team-member-popup {
                position: fixed;
                z-index: 9999;
                left: 0;
                top: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.3);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .team-member-popup .popup-content {
                background: #fff;
                border-radius: 12px;
                padding: 40px 30px 30px 30px;
                text-align: center;
                box-shadow: 0 8px 32px rgba(44, 62, 80, 0.18);
                position: relative;
                min-width: 320px;
            }

            .team-member-popup .popup-icon {
                font-size: 48px;
                color: #27ae60;
                margin-bottom: 12px;
            }

            .team-member-popup .popup-message {
                font-size: 20px;
                color: #222;
                margin-bottom: 10px;
            }

            .team-member-popup .popup-close {
                position: absolute;
                top: 10px;
                right: 18px;
                font-size: 28px;
                color: #aaa;
                cursor: pointer;
            }
        </style>

        <h2><?php echo $is_edit ? 'Edit Candidate' : 'Add New Candidate'; ?></h2>
        <div class="form-section passport-scan-section">
            <h3>Passport Scanner</h3>
            <div class="form-row">
                <div>

                    <div class="passport-upload-instructions" style="margin-bottom:10px; color:#1565c0;">
                        <strong>Tips for best results:</strong>
                        <ul style="margin:5px 0 0 18px; padding:0;">
                            <li>Place the passport on a flat, well-lit surface.</li>
                            <li>Make sure all text is visible and in focus.</li>
                            <li>Avoid glare, shadows, and fingers covering the text.</li>
                            <li>Align the passport so the MRZ (bottom two lines) is clear.</li>
                        </ul>
                    </div>
                    <label for="passport-file-input">Upload Passport Image:</label>
                    <div id="passport-js-loading" style="text-align:center; margin:20px 0;">
                        <div class="passport-spinner" style="display:inline-block; width:32px; height:32px; border:4px solid #eee; border-top:4px solid #3498db; border-radius:50%; animation:spin 1s linear infinite;"></div>
                        <div style="margin-top:8px; color:#1565c0;">Loading scanner...</div>
                    </div>
                    <input type="file" id="passport-file-input" accept="image/*" disabled>

                    <p class="field-description">Upload a clear image of the passport to automatically fill details</p>
                </div>
                <div class="passport-preview-container">
                    <img id="passport-preview" src="" alt="Passport Preview" style="max-width: 300px">
                    <div id="loading-spinner" class="spinner"></div>
                    <div id="scan-status" class="scan-status"></div>
                </div>
            </div>
            <button type="button" id="reset-fields-btn" class="reset-button">Reset Scanned Data</button>
        </div>



        <!-- Two-Column Main Form Section: Passport + Basic Info, then Personal + Skills, then Media -->
        <div class="form-row">
            <!-- LEFT: Passport Details -->
            <div class="form-section passport-section">
                <h3>Passport Details</h3>
                <div class="form-row">

                    <div>
                        <label for="team_member_nationality">Nationality:</label>
                        <select id="team_member_nationality" name="team_member_nationality" required>
                            <option value="">Select Nationality</option>
                            <option value="Ethiopia" <?php selected($nationality, 'Ethiopia'); ?>>Ethiopia</option>
                            <option value="Uganda" <?php selected($nationality, 'Uganda'); ?>>Uganda</option>
                            <option value="Philippines" <?php selected($nationality, 'Philippines'); ?>>Philippines</option>
                            <option value="Kenya" <?php selected($nationality, 'Kenya'); ?>>Kenya</option>
                            <option value="Indonesia" <?php selected($nationality, 'Indonesia'); ?>>Indonesia</option>
                            <option value="Sri Lanka" <?php selected($nationality, 'Sri Lanka'); ?>>Sri Lanka</option>
                            <option value="Vietnam" <?php selected($nationality, 'Vietnam'); ?>>Vietnam</option>
                            <option value="Nepal" <?php selected($nationality, 'Nepal'); ?>>Nepal</option>
                            <option value="Ghana" <?php selected($nationality, 'Ghana'); ?>>Ghana</option>
                            <option value="Myanmar" <?php selected($nationality, 'Myanmar'); ?>>Myanmar</option>
                            <option value="Bangladesh" <?php selected($nationality, 'Bangladesh'); ?>>Bangladesh</option>
                            <option value="Nigeria" <?php selected($nationality, 'Nigeria'); ?>>Nigeria</option>
                            <option value="Iraq" <?php selected($nationality, 'Iraq'); ?>>Iraq</option>
                            <option value="Lebanon" <?php selected($nationality, 'Lebanon'); ?>>Lebanon</option>
                        </select>
                    </div>
                    <div>
                        <label for="passport_number">Passport Number:</label>
                        <input type="text" id="passport_number" name="team_member_passport_number" placeholder="Passport Number" required value="<?php echo esc_attr($passport_number); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label for="passport_issue_date">Issue Date:</label>
                        <input type="date" id="passport_issue_date" name="team_member_passport_issue_date" required value="<?php echo esc_attr($passport_issue_date); ?>">
                    </div>
                    <div>
                        <label for="passport_expiry_date">Expiry Date:</label>
                        <input type="date" id="passport_expiry_date" name="team_member_passport_expiry_date" required value="<?php echo esc_attr($passport_expiry_date); ?>">
                    </div>
                    <div>
                        <label for="passport_place_of_issue">Place of Issue:</label>
                        <input type="text" id="passport_place_of_issue" name="team_member_passport_place_of_issue" placeholder="Place of Issue" required value="<?php echo esc_attr($passport_place_of_issue); ?>">
                    </div>
                </div>
            </div>
            <!-- RIGHT: Basic Information -->
            <div class="form-section">
                <h3>Basic Information</h3>
                <div class="form-row">
                    <div>
                        <label for="team_member_name">Name:</label>
                        <input id="team_member_name" type="text" name="team_member_name" placeholder="Full Name" required value="<?php echo esc_attr($name); ?>">
                    </div>
                    <div>
                        <label>Age:</label>
                        <input type="number" id="team_member_age" name="team_member_age" required value="<?php echo esc_attr($age); ?>">
                    </div>

                </div>
                <div class="form-row">
                    <div>
                        <label for="team_member_place_of_birth">Place of Birth</label>
                        <input type="text" name="team_member_place_of_birth" id="team_member_place_of_birth" value="<?php echo esc_attr($place_of_birth); ?>" required>
                    </div>
                    <div>
                        <label>Availability Status:</label>
                        <select name="team_member_status" required>
                            <option value="available" <?php selected($status, 'available'); ?>>Available</option>
                            <option value="hidden" <?php selected($status, 'hidden'); ?>>Hidden/Not Available</option>
                        </select>
                        <p class="field-description">Set to "Hidden" when candidate is not available or taken</p>
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label>Package:</label>
                        <select name="team_member_package" required>
                            <option value="">Select Package</option>
                            <option value="Traditional" <?php selected($package, 'Traditional'); ?>>Traditional</option>
                            <option value="Temporary" <?php selected($package, 'Temporary'); ?>>Temporary</option>
                            <option value="Long time live in" <?php selected($package, 'Long time live in'); ?>>Long time live in</option>
                            <option value="Flexible" <?php selected($package, 'Flexible'); ?>>Flexible</option>
                        </select>
                    </div>
                    <div>
                        <label>Years of Experience:</label>
                        <input type="number" name="team_member_experience" required value="<?php echo esc_attr($experience); ?>">
                    </div>
                </div>
                <label>Bio:</label>
                <textarea name="team_member_bio" required><?php echo esc_textarea($bio); ?></textarea>
            </div>
        </div>

        <div class="form-row">
            <!-- LEFT: Personal Information -->
            <div class="form-section">
                <h3>Personal Information</h3>
                <div class="form-row">
                    <div>
                        <label>Email:</label>
                        <input type="email" name="team_member_email" required value="<?php echo esc_attr($email); ?>">
                    </div>
                    <div>
                        <label>Phone:</label>
                        <input type="text" name="team_member_phone" required value="<?php echo esc_attr($phone); ?>">
                    </div>
                    <div>
                        <label for="team_member_weight">Weight (KG):</label>
                        <input type="number" step="0.1" min="0" name="team_member_weight" id="team_member_weight" required value="<?php echo esc_attr($weight); ?>">
                    </div>
                    <div>
                        <label for="team_member_height">Height (Meters):</label>
                        <input type="number" step="0.01" min="0" name="team_member_height" id="team_member_height" required value="<?php echo esc_attr($height); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label>Video Type:</label>
                        <input type="text" name="team_member_video" value="<?php echo esc_attr($video); ?>" placeholder="Enter YouTube or Vimeo URL">
                    </div>
                    <div>
                        <label>Address:</label>
                        <input type="text" name="team_member_address" required value="<?php echo esc_attr($address); ?>">
                    </div>
                    <div>
                        <label>City:</label>
                        <input type="text" name="team_member_city" required value="<?php echo esc_attr($city); ?>">
                    </div>
                    <div>
                        <label for="team_member_whatsapp_no">WhatsApp Number:</label>
                        <input type="text" id="team_member_whatsapp_no" name="team_member_whatsapp_no" required value="<?php echo esc_attr($whatsapp_no); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label>State:</label>
                        <input type="text" name="team_member_state" required value="<?php echo esc_attr($state); ?>">
                    </div>
                    <div>
                        <label>Country:</label>
                        <input type="text" name="team_member_country" required value="<?php echo esc_attr($country); ?>">
                    </div>
                    <div>
                        <label>PO Box:</label>
                        <input type="text" name="team_member_po_box" value="<?php echo esc_attr($po_box); ?>">
                    </div>
                </div>
            </div>

            <!-- RIGHT: Skills & Experience -->
            <div class="form-section">
                <h3>Skills & Experience</h3>
                <div class="form-row">
                    <div>
                        <label for="team_member_job">Job Categories</label>
                        <div class="custom-multiselect" data-target="team_member_job[]">
                            <div class="multiselect-header">
                                <span class="multiselect-placeholder">Select job categories</span>
                                <span class="multiselect-arrow">▼</span>
                            </div>
                            <div class="multiselect-options">
                                <?php
                                $job_options = [
                                    "Housemaid",
                                    "Housekeeper",
                                    "Cook",
                                    "Nanny/Babysitter",
                                    "Driver",
                                    "Security Guard",
                                    "Farmer",
                                    "Gardener",
                                    "Personal driver",
                                    "Cleaner",
                                    "Caregiver",
                                    "Tutor",
                                    "Babysitter",
                                    "Elder Care",
                                    "Pet Care"
                                ];
                                $selected_jobs = is_array($job) ? $job : explode(', ', trim($job));
                                foreach ($job_options as $option): ?>
                                    <label class="multiselect-option">
                                        <input type="checkbox" name="team_member_job[]" value="<?php echo esc_attr($option); ?>" <?php echo in_array(trim($option), array_map('trim', $selected_jobs)) ? 'checked' : ''; ?>>
                                        <span><?php echo esc_html($option); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <p class="field-description">Select multiple job categories</p>
                    </div>
                    <div>
                        <label for="team_member_mskills">Main Skills</label>
                        <div class="custom-multiselect" data-target="team_member_mskills[]">
                            <div class="multiselect-header">
                                <span class="multiselect-placeholder">Select main skills</span>
                                <span class="multiselect-arrow">▼</span>
                            </div>
                            <div class="multiselect-options">
                                <?php
                                $main_skills = [
                                    "Child Care",
                                    "Elderly Care",
                                    "Pet Care",
                                    "Housekeeping",
                                    "Cooking",
                                    "Driving",
                                    "Tutoring",
                                    "Cleaning",
                                    "Laundry",
                                    "Ironing",
                                    "Shopping",
                                    "First Aid",
                                    "Baby Care",
                                    "Disabled Care"
                                ];
                                $selected_mskills = is_array($mskills) ? $mskills : explode(', ', trim($mskills));
                                foreach ($main_skills as $skill): ?>
                                    <label class="multiselect-option">
                                        <input type="checkbox" name="team_member_mskills[]" value="<?php echo esc_attr($skill); ?>" <?php echo in_array(trim($skill), array_map('trim', $selected_mskills)) ? 'checked' : ''; ?>>
                                        <span><?php echo esc_html($skill); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <p class="field-description">Select multiple main skills</p>
                    </div>
                    <div>
                        <label for="team_member_personality">Personality Traits</label>
                        <div class="custom-multiselect" data-target="team_member_personality[]">
                            <div class="multiselect-header">
                                <span class="multiselect-placeholder">Select personality traits</span>
                                <span class="multiselect-arrow">▼</span>
                            </div>
                            <div class="multiselect-options">
                                <?php
                                $personality_traits = [
                                    "Friendly",
                                    "Hardworking",
                                    "Patient",
                                    "Honest",
                                    "Reliable",
                                    "Punctual",
                                    "Clean",
                                    "Organized",
                                    "Respectful",
                                    "Flexible",
                                    "Caring",
                                    "Energetic",
                                    "Calm",
                                    "Cheerful",
                                    "Responsible",
                                    "Trustworthy",
                                    "Polite",
                                    "Helpful",
                                    "Obedient",
                                    "Loyal",
                                    "Kind",
                                    "Gentle",
                                    "Quick Learner",
                                    "Detail-oriented",
                                    "Independent"
                                ];
                                $selected_personality = is_array($personality) ? $personality : explode(', ', trim($personality));
                                foreach ($personality_traits as $trait): ?>
                                    <label class="multiselect-option">
                                        <input type="checkbox" name="team_member_personality[]" value="<?php echo esc_attr($trait); ?>" <?php echo in_array(trim($trait), array_map('trim', $selected_personality)) ? 'checked' : ''; ?>>
                                        <span><?php echo esc_html($trait); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <p class="field-description">Select multiple personality traits</p>
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label for="team_member_cskills">Cooking Skills</label>
                        <div class="custom-multiselect" data-target="team_member_cskills[]">
                            <div class="multiselect-header">
                                <span class="multiselect-placeholder">Select cooking styles</span>
                                <span class="multiselect-arrow">▼</span>
                            </div>
                            <div class="multiselect-options">
                                <?php
                                $cooking_skills = [
                                    "Middle East",
                                    "Vegetarian",
                                    "Western",
                                    "Asian",
                                    "African",
                                    "Indian",
                                    "Filipino",
                                    "Chinese",
                                    "Italian",
                                    "Mexican",
                                    "Thai",
                                    "Japanese",
                                    "Mediterranean",
                                    "Arabic",
                                    "Lebanese",
                                    "Turkish"
                                ];
                                $selected_cskills = is_array($cskills) ? $cskills : explode(', ', trim($cskills));
                                foreach ($cooking_skills as $cskill_item): ?>
                                    <label class="multiselect-option">
                                        <input type="checkbox" name="team_member_cskills[]" value="<?php echo esc_attr($cskill_item); ?>" <?php echo in_array(trim($cskill_item), array_map('trim', $selected_cskills)) ? 'checked' : ''; ?>>
                                        <span><?php echo esc_html($cskill_item); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <p class="field-description">Select multiple cooking styles</p>
                    </div>
                    <div>
                        <label for="team_member_oskills">Other Skills</label>
                        <div class="custom-multiselect" data-target="team_member_oskills[]">
                            <div class="multiselect-header">
                                <span class="multiselect-placeholder">Select other skills</span>
                                <span class="multiselect-arrow">▼</span>
                            </div>
                            <div class="multiselect-options">
                                <?php
                                $other_skills = [
                                    "Caregiver",
                                    "Banking",
                                    "Computer",
                                    "Driving License",
                                    "First Aid",
                                    "Gardening",
                                    "Handyman",
                                    "Housework",
                                    "Sewing",
                                    "Swimming",
                                    "Car Wash",
                                    "Massage",
                                    "Hair Care",
                                    "Nursing",
                                    "Teaching",
                                    "Office Work",
                                    "Sales",
                                    "Customer Service"
                                ];
                                $selected_oskills = is_array($oskills) ? $oskills : explode(', ', trim($oskills));
                                foreach ($other_skills as $oskill_item): ?>
                                    <label class="multiselect-option">
                                        <input type="checkbox" name="team_member_oskills[]" value="<?php echo esc_attr($oskill_item); ?>" <?php echo in_array(trim($oskill_item), array_map('trim', $selected_oskills)) ? 'checked' : ''; ?>>
                                        <span><?php echo esc_html($oskill_item); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <p class="field-description">Select multiple other skills</p>
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label for="team_member_languages">Languages</label>
                        <div class="custom-multiselect" data-target="team_member_languages[]">
                            <div class="multiselect-header">
                                <span class="multiselect-placeholder">Select languages</span>
                                <span class="multiselect-arrow">▼</span>
                            </div>
                            <div class="multiselect-options">
                                <?php
                                $languages_list = [
                                    "English",
                                    "Arabic",
                                    "French",
                                    "Chinese",
                                    "Spanish",
                                    "Hindi",
                                    "Portuguese",
                                    "Bengali",
                                    "Russian",
                                    "Amharic",
                                    "Tagalog",
                                    "Indonesian",
                                    "Swahili",
                                    "Hausa",
                                    "Yoruba",
                                    "Igbo",
                                    "Oromo",
                                    "Urdu",
                                    "Japanese",
                                    "Punjabi",
                                    "Vietnamese",
                                    "Marathi",
                                    "Tamil",
                                    "Telugu",
                                    "Korean",
                                    "German",
                                    "Italian"
                                ];
                                $selected_languages = is_array($languages) ? $languages : explode(', ', trim($languages));
                                foreach ($languages_list as $language_item): ?>
                                    <label class="multiselect-option">
                                        <input type="checkbox" name="team_member_languages[]" value="<?php echo esc_attr($language_item); ?>" <?php echo in_array(trim($language_item), array_map('trim', $selected_languages)) ? 'checked' : ''; ?>>
                                        <span><?php echo esc_html($language_item); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <p class="field-description">Select multiple languages</p>
                    </div>
                    <div>
                        <label for="team_member_religion">Religion</label>
                        <select name="team_member_religion" id="team_member_religion" required>
                            <option value="">Select Religion</option>
                            <option value="Christianity" <?php selected($religion, 'Christianity'); ?>>Christianity</option>
                            <option value="Islam" <?php selected($religion, 'Islam'); ?>>Islam</option>
                            <option value="Hinduism" <?php selected($religion, 'Hinduism'); ?>>Hinduism</option>
                            <option value="Buddhism" <?php selected($religion, 'Buddhism'); ?>>Buddhism</option>
                            <option value="Other" <?php selected($religion, 'Other'); ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label for="team_member_civil_status">Civil Status</label>
                        <select name="team_member_civil_status" id="team_member_civil_status" required>
                            <option value="">Select Status</option>
                            <option value="Single" <?php selected($civil_status, 'Single'); ?>>Single</option>
                            <option value="Married" <?php selected($civil_status, 'Married'); ?>>Married</option>
                            <option value="Divorced" <?php selected($civil_status, 'Divorced'); ?>>Divorced</option>
                            <option value="Widowed" <?php selected($civil_status, 'Widowed'); ?>>Widowed</option>
                        </select>
                    </div>
                    <div>
                        <label for="team_member_education">Highest Education Level</label>
                        <select name="team_member_education" id="team_member_education" required>
                            <option value="">Select Education</option>
                            <option value="None" <?php selected($education, 'None'); ?>>None</option>
                            <option value="Primary" <?php selected($education, 'Primary'); ?>>Primary</option>
                            <option value="Secondary / High School" <?php selected($education, 'Secondary / High School'); ?>>Secondary / High School</option>
                            <option value="Diploma" <?php selected($education, 'Diploma'); ?>>Diploma</option>
                            <option value="Bachelor" <?php selected($education, 'Bachelor'); ?>>Bachelor</option>
                            <option value="Master" <?php selected($education, 'Master'); ?>>Master</option>
                        </select>
                    </div>
                </div>
                <!-- Improved Country Experience section -->
                <div class="form-row">
                    <div>
                        <label>Country Experience</label>
                        <div class="custom-multiselect" data-target="team_member_countries[]">
                            <div class="multiselect-header">
                                <span class="multiselect-placeholder">Select countries with experience</span>
                                <span class="multiselect-arrow">▼</span>
                            </div>
                            <div class="multiselect-options">
                                <?php
                                $country_list = [
                                    "UAE" => "🇦🇪",
                                    "Saudi Arabia" => "🇸🇦",
                                    "Qatar" => "🇶🇦",
                                    "Kuwait" => "🇰🇼",
                                    "Oman" => "🇴🇲",
                                    "Bahrain" => "🇧🇭",
                                    "Singapore" => "🇸🇬",
                                    "Hong Kong" => "🇭🇰",
                                    "Malaysia" => "🇲🇾",
                                    "Iraq" => "🇮🇶",
                                    "Lebanon" => "🇱🇧",
                                    "Beirut" => "🇱🇧"
                                ];
                                $country_experience = is_array($country_experience) ? $country_experience : [];
                                foreach ($country_list as $country => $flag):
                                    $years = isset($country_experience[$country]) ? intval($country_experience[$country]) : '';
                                ?>
                                    <label class="multiselect-option country-option">
                                        <input type="checkbox" name="team_member_countries[]" value="<?php echo esc_attr($country); ?>" <?php echo $years ? 'checked' : ''; ?>>
                                        <span class="country-flag"><?php echo $flag . ' ' . esc_html($country); ?></span>
                                        <input type="number" name="country_years[<?php echo esc_attr($country); ?>]" min="1" max="40" placeholder="Years" value="<?php echo $years ? esc_attr($years) : ''; ?>" class="years-input" style="display:<?php echo $years ? 'inline-block' : 'none'; ?>;">
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <small class="field-description">Select countries and enter years of experience.</small>
                    </div>
                </div>
            </div>

            <style>
                /* Custom Multiselect Dropdown Styles */
                .custom-multiselect {
                    position: relative;
                    width: 100%;
                    margin-bottom: 15px;
                }

                .multiselect-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 10px;
                    border: 2px solid #ddd;
                    border-radius: 6px;
                    background: #fff;
                    cursor: pointer;
                    transition: border-color 0.3s ease;
                    min-height: 42px;
                }

                .multiselect-header:hover {
                    border-color: #3498db;
                }

                .multiselect-header.active {
                    border-color: #3498db;
                    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
                }

                .multiselect-placeholder {
                    color: #666;
                    font-size: 14px;
                    flex: 1;
                }

                .multiselect-placeholder.has-selections {
                    color: #333;
                    font-weight: 500;
                }

                .multiselect-arrow {
                    color: #666;
                    transition: transform 0.3s ease;
                    font-size: 12px;
                }

                .multiselect-header.active .multiselect-arrow {
                    transform: rotate(180deg);
                }

                .multiselect-options {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    background: #fff;
                    border: 1px solid #ddd;
                    border-radius: 6px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    z-index: 1000;
                    max-height: 250px;
                    overflow-y: auto;
                    display: none;
                    margin-top: 2px;
                }

                .multiselect-options.open {
                    display: block;
                }

                .multiselect-option {
                    display: flex;
                    align-items: center;
                    padding: 8px 12px;
                    cursor: pointer;
                    border-bottom: 1px solid #f0f0f0;
                    transition: background-color 0.2s ease;
                    margin: 0;
                    font-weight: normal;
                }

                .multiselect-option:last-child {
                    border-bottom: none;
                }

                .multiselect-option:hover {
                    background-color: #f8f9fa;
                }

                .multiselect-option input[type="checkbox"] {
                    margin-right: 8px;
                    margin-bottom: 0;
                    width: auto;
                    padding: 0;
                }

                .multiselect-option span {
                    flex: 1;
                    font-size: 14px;
                }

                /* Country experience specific styles */
                .country-option {
                    justify-content: space-between;
                    align-items: center;
                }

                .country-flag {
                    flex: 1;
                    margin-right: 8px;
                }

                .years-input {
                    width: 60px;
                    padding: 4px 6px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-size: 12px;
                    margin-left: 8px;
                }

                /* Selected items display */
                .selected-items {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 4px;
                    margin-bottom: 8px;
                }

                .selected-item {
                    background: #3498db;
                    color: white;
                    padding: 2px 8px;
                    border-radius: 12px;
                    font-size: 12px;
                    display: flex;
                    align-items: center;
                    gap: 4px;
                }

                .selected-item .remove {
                    cursor: pointer;
                    font-weight: bold;
                }

                /* Scrollbar for options */
                .multiselect-options::-webkit-scrollbar {
                    width: 6px;
                }

                .multiselect-options::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 4px;
                }

                .multiselect-options::-webkit-scrollbar-thumb {
                    background: #c1c1c1;
                    border-radius: 4px;
                }

                /* Responsive */
                @media (max-width: 768px) {
                    .multiselect-options {
                        max-height: 200px;
                    }

                    .multiselect-option {
                        padding: 12px;
                        font-size: 16px;
                        /* Prevent zoom on iOS */
                    }
                }

                .field-description {
                    font-size: 12px;
                    color: #666;
                    margin-top: 5px;
                    font-style: italic;
                }

                /* Better spacing for form rows */
                .team-member-form .form-row {
                    margin-bottom: 20px;
                }

                .team-member-form .form-row>div {
                    flex: 1;
                    min-width: 250px;
                }

                /* Responsive adjustments */
                @media (max-width: 768px) {
                    .team-member-form .form-row>div {
                        min-width: 100%;
                    }
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Initialize all custom multiselects
                    const multiselects = document.querySelectorAll('.custom-multiselect');

                    multiselects.forEach(multiselect => {
                        const header = multiselect.querySelector('.multiselect-header');
                        const options = multiselect.querySelector('.multiselect-options');
                        const placeholder = multiselect.querySelector('.multiselect-placeholder');
                        const checkboxes = multiselect.querySelectorAll('input[type="checkbox"]');

                        // Toggle dropdown
                        header.addEventListener('click', function(e) {
                            e.stopPropagation();

                            // Close other dropdowns
                            multiselects.forEach(other => {
                                if (other !== multiselect) {
                                    other.querySelector('.multiselect-header').classList.remove('active');
                                    other.querySelector('.multiselect-options').classList.remove('open');
                                }
                            });

                            // Toggle current dropdown
                            header.classList.toggle('active');
                            options.classList.toggle('open');
                        });

                        // Handle checkbox changes
                        checkboxes.forEach(checkbox => {
                            checkbox.addEventListener('change', function() {
                                updatePlaceholder();

                                // Handle country experience years input
                                if (multiselect.dataset.target === 'team_member_countries[]') {
                                    const yearsInput = this.closest('.country-option').querySelector('.years-input');
                                    if (yearsInput) {
                                        yearsInput.style.display = this.checked ? 'inline-block' : 'none';
                                        if (!this.checked) {
                                            yearsInput.value = '';
                                        }
                                    }
                                }
                            });
                        });

                        // Update placeholder text
                        function updatePlaceholder() {
                            const checked = multiselect.querySelectorAll('input[type="checkbox"]:checked');
                            if (checked.length === 0) {
                                placeholder.textContent = placeholder.getAttribute('data-default') || 'Select options';
                                placeholder.classList.remove('has-selections');
                            } else if (checked.length === 1) {
                                placeholder.textContent = checked[0].nextElementSibling.textContent;
                                placeholder.classList.add('has-selections');
                            } else {
                                placeholder.textContent = `${checked.length} items selected`;
                                placeholder.classList.add('has-selections');
                            }
                        }

                        // Set default placeholder text
                        const defaultText = header.querySelector('.multiselect-placeholder').textContent;
                        placeholder.setAttribute('data-default', defaultText);

                        // Initialize placeholder
                        updatePlaceholder();
                    });

                    // Close dropdowns when clicking outside
                    document.addEventListener('click', function() {
                        multiselects.forEach(multiselect => {
                            multiselect.querySelector('.multiselect-header').classList.remove('active');
                            multiselect.querySelector('.multiselect-options').classList.remove('open');
                        });
                    });

                    // Prevent dropdown from closing when clicking inside options
                    document.querySelectorAll('.multiselect-options').forEach(options => {
                        options.addEventListener('click', function(e) {
                            e.stopPropagation();
                        });
                    });
                });
            </script>

        </div>

        <div class="form-section">
            <h3>Media Uploads</h3>
            <div class="form-row">
                <div>
                    <label>Profile Image:</label>
                    <?php if (!empty($image_url)): ?>
                        <div>
                            <img src="<?php echo esc_url($image_url); ?>" alt="Current Profile Image" style="max-width:100px;max-height:100px;">
                            <br>
                            <small>Current Image</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="team_member_image">
                </div>
                <div>
                    <label>Full Profile Image:</label>
                    <?php if (!empty($full_profile_image_url)): ?>
                        <div>
                            <img src="<?php echo esc_url($full_profile_image_url); ?>" alt="Current Full Profile Image" style="max-width:100px;max-height:100px;">
                            <br>
                            <small>Current Full Profile Image</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="team_member_full_profile_image">
                </div>
                <div>
                    <label>Company Logo:</label>
                    <?php if (!empty($company_logo_url)): ?>
                        <div>
                            <img src="<?php echo esc_url($company_logo_url); ?>" alt="Current Company Logo" style="max-width:100px;max-height:100px;">
                            <br>
                            <small>Current Company Logo</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="team_member_logo_url">
                </div>
            </div>
        </div>





        <button type="submit" name="submit_team_member">
            <?php echo $is_edit ? 'Update Team Member' : 'Add Team Member'; ?>
        </button>

        <!-- Success Message Popup -->
        <div id="team-member-success-popup" class="team-member-popup" style="display:none;">
            <div class="popup-content">
                <span class="popup-close" onclick="document.getElementById('team-member-success-popup').style.display='none'">&times;</span>
                <div class="popup-icon">&#10004;</div>
                <div class="popup-message" id="popup-message-text"></div>
            </div>
        </div>


        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Function to toggle dropdown visibility
                function toggleDropdown(dropdownId) {
                    const dropdown = document.getElementById(dropdownId);
                    if (dropdown) {
                        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                    }
                }

                // Add event listeners for dropdown buttons
                const dropdownButtons = document.querySelectorAll('[data-dropdown-button]');
                dropdownButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const dropdownId = this.getAttribute('data-dropdown-target');
                        toggleDropdown(dropdownId);
                    });
                });

                // Close dropdowns when clicking outside
                document.addEventListener('click', function(event) {
                    dropdownButtons.forEach(button => {
                        const dropdownId = button.getAttribute('data-dropdown-target');
                        const dropdown = document.getElementById(dropdownId);
                        if (dropdown && !event.target.closest(`#${dropdownId}`) && !event.target.closest(`[data-dropdown-target="${dropdownId}"]`)) {
                            dropdown.style.display = 'none';
                        }
                    });
                });


                const countryCheckboxes = document.querySelectorAll('.country-experience-item input[type="checkbox"]');

                countryCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const yearInput = this.closest('.country-experience-item').querySelector('.year-input');
                        yearInput.style.display = this.checked ? 'block' : 'none';

                        if (!this.checked) {
                            yearInput.querySelector('input').value = '';
                        }
                    });
                });


                // Close dropdowns when clicking outside
                document.addEventListener('click', function(event) {
                    dropdownButtons.forEach(button => {
                        const dropdownId = button.getAttribute('data-dropdown-target');
                        const dropdown = document.getElementById(dropdownId);
                        if (dropdown && !event.target.closest(`#${dropdownId}`) && !event.target.closest(`[data-dropdown-target="${dropdownId}"]`)) {
                            dropdown.style.display = 'none';
                        }
                    });
                });
            });
        </script>


        <script>
            function showTeamMemberPopup(message, redirectUrl = null) {
                document.getElementById('popup-message-text').innerHTML = message;
                document.getElementById('team-member-success-popup').style.display = 'flex';
                setTimeout(function() {
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    } else {
                        document.getElementById('team-member-success-popup').style.display = 'none';
                    }
                }, 2000);
            }
        </script>

    </form>



<?php
    return ob_get_clean();
}
add_shortcode('add_team_member_form', 'add_team_member_form');

// Display Team Members for Each User
function user_team_members_list()
{
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to manage team members.</p>';
    }

    $user_id = get_current_user_id();
    $args = array(
        'post_type'      => 'team_member',
        'author'         => $user_id,
        'posts_per_page' => -1
    );
    $query = new WP_Query($args);

    ob_start();
?>
    <div class="team-members-container">
        <h2>Your Candidates</h2>
        <div class="search-bar">
            <input type="text" id="member-search" placeholder="Search candidates...">
            <span class="member-count"><?php echo $query->found_posts; ?> Candidates</span>
        </div>

        <div class="team-members-grid">
            <?php
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $post_id = get_the_ID();

                    // Get all the meta data
                    $name = get_the_title() ?: get_post_meta($post_id, 'name', true);
                    $nationality = get_post_meta($post_id, 'nationality', true);
                    $job = get_post_meta($post_id, 'job', true);
                    $package = get_post_meta($post_id, 'package', true);
                    $age = get_post_meta($post_id, 'age', true);
                    $bio = get_post_meta($post_id, 'bio', true);
                    $video = get_post_meta($post_id, 'video', true);
                    $viewer_count = get_post_meta($post_id, 'viewer_count', true) ?: 0;
                    $image_id = get_post_meta($post_id, 'team_member_image', true);
                    $image_url = '';
                    if ($image_id) {
                        $image_url = wp_get_attachment_url($image_id);
                    } else {
                        $image_url = get_the_post_thumbnail_url($post_id, 'medium');
                    }
                    if (!$image_url) {
                        $image_url = 'https://via.placeholder.com/150';
                    }

                    // Convert lists to arrays
                    $job_list = explode(',', $job);
            ?>
                    <?php
                    $status = get_post_meta($post_id, 'team_member_status', true) ?: 'available';
                    $status_class = $status === 'hidden' ? 'candidate-hidden' : '';
                    $status_label = $status === 'hidden' ? '<span class="status-label">Not Available</span>' : '';
                    ?>

                    <div class="team-member-card <?php echo $status_class; ?>">
                        <?php echo $status_label; ?>
                        <div class="team-member-image">
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($name); ?>">
                        </div>
                        <div class="team-member-info">
                            <strong><?php echo esc_html($name); ?></strong>
                            <small>Views: <?php echo esc_html($viewer_count); ?></small>
                            <p><?php echo esc_html($age); ?> • <?php echo esc_html($nationality); ?></p>
                            <p>Package: <?php echo esc_html($package); ?></p>

                            <?php if ($bio): ?>
                                <p class="bio"><?php echo wp_trim_words(esc_html($bio), 20); ?></p>
                            <?php endif; ?>

                            <?php if ($video): ?>
                                <?php
                                $embed_url = get_video_embed_url($video);
                                if ($embed_url):
                                ?>
                                    <div class="video-container">
                                        <iframe
                                            src="<?php echo esc_url($embed_url); ?>"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                <?php else: ?>
                                    <p class="video-error">Invalid video URL format</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="team-member-actions">
                            <a href="<?php echo esc_url(add_query_arg('edit_id', $post_id, site_url('/add-candidates/'))); ?>">Edit</a>
                            <a href="?delete=<?php echo $post_id; ?>"
                                onclick="return confirm('Are you sure?')">Delete</a>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo '<p>No team members found.</p>';
            }
            ?>
        </div>
    </div>
<?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('user_team_members_list', 'user_team_members_list');


// Delete Team Member Function
function delete_team_member()
{
    if (isset($_GET['delete']) && is_user_logged_in()) {
        $post_id = intval($_GET['delete']);
        $post = get_post($post_id);

        if ($post && $post->post_author == get_current_user_id()) {
            wp_delete_post($post_id);
            wp_redirect(remove_query_arg('delete'));
            exit;
        }
    }
}
add_action('init', 'delete_team_member');


// Display Team Members for a Specific User (via Shortcode)
function display_user_team_members_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'user_id' => get_the_author_meta('ID'),
    ), $atts, 'display_user_team_members');

    $user_id = intval($atts['user_id']);
    $current_user_id = get_current_user_id();

    if ($user_id <= 0) {
        return '<p>No candidate found for this user.</p>';
    }

    $args = array(
        'post_type' => 'team_member',
        'author' => $user_id,
        'posts_per_page' => -1,
        'meta_query' => array()
    );

    // Show all candidates (including hidden) only to:
    // 1. The owner of the candidates
    // 2. Administrators    
    if ($current_user_id !== $user_id && !current_user_can('edit_others_posts')) {
        $args['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key' => 'team_member_status',
                'value' => 'available'
            ),
            array(
                'key' => 'team_member_status',
                'compare' => 'NOT EXISTS'
            )
        );
    }

    $query = new WP_Query($args);
    ob_start();

    if ($query->have_posts()) {
        echo '<h3 style="text-align: center; margin-top:20px;">Candidates:</h3>';
        echo '<div class="team-members-list">';

        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $name = esc_html(get_the_title());
            $nationality = esc_html(get_post_meta($post_id, 'nationality', true));
            $package = esc_html(get_post_meta($post_id, 'package', true));
            $job = esc_html(get_post_meta($post_id, 'job', true));
            $age = esc_html(get_post_meta($post_id, 'age', true));
            $viewer_count = get_post_meta($post_id, 'viewer_count', true);
            $viewer_count = $viewer_count ? intval($viewer_count) : 0;

            $image_id = get_post_meta($post_id, 'team_member_image', true);
            $image_url = '';
            if ($image_id) {
                $image_url = wp_get_attachment_url($image_id);
            } else {
                $image_url = get_the_post_thumbnail_url($post_id, 'medium');
            }
            if (!$image_url) {
                $image_url = 'https://via.placeholder.com/150';
            }

            // Link to the custom profile page, passing the team member ID
            $profile_link = site_url('/candidate/?id=' . $post_id);

            echo "<div class='team-member-card'>
                <div class='favorite-heart'
                    data-member-id='" . esc_attr($post_id) . "'
                    data-member-name='" . esc_attr($name) . "'
                    data-member-age='" . esc_attr($age) . "'
                    data-member-nationality='" . esc_attr($nationality) . "'
                    data-member-package='" . esc_attr($package) . "'
                    data-member-image='" . esc_url($image_url) . "'
                    data-member-link='" . esc_url($profile_link) . "'
                    title='Add to Favorites'>
                    <i class='far fa-heart'></i>
                </div>
                <div class='team-member-image'>
                    <img src='" . esc_url($image_url) . "' alt='" . esc_attr($name) . "' />
                </div>
                <div class='team-member-info'>
                    <strong>{$name}</strong>
                    <small>Views: $viewer_count</small>
                    <div class='member-meta'>
                        <span>{$age}</span>
                        <span>•</span>
                        " . get_country_flag($nationality) . "
                    </div>
                    Package: {$package}
                    <a href='" . esc_url($profile_link) . "' class='view-profile-btn'>View Profile</a>
                </div>      
            </div>";
        }
        echo '</div>';
    } else {
        echo '<p>No candidate found for this user.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}

add_shortcode('display_user_team_members', 'display_user_team_members_shortcode');




function add_status_query_args($args)
{
    // Add meta query for status
    if (!isset($args['meta_query'])) {
        $args['meta_query'] = array();
    }

    // Only show available candidates unless user is author or admin
    if (!current_user_can('edit_others_posts')) {
        $args['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key' => 'team_member_status',
                'value' => 'available'
            ),
            array(
                'key' => 'team_member_status',
                'compare' => 'NOT EXISTS'
            )
        );
    }

    return $args;
}




function display_all_team_members_shortcode($atts)
{
    $args = array(
        'post_type' => 'team_member',
        'posts_per_page' => -1
    );

    $args = add_status_query_args($args);
    $query = new WP_Query($args);

    global $wpdb;

    $nationalities = array('Ethiopia', 'Uganda', 'Philippines', 'Kenya', 'Indonesia', 'Sri Lanka', 'Vietnam', 'Nepal', 'Ghana', 'Myanmar', 'Bangladesh', 'Nigeria');
    $packages = array('Traditional', 'Temporary', 'Long time live in', 'Flexible');
    $job_categories = array(
        "Housemaid",
        "Sailor",
        "Security Guard",
        "Household Shepard",
        "Household horse groomer",
        "Household falcon trainer",
        "Tamer",
        "Physical labour worker",
        "Housekeeper",
        "Cook",
        "Nanny/Babysitter",
        "Farmer",
        "Gardener",
        "Personal driver"
    );

    ob_start();
?>
    <style>
        /* Add these styles for the ghost button */
        .view-all-button {
            display: block;
            width: fit-content;
            margin: 20px auto;
            padding: 12px 24px;
            background: transparent;
            border: 1px solid #024CAA;
            color: #024CAA;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
        }

        .view-all-button:hover {
            background: #FF851B;
            color: #024CAA;
            border: 0px;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
        }
    </style>

    <div id="team-members-container" style="overflow-x: auto; white-space: nowrap; scroll-behavior: smooth;">
        <?php echo fetch_filtered_team_members([]); // Load initial results 
        ?>
    </div>

    <a href="https://tadbeer.center/all-candidates/" class="view-all-button">
        View All Candidates
    </a>




    <script>
        (function() {
            const container = document.getElementById('team-members-container');
            if (!container) return;

            let scrollAmount = 0;
            let scrollStep = 1; // pixels per frame
            let maxScroll = container.scrollWidth - container.clientWidth;
            let scrolling = false;

            function isInViewport(element) {
                const rect = element.getBoundingClientRect();
                return (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
                );
            }

            function startScrolling() {
                if (scrolling) return;
                scrolling = true;
                requestAnimationFrame(scrollStepFunc);
            }

            function stopScrolling() {
                scrolling = false;
            }

            function scrollStepFunc() {
                if (!scrolling) return;
                scrollAmount += scrollStep;
                if (scrollAmount >= maxScroll) {
                    scrollAmount = 0; // reset to start
                }
                container.scrollLeft = scrollAmount;
                requestAnimationFrame(scrollStepFunc);
            }

            function onScroll() {
                if (isInViewport(container)) {
                    startScrolling();
                } else {
                    stopScrolling();
                }
            }

            window.addEventListener('scroll', onScroll);
            window.addEventListener('resize', () => {
                maxScroll = container.scrollWidth - container.clientWidth;
                onScroll();
            });

            // Initial check
            onScroll();
        })();
    </script>

<?php
    return ob_get_clean();
}
add_shortcode('display_all_team_members', 'display_all_team_members_shortcode');

function fetch_filtered_team_members($filters)
{
    $args = array(
        'post_type'      => 'team_member',
        'posts_per_page' => -1,
        'meta_query'     => array('relation' => 'AND')
    );

    if (!empty($filters['nationality'])) {
        $args['meta_query'][] = array('key' => 'nationality', 'value' => $filters['nationality'], 'compare' => '=');
    }
    if (!empty($filters['package'])) {
        $args['meta_query'][] = array('key' => 'package', 'value' => $filters['package'], 'compare' => '=');
    }
    if (!empty($filters['job_category'])) {
        $args['meta_query'][] = array('key' => 'job', 'value' => $filters['job_category'], 'compare' => 'LIKE');
    }
    if (!empty($filters['age'])) {
        if ($filters['age'] == 'under_30') {
            $args['meta_query'][] = array('key' => 'age', 'value' => 30, 'compare' => '<', 'type' => 'NUMERIC');
        } elseif ($filters['age'] == '30_50') {
            $args['meta_query'][] = array('key' => 'age', 'value' => array(30, 50), 'compare' => 'BETWEEN', 'type' => 'NUMERIC');
        } elseif ($filters['age'] == 'above_50') {
            $args['meta_query'][] = array('key' => 'age', 'value' => 50, 'compare' => '>', 'type' => 'NUMERIC');
        }
    }

    $query = new WP_Query($args);
    ob_start();

    if ($query->have_posts()) {
        echo '<div class="team-members-container">';
        echo '<div class="team-members-list" style="display: flex; gap: 15px; overflow-x: auto; white-space: nowrap; padding: 10px;">';

        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $name = esc_html(get_the_title());
            $nationality = esc_html(get_post_meta($post_id, 'nationality', true));
            $package = esc_html(get_post_meta($post_id, 'package', true));
            $job = esc_html(get_post_meta($post_id, 'job', true));
            $age = esc_html(get_post_meta($post_id, 'age', true));
            $viewer_count = get_post_meta($post_id, 'viewer_count', true);
            $viewer_count = $viewer_count ? intval($viewer_count) : 0;
            $image_id = get_post_meta($post_id, 'team_member_image', true);
            $image_url = '';
            if ($image_id) {
                $image_url = wp_get_attachment_url($image_id);
            } else {
                $image_url = get_the_post_thumbnail_url($post_id, 'medium');
            }
            if (!$image_url) {
                $image_url = 'https://via.placeholder.com/150';
            }
            $profile_link = site_url('/candidate/?id=' . $post_id);

            $job_list = '<ol>';
            $job_array = explode(',', $job);
            foreach ($job_array as $single_job) {
                $job_list .= '<li>' . esc_html(trim($single_job)) . '</li>';
            }
            $job_list .= '</ol>';


            echo "<div class='team-member-card'>
                <div class='favorite-heart'
                    data-member-id='" . esc_attr($post_id) . "'
                    data-member-name='" . esc_attr($name) . "'
                    data-member-age='" . esc_attr($age) . "'
                    data-member-nationality='" . esc_attr($nationality) . "'
                    data-member-package='" . esc_attr($package) . "'
                    data-member-image='" . esc_url($image_url) . "'
                    data-member-link='" . esc_url($profile_link) . "'
                    title='Add to Favorites'>
                    <i class='far fa-heart'></i>
                </div>
                <div class='team-member-image'>
                    <img src='" . esc_url($image_url) . "' alt='" . esc_attr($name) . "' />
                </div>
                <div class='team-member-info'>
                    <strong>{$name}</strong>
                    <small>Views: $viewer_count</small>
                    <div class='member-meta'>
                        <span>{$age}</span>
                        <span>•</span>
                        " . get_country_flag($nationality) . "
                    </div>
                    Package: {$package}
                    <a href='" . esc_url($profile_link) . "' class='view-profile-btn'>View Profile</a>
                </div>      
            </div>";
        }

        echo '</div>';
        echo '</div>';
    } else {
        echo '<p style="text-align: center;">No team members found.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}

// AJAX handler
function filter_team_members_ajax()
{
    $filters = array(
        'nationality'  => isset($_POST['nationality']) ? sanitize_text_field($_POST['nationality']) : '',
        'package'      => isset($_POST['package']) ? sanitize_text_field($_POST['package']) : '',
        'job_category' => isset($_POST['job_category']) ? sanitize_text_field($_POST['job_category']) : '',
        'age'          => isset($_POST['age']) ? sanitize_text_field($_POST['age']) : ''
    );

    echo fetch_filtered_team_members($filters);
    wp_die();
}
add_action('wp_ajax_filter_team_members', 'filter_team_members_ajax');
add_action('wp_ajax_nopriv_filter_team_members', 'filter_team_members_ajax');



function add_team_member_search_script()
{
?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('member-search');
            if (!searchInput) return;

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const cards = document.querySelectorAll('.team-member-card');

                cards.forEach(card => {
                    const text = card.textContent.toLowerCase();
                    card.style.display = text.includes(searchTerm) ? 'block' : 'none';
                });
            });
        });
    </script>
<?php
}
add_action('wp_footer', 'add_team_member_search_script');

// Get active companies for the filter dropdown
function get_active_companies()
{
    global $wpdb;

    $query = "
        SELECT DISTINCT u.ID, u.display_name 
        FROM {$wpdb->users} u 
        INNER JOIN {$wpdb->posts} p ON u.ID = p.post_author 
        WHERE p.post_type = 'team_member' 
        AND p.post_status = 'publish'
        ORDER BY u.display_name ASC
    ";

    return $wpdb->get_results($query);
}

function update_company_filter_dropdown()
{
    $active_companies = get_active_companies();
?>
    <div class="filter-group">
        <label>Company/Agency</label>
        <select name="user_filter">
            <option value="">All Companies</option>
            <?php foreach ($active_companies as $company): ?>
                <option value="<?php echo esc_attr($company->ID); ?>">
                    <?php echo esc_html($company->display_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
<?php
}

function view_all_candidates_shortcode()
{
    ob_start();
?>
    <style>
        .candidates-container {
            display: flex;
            gap: 30px;
            margin: 16px;
            padding: 20px;
            overflow: hidden;
            align-items: flex-start;
            /* Prevent overall container scroll */
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .all {
            width: 100%;
        }

        .filter-sidebar {
            flex: 0 0 280px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: 100%;
            overflow-y: auto;
            /* Scrollable sidebar */
            position: sticky;
            top: 20px;
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }

        .filter-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .filter-sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .filter-sidebar::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .candidates-results {
            flex: 1;
            height: 100%;
            overflow: hidden;
            position: relative;
        }

        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            /* Force exactly 3 columns */
            gap: 20px;
            height: 100%;
            overflow-y: auto;
            /* Scrollable grid */
            padding-right: 15px;
            /* Space for scrollbar */
        }

        /* Scrollbar styling for better appearance */
        .candidates-grid::-webkit-scrollbar {
            width: 8px;
        }

        .candidates-grid::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .candidates-grid::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .candidates-grid::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        .filter-group {
            margin-bottom: 20px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f8f9fa;
        }

        .filter-submit {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .filter-submit:hover {
            background: #2980b9;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 768px) {
            .candidates-container {
                height: auto;
                flex-direction: column;
            }


            .candidates-grid {
                height: auto;
                overflow: hidden;
            }

            .filter-sidebar {
                position: relative;
                height: auto;
                max-height: 300px;
            }
        }




        @media (max-width: 1200px) {
            .candidates-grid {
                grid-template-columns: repeat(2, 1fr);
                /* 2 columns on smaller screens */
            }
        }

        @media (max-width: 480px) {
            .candidates-grid {
                grid-template-columns: 1fr;
                align-items: center;
                justify-items: center;
            }
        }

        .no-results {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 20px 0;
        }

        /* Add a floating "Scroll to Top" button */
        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: #3498db;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: none;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .scroll-to-top:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .scroll-to-top.visible {
            display: flex;
        }

        .whatsapp-info {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #25D366;
            font-size: 0.9em;
            margin: 8px 0;
        }

        .whatsapp-info::before {
            content: "📱";
            font-size: 1.1em;
        }

        .whatsapp-number {
            color: #075E54;
            font-weight: 500;
        }

        .whatsapp-filter {
            font-family: monospace;
            letter-spacing: 0.5px;
        }
    </style>

    <div class="candidates-container">
        <!-- Filter Sidebar -->
        <aside class="filter-sidebar">
            <form id="candidates-filter" class="filter-form">
                <?php wp_nonce_field('filter_candidates_nonce', 'candidates_nonce'); ?>

                <!-- Company Filter -->
                <?php update_company_filter_dropdown();  ?>

                <div class="filter-group">
                    <label>WhatsApp Contact</label>
                    <select name="whatsapp_filter" class="whatsapp-filter">
                        <option value="">All Candidates</option>
                        <?php
                        // Get all unique WhatsApp numbers from users who have posted team members
                        global $wpdb;
                        $whatsapp_numbers = $wpdb->get_col("
                        SELECT DISTINCT um.meta_value 
                        FROM {$wpdb->usermeta} um
                        JOIN {$wpdb->posts} p ON um.user_id = p.post_author
                        WHERE um.meta_key = 'whatsapp_number'
                        AND p.post_type = 'team_member'
                        AND p.post_status = 'publish'
                        AND um.meta_value != ''
                    ");

                        foreach ($whatsapp_numbers as $number) {
                            printf(
                                '<option value="%s">%s</option>',
                                esc_attr($number),
                                esc_html($number)
                            );
                        }
                        ?>
                    </select>
                </div>


                <div class="filter-group">
                    <label>Nationality</label>
                    <select name="nationality_filter">
                        <option value="">All Nationalities</option>
                        <option value="Ethiopia">Ethiopia 🇪🇹</option>
                        <option value="Uganda">Uganda 🇺🇬</option>
                        <option value="Philippines">Philippines 🇵🇭</option>
                        <option value="Kenya">Kenya 🇰🇪</option>
                        <option value="Indonesia">Indonesia 🇮🇩</option>
                        <option value="Sri Lanka">Sri Lanka 🇱🇰</option>
                        <option value="Vietnam">Vietnam 🇻🇳</option>
                        <option value="Nepal">Nepal 🇳🇵</option>
                        <option value="Ghana">Ghana 🇬🇭</option>
                        <option value="Myanmar">Myanmar 🇲🇲</option>
                        <option value="Bangladesh">Bangladesh 🇧🇩</option>
                        <option value="Nigeria">Nigeria 🇳🇬</option>
                        <option value="Iraq">Iraq 🇮🇶</option>
                        <option value="Lebanon">Lebanon 🇱🇧</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Experience In</label>
                    <select name="country_experience_filter">
                        <option value="">Any Country</option>
                        <optgroup label="Gulf Countries">
                            <option value="Saudi Arabia">Saudi Arabia 🇸🇦</option>
                            <option value="UAE">UAE 🇦🇪</option>
                            <option value="Qatar">Qatar 🇶🇦</option>
                            <option value="Kuwait">Kuwait 🇰🇼</option>
                            <option value="Oman">Oman 🇴🇲</option>
                            <option value="Bahrain">Bahrain 🇧🇭</option>
                        </optgroup>
                        <optgroup label="Asian Countries">
                            <option value="Singapore">Singapore 🇸🇬</option>
                            <option value="Hong Kong">Hong Kong 🇭🇰</option>
                            <option value="Malaysia">Malaysia 🇲🇾</option>
                        </optgroup>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Experience Category</label>
                    <select name="job_category_filter">
                        <option value="">Any Category</option>
                        <option value="Housemaid">Housemaid</option>
                        <option value="Cook">Cook</option>
                        <option value="Nanny">Nanny/Babysitter</option>
                        <option value="Driver">Driver</option>
                        <option value="Security">Security Guard</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Years of Experience</label>
                    <select name="experience_filter">
                        <option value="">Any Experience</option>
                        <option value="0-2">0-2 years</option>
                        <option value="3-5">3-5 years</option>
                        <option value="5-10">5-10 years</option>
                        <option value="10+">10+ years</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Age Range</label>
                    <select name="age_filter">
                        <option value="">Any Age</option>
                        <option value="18-25">18-25 years</option>
                        <option value="26-35">26-35 years</option>
                        <option value="36-45">36-45 years</option>
                        <option value="46+">46+ years</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Sort By</label>
                    <select name="sort_by">
                        <option value="recent">Most Recent</option>
                        <option value="views">Most Viewed</option>
                        <option value="experience">Most Experienced</option>
                    </select>
                </div>

                <button type="button" id="reset-filters" class="filter-submit">Reset Filters</button>


                <button type="submit" class="filter-submit">Apply Filters</button>
            </form>
        </aside>
        <!-- Results Grid -->
        <div class="candidates-results">
            <p id="result-count"></p>
            <div id="filtered-candidates" class="candidates-grid">
                <div class="loading-overlay" style="display: none;">
                    <div class="spinner"></div>
                </div>
                <div class="no-results" style="display: none;">
                    <p>No candidates found matching your criteria.</p>
                </div>
                <!-- Results will be loaded here -->
            </div>
        </div>
    </div>
    <div class="scroll-to-top" title="Scroll to top">↑</div>

    <!-- <script>
        jQuery(document).ready(function($) {
            function getFormData($form) {
                let data = {};
                $form.find('select, input').each(function() {
                    if (this.value) {
                        data[this.name] = this.value;
                    }
                });
                return data;
            }

            function loadCandidates(formData) {
                const $results = $('#filtered-candidates');
                const $loadingOverlay = $('.loading-overlay');
                const $noResults = $('.no-results');

                $loadingOverlay.show();
                $noResults.hide();

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'filter_candidates',
                        candidates_nonce: $('#candidates_nonce').val(),
                        ...formData
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            $results.html(response.data);
                            const resultCount = $results.find('.team-member-card').length;
                            $('#result-count').text(`${resultCount} candidates found`);
                            if (!response.data.trim() || resultCount === 0) {
                                $noResults.show();
                            } else {
                                $noResults.hide();
                            }
                        } else {
                            $results.html('<p>Error loading candidates</p>');
                            $noResults.show();
                        }
                    },
                    error: function() {
                        $results.html('<p>Error loading candidates</p>');
                        $noResults.show();
                    },
                    complete: function() {
                        $loadingOverlay.hide();
                    }
                });
            }

            // Initial load
            loadCandidates({});

            // Handle form submission
            console.log('AJAX script loaded');
            $('#candidates-filter').on('submit', function(e) {
                console.log('Form submitted');
                e.preventDefault();
                const formData = getFormData($(this));
                loadCandidates(formData);
            });

            // Handle individual filter changes
            $('#candidates-filter select').on('change', function() {
                const formData = getFormData($('#candidates-filter'));
                loadCandidates(formData);
            });

            $('#reset-filters').on('click', function() {
                $('#candidates-filter')[0].reset();
                loadCandidates({});
            });
        });
    </script> -->
    <?php
    return ob_get_clean();
}
add_shortcode('view_all_candidates', 'view_all_candidates_shortcode');

// Update the company filter dropdown to only show users who have posted team members


// Update the filter_candidates_ajax() function

function filter_candidates_ajax()
{
    check_ajax_referer('filter_candidates_nonce', 'candidates_nonce');

    $args = array(
        'post_type' => 'team_member',
        'posts_per_page' => -1,
        'meta_query' => array('relation' => 'AND')
    );

    // Add pagination
    $args['paged'] = isset($_POST['page']) ? intval($_POST['page']) : 1;

    // Company/Agency filter
    if (!empty($_POST['user_filter'])) {
        $args['author'] = intval($_POST['user_filter']);
    }

    // WhatsApp filter
    if (!empty($_POST['whatsapp_filter'])) {
        $whatsapp_number = sanitize_text_field($_POST['whatsapp_filter']);
        $user_ids = get_users(array(
            'meta_key' => 'whatsapp_number',
            'meta_value' => $whatsapp_number,
            'fields' => 'ID'
        ));
        if (!empty($user_ids)) {
            $args['author__in'] = $user_ids;
        } else {
            $args['post__in'] = array(0); // Force no results if WhatsApp number not found
        }
    }

    // Nationality filter
    if (!empty($_POST['nationality_filter'])) {
        $args['meta_query'][] = array(
            'key' => 'nationality',
            'value' => sanitize_text_field($_POST['nationality_filter']),
            'compare' => '='
        );
    }

    // Experience in country filter
    if (!empty($_POST['country_experience_filter'])) {
        $country = sanitize_text_field($_POST['country_experience_filter']);
        $args['meta_query'][] = array(
            'key' => 'country_experience',
            'value' => $country,
            'compare' => 'LIKE'
        );
    }

    // Job category filter
    if (!empty($_POST['job_category_filter'])) {
        $args['meta_query'][] = array(
            'key' => 'job',
            'value' => sanitize_text_field($_POST['job_category_filter']),
            'compare' => 'LIKE'
        );
    }

    // Age filter
    if (!empty($_POST['age_filter'])) {
        list($min, $max) = explode('-', $_POST['age_filter']);
        $args['meta_query'][] = array(
            'key' => 'age',
            'value' => array($min, $max),
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN'
        );
    }

    // Sort by
    if (!empty($_POST['sort_by'])) {
        switch ($_POST['sort_by']) {
            case 'recent':
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
            case 'views':
                $args['meta_key'] = 'viewer_count';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'experience':
                $args['meta_key'] = 'years_of_experience';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
        }
    }

    // Add status query args
    $args = add_status_query_args($args);

    // Debugging: Log the query arguments
    // error_log('Query Args: ' . print_r($args, true));

    $query = new WP_Query($args);

    // Debugging: Log the number of posts found
    //error_log('Found Posts: ' . $query->found_posts);

    ob_start();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $author_id = get_post_field('post_author', $post_id);

            // Get required meta fields
            $name = get_the_title();
            $nationality = get_post_meta($post_id, 'nationality', true);
            $age = get_post_meta($post_id, 'age', true);
            $package = get_post_meta($post_id, 'package', true);
            $viewer_count = get_post_meta($post_id, 'viewer_count', true) ?: 0;
            $whatsapp_number = get_user_meta($author_id, 'whatsapp_number', true);

            // Get image
            $image_id = get_post_meta($post_id, 'team_member_image', true);
            $image_url = $image_id ? wp_get_attachment_url($image_id) : (get_the_post_thumbnail_url($post_id, 'medium') ?: 'https://via.placeholder.com/150');

            // Output card HTML
    ?>
            <div class="team-member-card">
                <div class="favorite-heart"
                    data-member-id="<?php echo esc_attr($post_id); ?>"
                    data-member-name="<?php echo esc_attr($name); ?>"
                    data-member-age="<?php echo esc_attr($age); ?>"
                    data-member-nationality="<?php echo esc_attr($nationality); ?>"
                    data-member-package="<?php echo esc_attr($package); ?>"
                    data-member-image="<?php echo esc_url($image_url); ?>"
                    data-member-link="<?php echo esc_url(site_url('/candidate/?id=' . $post_id)); ?>"
                    title="Add to Favorites">
                    <i class="far fa-heart"></i>
                </div>
                <div class="team-member-image">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($name); ?>">
                </div>
                <div class="team-member-info">
                    <strong><?php echo esc_html($name); ?></strong>
                    <small>Views: <?php echo esc_html($viewer_count); ?></small>
                    <p><?php echo esc_html($age); ?> • <?php echo esc_html($nationality); ?></p>
                    <p>Package: <?php echo esc_html($package); ?></p>
                    <?php if ($whatsapp_number): ?>
                        <p class="whatsapp-info">
                            WhatsApp: <span class="whatsapp-number"><?php echo esc_html($whatsapp_number); ?></span>
                        </p>
                    <?php endif; ?>
                    <a href="<?php echo esc_url(site_url('/candidate/?id=' . $post_id)); ?>" class="view-profile-btn">View Profile</a>

                </div>
            </div>
<?php
        }
        // Add pagination links
        $total_pages = $query->max_num_pages;
        if ($total_pages > 1) {
            echo '<div id="pagination">';
            echo paginate_links(array(
                'base' => '%_%',
                'format' => '?page=%#%',
                'current' => max(1, isset($_POST['page']) ? intval($_POST['page']) : 1),
                'total' => $total_pages,
            ));
            echo '</div>';
        }
    } else {
        echo '<div class="no-results"><p>No candidates found matching your criteria.</p></div>';
    }

    wp_reset_postdata();
    $output = ob_get_clean();
    wp_send_json_success($output);
}


add_action('wp_ajax_filter_candidates', 'filter_candidates_ajax');
add_action('wp_ajax_nopriv_filter_candidates', 'filter_candidates_ajax');

function extract_mrz_with_ocr_space($image_data_base64)
{
    $api_key = 'K87543195588957';
    $url = 'https://api.ocr.space/parse/image';
    $post_fields = [
        'base64Image' => $image_data_base64,
        'language' => 'eng',
        'isTable' => false,
        'OCREngine' => 2,
        'isOverlayRequired' => false,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $api_key
    ]);
    $result = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($result, true);
    if (!empty($result['ParsedResults'][0]['ParsedText'])) {
        return $result['ParsedResults'][0]['ParsedText'];
    }
    return false;
}

function parse_passport_mrz($mrz)
{
    $mrz = preg_replace('/\s+/', '', $mrz);
    $lines = explode("\n", $mrz);
    if (count($lines) < 2) {
        $lines = str_split($mrz, 44);
    }
    if (count($lines) < 2) return false;

    $line1 = $lines[0];
    $line2 = $lines[1];

    $passport_number = trim(str_replace('<', '', substr($line2, 0, 9)));
    $nationality = trim(str_replace('<', '', substr($line2, 10, 3)));
    $dob = substr($line2, 13, 6);
    $gender = substr($line2, 20, 1);
    $expiry = substr($line2, 21, 6);

    // Convert dates
    $dob_formatted = mrz_date_to_dmy($dob);
    $expiry_formatted = mrz_date_to_dmy($expiry);

    $names = explode('<<', substr($line1, 5));
    $surname = str_replace('<', ' ', $names[0]);
    $given_names = isset($names[1]) ? str_replace('<', ' ', $names[1]) : '';
    $full_name = trim($surname . ' ' . $given_names);

    return array(
        'passport_number' => $passport_number,
        'nationality' => $nationality,
        'dob' => $dob_formatted,
        'gender' => $gender,
        'expiry' => $expiry_formatted,
        'full_name' => $full_name,
    );
}

function mrz_date_to_dmy($ymd)
{
    if (strlen($ymd) !== 6) return '';
    $year = substr($ymd, 0, 2);
    $month = substr($ymd, 2, 2);
    $day = substr($ymd, 4, 2);
    $full_year = ((int)$year > 30 ? '19' : '20') . $year;
    return "$day/$month/$full_year";
}

add_action('wp_ajax_process_mrz', 'process_mrz_ajax');
add_action('wp_ajax_nopriv_process_mrz', 'process_mrz_ajax');

function process_mrz_ajax()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'passport_scan_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    if (empty($_POST['image_data'])) {
        wp_send_json_error('No image data');
    }

    // Remove the data URL prefix if present
    $image_data = $_POST['image_data'];
    if (strpos($image_data, 'base64,') !== false) {
        $image_data = explode('base64,', $image_data)[1];
    }
    $image_data_base64 = 'data:image/png;base64,' . $image_data;

    // OCR.space API call
    $mrz_text = extract_mrz_with_ocr_space($image_data_base64);

    if (!$mrz_text) {
        wp_send_json_error('Could not extract MRZ text');
    }

    // Parse MRZ
    $parsed = parse_passport_mrz($mrz_text);

    if ($parsed) {
        wp_send_json_success($parsed);
    } else {
        wp_send_json_error('Could not parse MRZ');
    }
}

// Image Enhance
function enhance_image_with_rapidapi($attachment_id)
{
    $api_key = RAPIDAPI_PHOTO_ENHANCE_KEY;
    $image_path = get_attached_file($attachment_id);
    $image_data = file_get_contents($image_path);
    $image_base64 = base64_encode($image_data);

    $body = json_encode([
        'image_base64' => $image_base64,
        'type' => 'clean',
        'scale_factor' => 2
    ]);

    $response = wp_remote_post('https://photo-enhance-api.p.rapidapi.com/api/scale', [
        'headers' => [
            'Content-Type' => 'application/json',
            'x-rapidapi-host' => 'photo-enhance-api.p.rapidapi.com',
            'x-rapidapi-key'  => $api_key,
        ],
        'body' => $body,
        'timeout' => 60,
    ]);

    if (is_wp_error($response)) return false;

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!empty($data['image_base64'])) {
        // Overwrite the original image with the enhanced one
        file_put_contents($image_path, base64_decode($data['image_base64']));
        // Regenerate thumbnails if needed
        if (function_exists('wp_update_attachment_metadata')) {
            $attach_data = wp_generate_attachment_metadata($attachment_id, $image_path);
            wp_update_attachment_metadata($attachment_id, $attach_data);
        }
        return true;
    }
    return false;
}



add_action('admin_init', function () {
    $meta_map = [
        'team_member_name'              => 'name',
        'team_member_nationality'       => 'nationality',
        'team_member_age'               => 'age',
        'team_member_bio'               => 'bio',
        'team_member_package'           => 'package',
        'team_member_job'               => 'job',
        'team_member_mskills'           => 'mskills',
        'team_member_cskills'           => 'cskills',
        'team_member_oskills'           => 'oskills',
        'team_member_personality'       => 'personality',
        'team_member_status'            => 'status',
        'team_member_email'             => 'email',
        'team_member_phone'             => 'phone',
        'team_member_address'           => 'address',
        'team_member_city'              => 'city',
        'team_member_state'             => 'state',
        'team_member_country'           => 'country',
        'team_member_salary'            => 'salary',
        'team_member_whatsapp_no'       => 'whatsapp_no',
        'team_member_po_box'            => 'po_box',
        'team_member_company_name'      => 'company_name',
        'team_member_company_tagline'   => 'company_tagline',
        'team_member_company_phone'     => 'company_phone',
        'team_member_video'             => 'video',
        'team_member_weight'            => 'weight',
        'team_member_height'            => 'height',
        'team_member_qualification'     => 'qualification',
        'team_member_experience'        => 'experience',
        'team_member_languages'         => 'languages',
        'team_member_education'         => 'education',
        'team_member_religion'          => 'religion',
        'team_member_place_of_birth'    => 'place_of_birth',
        'team_member_civil_status'      => 'civil_status',
        'team_member_passport_nationality' => 'passport_nationality',
        'team_member_passport_number'   => 'passport_number',
        'team_member_passport_issue_date' => 'passport_issue_date',
        'team_member_passport_expiry_date' => 'passport_expiry_date',
        'team_member_passport_place_of_issue' => 'passport_place_of_issue',
    ];

    $args = [
        'post_type' => 'team_member',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ];
    $posts = get_posts($args);

    foreach ($posts as $post_id) {
        foreach ($meta_map as $from => $to) {
            $val = get_post_meta($post_id, $from, true);
            if (!empty($val)) {
                update_post_meta($post_id, $to, $val);
            }
        }
        // Ensure status is set
        $status = get_post_meta($post_id, 'team_member_status', true);
        if (empty($status)) {
            update_post_meta($post_id, 'team_member_status', 'available');
            update_post_meta($post_id, 'status', 'available');
        }
    }
});