<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://divineinfosys.com
 * @since             1.0.0
 * @package           Product_Taxonomy_Filter
 *
 * @wordpress-plugin
 * Plugin Name:       Product Taxonomy Filter
 * Plugin URI:        https://divineinfosys.com
 * Description:       Woocommerce Product Taxonomy Filter 
 * Version:           1.0.0
 * Author:            Divine Infosys
 * Author URI:        https://divineinfosys.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       product-filter 
 * Domain Path:       /languages
 */

define("selected_taxonomy", get_option('product_taxonomy_option', 'product_cat') );
define("shortcode_desc", get_option('shortcode_desc', 'Product Taxonomy Filter') );
function activate_product_taxonomy_filter() {
    if ( ! function_exists( 'is_woocommerce_activated' ) ) {
         if ( class_exists( 'woocommerce' ) ) { 
            return true; 
        }else { 
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( __( 'Please Install/Activate WooCommerce to use Product Taxonomy Filter Plugin.', 'my-plugin' ), 'Plugin dependency check', array( 'back_link' => true ) );
                return false; 
        }
    }
}

function product_taxonomy_filter_enqueue_scripts() {
  
    wp_register_script( 'product-filter-js', plugin_dir_url('') .basename( dirname( __FILE__ ) ). '/assets/js/product-filter.js');
    wp_enqueue_script( 'pf-script-jquery', plugin_dir_url( '' ) . basename( dirname( __FILE__ ) ) . '/assets/js/jquery.min.js', array('jquery'), true  );
    wp_register_style( 'product-filter-css',  plugin_dir_url('') .basename( dirname( __FILE__ ) ). '/assets/css/product-filter.css');
 
    wp_enqueue_script( 'product-filter-js' );
    wp_enqueue_style( 'product-filter-css' );
 
}
add_action( 'wp_enqueue_scripts', 'product_taxonomy_filter_enqueue_scripts' );

function admin_scripts_enqueue() {
    wp_enqueue_script('admin-product-filter-js', plugin_dir_url('') .basename( dirname( __FILE__ ) ). '/assets/js/admin-product-filter.js');
    wp_enqueue_style('admin-product-filter-css',  plugin_dir_url('') .basename( dirname( __FILE__ ) ). '/assets/css/admin-product-filter.css');
}

add_action('admin_enqueue_scripts', 'admin_scripts_enqueue');
 

/**
 * The code that runs during plugin deactivation.
 * 
 */
function deactivate_product_taxonomy_filter() {
        deactivate_plugins( basename( __FILE__ ) );
}

register_activation_hook( __FILE__, 'product_taxonomy_filter_admin_page' );
// register_activation_hook( __FILE__, 'custom_taxonomy_brand' );
register_activation_hook( __FILE__, 'activate_product_taxonomy_filter' );

