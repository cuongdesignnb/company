<?php
if (!defined('ABSPATH')) {
    exit;
}

function cb_default_homepage_sections()
{
    $asset = content_url('/themes/cb-company-theme/assets/images/aurelia-reference.png');
    return [
        [
            'enable' => '1',
            'type' => 'hero_slider',
            'eyebrow' => 'KITCHEN APPLIANCE MANUFACTURER',
            'title' => 'Precision Manufacturing. Performance in Every Detail.',
            'subtitle' => 'Aurelia Manufacturing',
            'description' => 'Reliable OEM/ODM kitchen appliance manufacturing for brands that need quality, delivery discipline and global support.',
            'button_text' => 'Explore Products',
            'button_url' => '/en/products/',
            'image' => $asset,
            'items' => 'Reliable Quality|Strict QC from material to shipment|',
        ],
        [
            'enable' => '1',
            'type' => 'company_intro',
            'eyebrow' => 'ABOUT AURELIA',
            'title' => 'Built on Quality. Driven by Innovation.',
            'subtitle' => '',
            'description' => 'Founded in 2010, Aurelia Manufacturing is a kitchen appliance manufacturer integrating R&D, production and global sales.',
            'button_text' => 'More About Us',
            'button_url' => '/en/about-us/',
            'image' => $asset,
            'items' => '14+|Years Experience|50,000 m²|Factory Area|70+|Countries Served|600+|Skilled Employees',
        ],
        ['enable' => '1', 'type' => 'product_categories', 'eyebrow' => 'PRODUCT CATEGORIES', 'title' => 'Explore Our Main Categories', 'subtitle' => '', 'description' => '', 'button_text' => 'View All Products', 'button_url' => '/en/products/', 'image' => '', 'items' => ''],
        ['enable' => '1', 'type' => 'featured_products', 'eyebrow' => 'FEATURED PRODUCTS', 'title' => 'High Quality. Made for Performance.', 'subtitle' => '', 'description' => '', 'button_text' => 'All Products', 'button_url' => '/en/products/', 'image' => '', 'items' => ''],
        [
            'enable' => '1',
            'type' => 'why_choose_us',
            'eyebrow' => 'WHY CHOOSE US',
            'title' => 'Your Reliable Manufacturing Partner',
            'subtitle' => '',
            'description' => 'From quality control to on-time delivery, we are committed to helping your brand succeed.',
            'button_text' => 'More About Us',
            'button_url' => '/en/about-us/',
            'image' => '',
            'items' => "Quality Assurance|Strict QC system from raw material to finished product|\nAdvanced Equipment|Modern production lines and testing facilities|\nR&D Innovation|Strong team for innovative product solutions|\nCustomization OEM/ODM|Flexible solutions tailored to your brand needs|\nOn-time Delivery|Efficient planning ensures punctual delivery|\nGlobal Support|Responsive service worldwide|",
        ],
        [
            'enable' => '1',
            'type' => 'factory_capability',
            'eyebrow' => 'OUR FACTORY CAPABILITIES',
            'title' => 'Integrated Manufacturing. Reliable Capacity.',
            'subtitle' => '',
            'description' => 'Automated processing, assembly, testing, warehousing and logistics under one production system.',
            'button_text' => 'View More Capabilities',
            'button_url' => '/en/factory/',
            'image' => $asset,
            'items' => "Injection Molding|High-precision molding with automated lines|{$asset}\nMetal Processing|Stamping and fabrication|{$asset}\nAssembly Line|Lean production with skilled workers|{$asset}\nQuality Control|Multiple inspections before shipment|{$asset}\nWarehouse & Logistics|Large warehouse and efficient shipping|{$asset}",
        ],
        [
            'enable' => '1',
            'type' => 'oem_odm_process',
            'eyebrow' => 'OEM/ODM PROCESS',
            'title' => 'From Concept to Market in 7 Simple Steps',
            'subtitle' => '',
            'description' => '',
            'button_text' => '',
            'button_url' => '',
            'image' => '',
            'items' => "Inquiry|Share your needs|\nProposal|Evaluation and quotation|\nDesign|Product design and engineering|\nSample|Prototype confirmation|\nProduction|Mass production with QC|\nQuality Check|100% inspection|\nDelivery|Ship to your destination|",
        ],
        [
            'enable' => '1',
            'type' => 'case_studies',
            'eyebrow' => 'GLOBAL MARKETS',
            'title' => 'Trusted by Brands Worldwide',
            'subtitle' => '',
            'description' => '',
            'button_text' => 'View All Cases',
            'button_url' => '/en/cases/',
            'image' => '',
            'items' => "Europe|Long-term cooperation with leading retailers|{$asset}\nNorth America|Supplying appliances to top brands|{$asset}\nAsia|Strong presence across Asia and Middle East|{$asset}\nSouth America|Growing partnerships across markets|{$asset}",
        ],
        [
            'enable' => '1',
            'type' => 'certificates',
            'eyebrow' => 'CERTIFICATIONS',
            'title' => 'Quality You Can Trust',
            'subtitle' => '',
            'description' => 'We meet international standards and ensure product safety.',
            'button_text' => '',
            'button_url' => '',
            'image' => '',
            'items' => "ISO 9001|Quality Management|\nBSCI|Business Social Compliance|\nCE|European Conformity|\nCB|IEC Certification|\nRoHS|Environmental Protection|\nLFGB|Food Safety|",
        ],
        ['enable' => '1', 'type' => 'news_section', 'eyebrow' => 'LATEST NEWS', 'title' => 'Insights, Updates & Industry Trends', 'subtitle' => '', 'description' => '', 'button_text' => 'View All News', 'button_url' => '/en/news/', 'image' => '', 'items' => ''],
        [
            'enable' => '1',
            'type' => 'inquiry_cta',
            'eyebrow' => '',
            'title' => 'Ready to Build Your Next Best-selling Product?',
            'subtitle' => '',
            'description' => 'Partner with Aurelia Manufacturing and bring your ideas to life with quality, reliability and on-time delivery.',
            'button_text' => 'Get a Quote Now',
            'button_url' => '#inquiry',
            'image' => '',
            'items' => '',
        ],
    ];
}

