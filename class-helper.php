<?php

class GoCuotas_Helper
{

    private static $instance;

    private function __construct()
    {
        add_filter('woocommerce_get_price_html', [$this, 'show_fees_product'], 10, 2);
        add_filter('woocommerce_available_variation', [$this, 'show_fees_product_variations'], 10, 3);
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    //logger
    public static function go_log($log, $save)
    {
        $logg = fopen(__DIR__ . '/logs/' . $log, 'a');
        fwrite($logg, $save);
        fclose($logg);
    }


    public function fees($price, $product_id)
    {
        //echo $product_id;
        $product = wc_get_product($product_id);
        $sale_price = $product->get_sale_price();
        $regular_price = $product->get_regular_price();

        if ($product->is_type('variable')) {
            $precio = $product->get_price();
            $cuota = $precio / get_option('woocommerce_gocuotas_settings', true)['fees_number'];
            $cuota = number_format($cuota, 2, '.', ',');
            $new_price =  $price . '<span class="custom-price-prefix singlefee">' . get_option('woocommerce_gocuotas_settings', true)['fees_text'] . ' $' . $cuota . ' con <a id="fee" href="https://www.gocuotas.com" target="_blank"> <img style="max-height: 35px;" src="' . plugin_dir_url(__FILE__) . 'logo.svg"> </a></span>';
            return $new_price;
        }

        if ($product->is_type('simple')) {
            $cuota = $sale_price ? $sale_price / get_option('woocommerce_gocuotas_settings', true)['fees_number'] : $regular_price / get_option('woocommerce_gocuotas_settings', true)['fees_number'];
            $cuota = number_format($cuota, 2, '.', ',');
            $new_price = $price . '<span class="custom-price-prefix singlefee">' . get_option('woocommerce_gocuotas_settings', true)['fees_text'] . ' $' . $cuota . ' con <a id="fee" href="https://www.gocuotas.com" target="_blank"> <img style="max-height: 35px;" src="' . plugin_dir_url(__FILE__) . 'logo.svg"> </a></span>';
            return $new_price;
        }
    }

    public function show_fees_product($price, $product)
    {
        $post = get_post_type(get_queried_object_id(  ));

        if($post != 'product') return;
 
        if (is_admin()) return $price;

        if(get_option('woocommerce_gocuotas_settings', true)['enabled'] == 'no') return $price;  
        
        if(get_option('woocommerce_gocuotas_settings', true)['max_total'] < $product->get_price() && get_option('woocommerce_gocuotas_settings', true)['max_total']!= '') return $price;
        
        if (get_option('woocommerce_gocuotas_settings', true)['show_fees_product'] == 'yes' && is_product()) {
            
            return $this->fees($price, get_queried_object_id(  ));
        }

        if (get_option('woocommerce_gocuotas_settings', true)['show_fees_category'] == 'yes' && !is_product()) {
            return $this->fees($price, get_queried_object_id(  ));
        }

        return $price;
    }

    public function show_fees_product_variations($variation_data, $product, $variation)
    {
        if (get_option('woocommerce_gocuotas_settings', true)['show_fees_product'] == 'yes' && is_product()) {
            $cuota = $variation_data['display_price'] / get_option('woocommerce_gocuotas_settings', true)['fees_number'];
            $cuota = number_format($cuota, 2, '.', ',');
            $variation_data['price_html'] .= '<span class="custom-price-prefix">' . get_option('woocommerce_gocuotas_settings', true)['fees_text'] . ' $' . $cuota . ' con <a id="fee" href="https://www.gocuotas.com" target="_blank"> <img style="max-height: 35px;" src="' . plugin_dir_url(__FILE__) . 'logo.svg"> </a></span>';

            return $variation_data;
        }

        return $variation_data;
    }
}

GoCuotas_Helper::getInstance();