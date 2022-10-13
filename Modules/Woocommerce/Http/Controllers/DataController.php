<?php

namespace Modules\Woocommerce\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class DataController extends Controller
{
	public function dummy_data(){
		Artisan::call('db:seed', ["--class" => 'Modules\Woocommerce\Database\Seeders\AddDummySyncLogTableSeeder']);
	}

    public function superadmin_package(){
        return [
            [
                'name' => 'woocommerce_module',
                'label' => __('woocommerce::lang.woocommerce_module'),
                'default' => false
            ]
        ];
    }

    /**
     * Defines user permissions for the module.
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'woocommerce.syc_categories',
                'label' => __('woocommerce::lang.sync_product_categories'),
                'default' => false
            ],
            [
                'value' => 'woocommerce.sync_products',
                'label' => __('woocommerce::lang.sync_products'),
                'default' => false
            ],
            [
                'value' => 'woocommerce.sync_orders',
                'label' => __('woocommerce::lang.sync_orders'),
                'default' => false
            ],
            [
                'value' => 'woocommerce.map_tax_rates',
                'label' => __('woocommerce::lang.map_tax_rates'),
                'default' => false
            ],
            [
                'value' => 'woocommerce.access_woocommerce_api_settings',
                'label' => __('woocommerce::lang.access_woocommerce_api_settings'),
                'default' => false
            ],

        ];
    }
}