function cb_seed_default_content()
{
    if (!get_option('cb_theme_options')) {
        update_option('cb_theme_options', cb_default_theme_options());
    }
    if (!get_option('cb_string_translations')) {
        update_option('cb_string_translations', cb_default_string_translations());
    }
    if (!get_option('cb_homepage_sections')) {
        update_option('cb_homepage_sections', cb_default_homepage_sections());
    }
    if (get_option('cb_seeded_v1')) {
        return;
    }

    $asset = content_url('/themes/cb-company-theme/assets/images/aurelia-reference.png');
    $categories = [
        'Small Kitchen Appliances' => 'Air fryers, blenders, kettles and more',
        'Coffee Machines' => 'Drip coffee makers, espresso machines and accessories',
        'Food Preparation' => 'Mixers, processors, choppers and more',
        'Cooking Appliances' => 'Ovens, grills, cookers and multi-cookers',
    ];

    foreach ($categories as $name => $desc) {
        if (!term_exists($name, 'product_category')) {
            $term = wp_insert_term($name, 'product_category', ['description' => $desc]);
            if (!is_wp_error($term)) {
                update_term_meta($term['term_id'], '_cb_language', 'en');
                update_term_meta($term['term_id'], '_cb_featured', '1');
                update_term_meta($term['term_id'], '_cb_banner_image', $asset);
            }
        }
    }

    $products = [
        ['Air Fryer 5.5L', 'AF-8001', 'Small Kitchen Appliances'],
        ['Espresso Machine', 'CM-3002', 'Coffee Machines'],
        ['Stand Mixer 6.5L', 'SM-6503', 'Food Preparation'],
        ['Blender 1.8L', 'BL-1804', 'Food Preparation'],
        ['Multi Cooker 5L', 'MC-5005', 'Cooking Appliances'],
        ['Electric Kettle 1.7L', 'EK-1706', 'Small Kitchen Appliances'],
    ];
    foreach ($products as $product) {
        if (get_page_by_title($product[0], OBJECT, 'product')) {
            continue;
        }
        $id = wp_insert_post([
            'post_type' => 'product',
            'post_status' => 'publish',
            'post_title' => $product[0],
            'post_excerpt' => 'Reliable appliance designed for consistent performance and OEM customization.',
            'post_content' => 'This product is engineered for brands that need stable supply, strict quality control and flexible OEM/ODM options.',
        ]);
        if ($id && !is_wp_error($id)) {
            update_post_meta($id, '_cb_language', 'en');
            update_post_meta($id, '_cb_featured', '1');
            update_post_meta($id, '_cb_model', $product[1]);
            update_post_meta($id, '_cb_brand', 'Aurelia');
            update_post_meta($id, '_cb_short_description', 'OEM/ODM ready kitchen appliance for international brands.');
            update_post_meta($id, '_cb_gallery', $asset);
            update_post_meta($id, '_cb_specs', "Model|{$product[1]}\nVoltage|220-240V\nCertification|CE / CB / RoHS\nMOQ|500 units\nLead Time|30-45 days");
            update_post_meta($id, '_cb_inquiry_enabled', '1');
            wp_set_object_terms($id, $product[2], 'product_category');
        }
    }

    for ($i = 1; $i <= 3; $i++) {
        $title = ['Air Fryer Market Trends in 2024', 'Aurelia Manufacturing Expands Production Line', 'How We Ensure Consistent Product Quality'][$i - 1];
        if (!get_page_by_title($title, OBJECT, 'post')) {
            $id = wp_insert_post([
                'post_type' => 'post',
                'post_status' => 'publish',
                'post_title' => $title,
                'post_excerpt' => 'Updates from our manufacturing team and appliance industry insights.',
                'post_content' => 'A short industry update prepared for brand owners sourcing reliable kitchen appliances.',
            ]);
            update_post_meta($id, '_cb_language', 'en');
        }
    }

    update_option('cb_seeded_v1', '1');
}