register_deactivation_hook( __FILE__, 'deactivate_product_taxonomy_filter' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

/* Add Brand Taxonomy */
// function custom_taxonomy_brand()  {
    // $labels = array(
    //     'name'                       => 'Brands',
    //     'singular_name'              => 'Brand',
    //     'menu_name'                  => 'Product Brands',
    //     'all_brands'                  => 'All Brands',
    //     'parent_brand'                => 'Parent Brand',
    //     'parent_brand_colon'          => 'Parent Brand:',
    //     'new_brand_name'              => 'New Brand Name',
    //     'add_new_brand'               => 'Add New Brand',
    //     'edit_brand'                  => 'Edit Brand',
    //     'update_brand'                => 'Update Brand',
    //     'separate_brands_with_commas' => 'Separate Brand with commas',
    //     'search_brands'               => 'Search Brands',
    //     'add_or_remove_brands'        => 'Add or remove Brands',
    //     'choose_from_most_used'      => 'Choose from the most used Brands',
    // );
    // $args = array(
    //     'labels'                     => $labels,
    //     'hierarchical'               => true,
    //     'public'                     => true,
    //     'show_ui'                    => true,
    //     'show_admin_column'          => true,
    //     'show_in_nav_menus'          => true,
    //     'show_tagcloud'              => true,
    // );

    // $taxonomy_exist = taxonomy_exists( 'pb_brands' );

    // if(!$taxonomy_exist){
    //     register_taxonomy( 'pb_brands', 'product', $args );
    //     register_taxonomy_for_object_type( 'pb_brands', 'product' );  
    // }else{
    //     return 0;
    // }

// }
// add_action( 'init', 'custom_taxonomy_brand' );

function product_taxonomy_filter_admin_page(){
    add_menu_page( 
        __( 'Product Taxonomy Filter', 'textdomain' ),
        'Product Taxonomy Filter',
        'manage_options',
        'product_taxonomy_filter',
        'product_taxonomy_filter_menu_page',
        'dashicons-category',
        56
    ); 
    add_action( 'admin_init', 'product_filter_settings' );

}
add_action( 'admin_menu', 'product_taxonomy_filter_admin_page' );

function filter_shortcode(){
    global $product;
    ob_start();
    ?> 
      <div class="product_taxonomy_filter_form"> 
        <h3 class="shortcode_desc"><?php echo shortcode_desc ?></h3>
       <select name="filter_dropdown" id="filter_dropdown" onchange=product_taxonomy_ajax_action(this,"<?php echo admin_url( 'admin-ajax.php' ); ?>")>
        <option value="0" selected="selected">Show All</option>
        <?php
           $tax_terms = get_terms(selected_taxonomy, array('hide_empty' => '0'));      
           foreach ( $tax_terms as $tax_term ):  
              echo '<option value="'.$tax_term->name.'">'.$tax_term->name.'</option>';   
           endforeach;
        ?>
        </select>
    </div>
    
    <div id="response">
        <img src="<?php echo plugin_dir_url( __FILE__ ) . '/assets/images/preloader.gif'; ?>" id="loading-image" style="display: none;">
        <div class="filter_response">

             <div class="filtered_products">
              <?php
              $args = array( 'post_type' => 'product', 'posts_per_page' => -1 );
                $product_taxanomy_loop = new WP_Query( $args );
                if( $product_taxanomy_loop->have_posts() ){
                ?>
                <div class="filter_single_product woocommerce">  
                    <ul class="products columns-4">
                    <?php
                        while ( $product_taxanomy_loop->have_posts() ) : $product_taxanomy_loop->the_post(); global $product; ?>
                               
                        <li class="product type-product">
                            <a class="woocommerce-LoopProduct-link woocommerce-loop-product__link" href="<?php echo get_permalink( $product_taxanomy_loop->post->ID ) ?>" title="<?php echo esc_attr($product_taxanomy_loop->post->post_title ? $product_taxanomy_loop->post->post_title : $product_taxanomy_loop->post->ID); ?>">
                                <div class="brand_product_image"> 
                                    <?php if (has_post_thumbnail( $product_taxanomy_loop->post->ID )) echo get_the_post_thumbnail($product_taxanomy_loop->post->ID, 'shop_catalog'); else echo '<img class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" src="'.woocommerce_placeholder_img_src().'" alt="Placeholder" sizes="(max-width: 450px) 100vw, 450px" />'; ?>
                                </div>
                                <h2 class="woocommerce-loop-product__title"><?php the_title(); ?></h2>
                                <span class="price"><?php echo $product->get_price_html(); ?></span>
                            </a>
                                 <?php woocommerce_template_loop_add_to_cart( $product_taxanomy_loop->post, $product ); ?>
                        </li>

                    <?php endwhile; ?>

                    <?php wp_reset_query();

                    ?>
                      </ul>
                            </div>
                    <?php
                } else {
                    ?><div>No Products found</div><?php
                }
                ?>
            </div>
        </div>
        
    </div>

<?php 

return ob_get_clean();
}
add_shortcode('product_taxonomy_filter', 'filter_shortcode');
add_action('wp_ajax_product_taxonomy_filter_action', 'product_taxonomy_filter_action'); 
add_action('wp_ajax_nopriv_product_taxonomy_filter_action', 'product_taxonomy_filter_action');

function product_taxonomy_filter_action(){
   if( isset( $_POST['data'] ) ){
         $args = array( 'post_type' => 'product', 'posts_per_page' => -1, selected_taxonomy => $_POST['data'], 'orderby' => 'rand' );
    }else{
         $args = array( 'post_type' => 'product', 'posts_per_page' => -1 );
    }
?>
    <div class="filtered_products">
      <?php
        $product_taxanomy_loop = new WP_Query( $args );
        if( $product_taxanomy_loop->have_posts() ){
        ?>
        <div class="filter_single_product woocommerce">  
            <ul class="products columns-4">
            <?php
                while ( $product_taxanomy_loop->have_posts() ) : $product_taxanomy_loop->the_post(); global $product; ?>
                       
                <li class="product type-product">
                    <a class="woocommerce-LoopProduct-link woocommerce-loop-product__link" href="<?php echo get_permalink( $product_taxanomy_loop->post->ID ) ?>" title="<?php echo esc_attr($product_taxanomy_loop->post->post_title ? $product_taxanomy_loop->post->post_title : $product_taxanomy_loop->post->ID); ?>">
                        <div class="brand_product_image"> 
                            <?php if (has_post_thumbnail( $product_taxanomy_loop->post->ID )) echo get_the_post_thumbnail($product_taxanomy_loop->post->ID, 'shop_catalog'); else echo '<img class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" src="'.woocommerce_placeholder_img_src().'" alt="Placeholder" sizes="(max-width: 450px) 100vw, 450px" />'; ?>
                        </div>
                        <h2 class="woocommerce-loop-product__title"><?php the_title(); ?></h2>
                        <span class="price"><?php echo $product->get_price_html(); ?></span>
                    </a>
                         <?php woocommerce_template_loop_add_to_cart( $product_taxanomy_loop->post, $product ); ?>
                </li>

            <?php endwhile; ?>

            <?php wp_reset_query();

            ?>
              </ul>
                    </div>
            <?php
        } else {
            ?><div>No Products found</div><?php
        }
        ?>
    </div>
    <?php
    die();
}

/* //ADD brands taxonomy to woocommerce import 
function add_brands_to_importer( $options ) {
    $options['pb_brands'] = 'Brands';
    return $options;
}
add_filter( 'woocommerce_csv_product_import_mapping_options', 'add_brands_to_importer' );

function add_brands_to_mapping_screen( $columns ) {
    
    // potential column name => column slug
    $columns['Brands'] = 'pb_brands';
    $columns['brands'] = 'pb_brands';
    return $columns;
}
add_filter( 'woocommerce_csv_product_import_mapping_default_columns', 'add_brands_to_mapping_screen' );



function brands_set_taxonomy( $product, $data ) {
    if ( is_a( $product, 'WC_Product' ) ) {
        if( ! empty( $data[ 'pb_brands' ] ) ) {
                    $product->save();
                    $brands = $data[ 'pb_brands' ];
                    $brands = explode(",", $brands);
                    $terms = array();
                    foreach($brands as $brands){
                        if(!get_term_by('name', $brands, 'pb_brands')){
                                $partNumArgs= array(
                                    'cat_name' => $brands,
                                    'taxonomy' => 'pb_brands',
                                );
                                $brands_cat = wp_insert_category($partNumArgs);
                                array_push($terms, $brands_cat);
                        }else{
                                $brands_cat = get_term_by('name', $brands, 'pb_brands')->term_id;
                                array_push($terms, $brands_cat);
                        }
                    }
            wp_set_object_terms( $product->get_id(),  $terms, 'pb_brands' );
        }
    }
    return $product;
}
add_filter( 'woocommerce_product_import_inserted_product_object', 'brands_set_taxonomy', 10, 2 ); 

*/

function product_taxonomy_filter_menu_page(){
    
    ?>
    <h1>Product Taxonomy Filter</h1>
    
    <div class="product-taxononmy-page">
        <table cellpadding="5" class="filter_admin_table">
            <tr>
                <td>Filter Shortcode</td>
                <td>[product_taxonomy_filter]</td>
            </tr>
            <tr>
                <td> Select a Taxanomy for Filter </td>
                <td>
                    <form method="POST" action="options.php" id="product_taxonomy">
                        <?php settings_fields( 'product-taxonomy-option-settings' ); 

                        $produt_taxonomies = get_object_taxonomies('product', 'objects');

                        foreach ($produt_taxonomies as $value) {
                            if ($value->name == 'product_type' || $value->name == 'product_shipping_class' || $value->name == 'product_visibility' || $value->name == str_starts_with($value->name, 'pa_') )
                                continue;   
                            ?>            
                            <label for="product_taxonomy_option"><?php echo $value->label; ?></label>
                            <input type="radio" name="product_taxonomy_option" id="product_taxonomy_option" value="<?php echo $value->name ?>" <?php echo $value->name == selected_taxonomy ? "checked=checked": "checked:false" ; ?> />
                            <?php
                        }?>
                    </td>
            </tr>
            <tr>
                <td>Set Description</td>
                <td><textarea placeholder="Description" name="shortcode_desc"><?php echo shortcode_desc ?></textarea></td>
            </tr>
            <tr>
                <td></td>
                <td><?php submit_button();?>
                    </form>
                    <?php
                    if(isset($_POST["product_taxonomy_option"]))
                    {
                        $product_taxonomy_option = get_option('product_taxonomy_option');
                        update_option("product_taxonomy_option", $product_taxonomy_option);
                    }else{
                       add_option( 'product_taxonomy_option' , 'product_cat' ); 
                    }
                    if(isset($_POST["shortcode_desc"]))
                    {
                        $shortcode_desc_option = get_option( 'shortcode_desc' );
                        update_option("shortcode_desc", $shortcode_desc_option);
                    }
                    else{
                        add_option( 'shortcode_desc' , 'Product Taxonomy Filter' );
                    }
                    ?>
                </td>
            </tr>
                    
           <!--  <tr>
                <td>Import Taxonomy CSV</td>
                <td> <?php
            // if (isset($_POST['submit'])) {
            //     $csv_file = $_FILES['csv_file'];
            //     $csv_to_array = array_map('str_getcsv', file($csv_file['tmp_name']));
                    
            //     foreach ($csv_to_array as $key => $value) {
            //     if ($key == 0)
            //             continue;
            //             $taxonomy = selected_taxonomy;
            //             $terms = array (
            //                 '0' => array (
            //                     'name'          => $value[0],
            //                     'slug'          => $value[1],
            //                     'description'   => $value[2],
            //                 )
            //             );  
            //             wp_insert_term(
            //                         $terms[0]['name'],
            //                         $taxonomy, 
            //                         array(
            //                             'description'   => $terms[0]['description'],
            //                             'slug'          => $terms[0]['slug'],
            //                         )
            //                     );
            //             echo "<div id='message' class='updated fade'><p><strong>".  selected_taxonomy ." Inserted - ".$terms[0]['name']."</strong></p></div>";
            //             unset( $terms ); 
            //     }
            // } else {
                ?>
                <!-- <form action="" method="post" id="import_product_taxonomy" enctype="multipart/form-data">
                    <input type="file" name="csv_file">
                    <input type="submit" name="submit" class="button" value="Import">
                </form> -->

            <?php
            // }
            ?> 
        <!-- </td> 
            </tr>-->
        </table>
    </div>
    <?php
}

function product_filter_settings() {
    register_setting( 'product-taxonomy-option-settings', 'product_taxonomy_option' );
    register_setting( 'product-taxonomy-option-settings', 'shortcode_desc' );
}

