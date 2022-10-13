<?php
namespace Modules\Woocommerce\Utils;

use App\Business, App\BusinessLocation, App\Category,
	App\Product, App\Transaction, App\Contact,
	App\TaxRate, App\VariationTemplate, App\VariationLocationDetails;

use Automattic\WooCommerce\Client;

use Modules\Woocommerce\Entities\WoocommerceSyncLog;

use App\Utils\TransactionUtil;
use App\Utils\ProductUtil;
use App\Utils\Util;

use App\Exceptions\PurchaseSellMismatch;
use Modules\Woocommerce\Exceptions\WooCommerceError;

use DB;

class WoocommerceUtil extends Util
{
	/**
     * All Utils instance.
     *
     */
    protected $transactionUtil;
    protected $productUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct( TransactionUtil $transactionUtil, ProductUtil $productUtil) 
    {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
    }


	public function get_api_settings($business_id)
	{
		$business = Business::find($business_id);
        $woocommerce_api_settings = json_decode($business->woocommerce_api_settings);
        return $woocommerce_api_settings;
	}

	/**
     * Creates Automattic\WooCommerce\Client object
     * @param int $business_id
     * @return obj
     */
	public function woo_client($business_id)
	{
        $woocommerce_api_settings = $this->get_api_settings($business_id);
        if(empty($woocommerce_api_settings)){
            throw new WooCommerceError(__("woocommerce::lang.unable_to_connect"));
        }

        $woocommerce = new Client(
            $woocommerce_api_settings->woocommerce_app_url, 
            $woocommerce_api_settings->woocommerce_consumer_key, 
            $woocommerce_api_settings->woocommerce_consumer_secret,
            [
                'wp_api' => true,
                'version' => 'wc/v2',
                'timeout' => 10000
            ]
        );

        return $woocommerce;
	}

	/**
     * Synchronizes pos categories with Woocommerce categories
     * @param int $business_id
     * @return Void
     */
	public function syncCategories($business_id, $user_id)
	{

		//woocommerce api client object
		$woocommerce = $this->woo_client($business_id);

		$last_synced = $this->getLastSync($business_id, 'categories', false);

		//Update parent categories
		$query = Category::where('business_id', $business_id)
						->where('parent_id', 0);

        //Limit query to last sync
		if (!empty($last_synced)) {
			$query->where('updated_at', '>', $last_synced);
		}

		$categories = $query->get();

        $category_data = [];
        $new_categories = [];
        $created_data = [];
        $updated_data = [];
        foreach ($categories as $category) {
        	if(empty($category->woocommerce_cat_id)){
        		$category_data['create'][] = [
        			'name' => $category->name
        		];
        		$new_categories[] = $category;
        		$created_data[] = $category->name;
        	} else {
        		$category_data['update'][] = [
        			'id' => $category->woocommerce_cat_id,
        			'name' => $category->name
        		];
        		$updated_data[] = $category->name;
        	}
        }

        if(!empty($category_data)) {
        	//Batch update categories
        	$response = $woocommerce->post('products/categories/batch', $category_data);

        	//update woocommerce_cat_id
        	if (!empty($response->create)){
        		foreach ($response->create as $key => $value) {
	        		$new_category = $new_categories[$key];
	        		if ($value->id != 0){
	        			$new_category->woocommerce_cat_id = $value->id;
	        		} else {
	        			if(!empty($value->error->data->resource_id)){
	        				$new_category->woocommerce_cat_id = $value->error->data->resource_id;
	        			}
	        		}
	        	    $new_category->save();
	        	}
        	}
        	
        }

        //Sync child categories
        $query2 = Category::where('business_id', $business_id)
									->where('parent_id', '!=', 0);
		//Limit query to last sync
		if (!empty($last_synced)) {
			$query2->where('updated_at', '>', $last_synced);
		}

        $child_categories = $query2->get();

        $cat_id_woocommerce_id = Category::where('business_id', $business_id)
									->where('parent_id', 0)
                                    ->pluck('woocommerce_cat_id', 'id')
                                    ->toArray();

        $category_data = [];
        $new_categories = [];
        foreach ($child_categories as $category) {
        	if(empty($category->woocommerce_cat_id)){
        		$category_data['create'][] = [
        			'name' => $category->name,
        			'parent' => $cat_id_woocommerce_id[$category->parent_id]
        		];
        		$new_categories[] = $category;
        		$created_data[] = $category->name;
        	} else {
        		$category_data['update'][] = [
        			'id' => $category->woocommerce_cat_id,
        			'name' => $category->name,
        			'parent' => $cat_id_woocommerce_id[$category->parent_id]
        		];
        		$updated_data[] = $category->name;
        	}
        }

        if (!empty($category_data)) {
        	//Batch update categories
        	$response = $woocommerce->post('products/categories/batch', $category_data);

        	//update woocommerce_cat_id
        	if(!empty($response->create)){
	        	foreach ($response->create as $key => $value) {
	        		$new_category = $new_categories[$key];
	        		if($value->id != 0){
	        			$new_category->woocommerce_cat_id = $value->id;
	        		} else {
	        			if(!empty($value->error->data->resource_id)){
	        				$new_category->woocommerce_cat_id = $value->error->data->resource_id;
	        			}
	        		}
	        	    $new_category->save();
	        	}
	        }
        }

        //Create log
        if(!empty($created_data)){
        	$this->createSyncLog($business_id, $user_id, 'categories', 'created', $created_data);
        }
        if(!empty($updated_data)){
        	$this->createSyncLog($business_id, $user_id, 'categories', 'updated', $updated_data);
        }
        if(empty($created_data) && empty($updated_data)){
        	$this->createSyncLog($business_id, $user_id, 'categories');
        }
	}

	/**
     * Synchronizes pos products with Woocommerce products
     * @param int $business_id
     * @return Void
     */
	public function syncProducts($business_id, $user_id, $sync_type)
	{
		//Sync Categories
		$this->syncCategories($business_id, $user_id);

        //Sync variation attributes
        $this->syncVariationAttributes($business_id);

        $last_synced = $this->getLastSync($business_id, 'all_products', false);
        //woocommerce api client object
        $woocommerce = $this->woo_client($business_id);
        $woocommerce_api_settings = $this->get_api_settings($business_id);
        $created_data = [];
        $updated_data = [];

        $query = Product::where('business_id', $business_id)
                        ->whereIn('type', ['single', 'variable'])
                        ->with(['variations', 'category', 'sub_category', 
                            'variations.variation_location_details', 
                            'variations.product_variation', 
                            'variations.product_variation.variation_template']);

        if ($sync_type == 'new') {
            $query->whereNull('woocommerce_product_id');
        }

        $all_products = $query->get();
        $product_data = [];
        $new_products = [];
        $business_location_id = $woocommerce_api_settings->location_id;
        foreach ($all_products as $product) {
            //Skip product if last updated is less than last sync
            $last_updated = $product->updated_at;
            //check last stock updated
            $last_stock_updated = $this->getLastStockUpdated($business_location_id,  $product->id); 

            if(!empty($last_stock_updated)){
                $last_updated = strtotime($last_stock_updated) > strtotime($last_updated) ?
                        $last_stock_updated : $last_updated;
            }
            if(!empty($last_synced) && strtotime($last_updated) < strtotime($last_synced)){
                continue;
            }

            //Set common data
            $array = [
                'type' => $product->type == 'single' ? 'simple' : 'variable',
                'sku' => $product->sku,
                'manage_stock' => ($product->enable_stock == 1 && $product->type == 'single') ? true : false,
                'description' => $product->product_description
            ];

            //Get details from first variation for single product only
            $first_variation = $product->variations->first();
            $price = $woocommerce_api_settings->product_tax_type == 'exc' ? $first_variation->default_sell_price : $first_variation->sell_price_inc_tax;

            if(!empty($woocommerce_api_settings->default_selling_price_group)) {
                $group_prices = $this->productUtil->getVariationGroupPrice($first_variation->id, $woocommerce_api_settings->default_selling_price_group, $product->tax_id);

                $price = $woocommerce_api_settings->product_tax_type == 'exc' ? $group_prices['price_exc_tax'] : $group_prices['price_inc_tax'];
            }

            //Set product stock
            $qty_available = 0;
            if ($array['manage_stock']) {
                $variation_location_details = $first_variation->variation_location_details;
                foreach ($variation_location_details as $vld) {
                    if($vld->location_id == $business_location_id){
                        $qty_available = $vld->qty_available;
                    }
                }
            }

            //Set product category
            $product_cat = [];
            if(!empty($product->category)){
                $product_cat[] = ['id' => $product->category->woocommerce_cat_id];
            }
            if(!empty($product->sub_category)){
                $product_cat[] = ['id' => $product->sub_category->woocommerce_cat_id];
            }

            //set attributes for variable products
            if ($product->type == 'variable') {
                $variation_attr_data = [];

                foreach ($product->variations as $variation) {
                    if(!empty($variation->product_variation->variation_template->woocommerce_attr_id)) {
                        $woocommerce_attr_id = $variation->product_variation->variation_template->woocommerce_attr_id;
                        $variation_attr_data[$woocommerce_attr_id][] = $variation->name;
                    }
                }

                foreach ($variation_attr_data as $key => $value) {
                    $array['attributes'][] = [
                        'id' => $key,
                        'variation' => true,
                        'visible'   => true,
                        'options' => $value
                    ];
                }

            }

            if(empty($product->woocommerce_product_id)){
                $array['tax_class'] = !empty($woocommerce_api_settings->default_tax_class) ? 
                $woocommerce_api_settings->default_tax_class : 'standard';

                //assign category
                if(in_array('category', $woocommerce_api_settings->product_fields_for_create)) {
                    if(!empty($product_cat)) {
                        $array['categories'] = $product_cat;
                    }
                }

                if(in_array('weight', $woocommerce_api_settings->product_fields_for_create)){
                    $array['weight'] = $product->weight;
                }

                //assign quantity and price if single product
                if ($product->type == 'single') {
                    if(in_array('quantity', $woocommerce_api_settings->product_fields_for_create)){
                        $array['stock_quantity'] = $qty_available;
                    }
                    $array['regular_price'] = $price;
                }

                //assign name
                $array['name'] = $product->name;

                $product_data['create'][] = $array;
                $new_products[] = $product;

                $created_data[] = $product->sku;
            } else {
                $array['id'] = $product->woocommerce_product_id;
                //assign category
                if(in_array('category', $woocommerce_api_settings->product_fields_for_update)) {
                    if(!empty($product_cat)) {
                        $array['categories'] = $product_cat;
                    }
                }

                if(in_array('weight', $woocommerce_api_settings->product_fields_for_update)){
                    $array['weight'] = $product->weight;
                }

                if ($product->type == 'single') {
                    //assign quantity
                    if(in_array('quantity', $woocommerce_api_settings->product_fields_for_update)) {
                        $array['stock_quantity'] = $qty_available;
                    }
                    //assign price
                    if(in_array('price', $woocommerce_api_settings->product_fields_for_update)) {
                        $array['regular_price'] = $price;
                    }
                }

                //assign name
                if(in_array('name', $woocommerce_api_settings->product_fields_for_update)) {
                    $array['name'] = $product->name;
                }

                $product_data['update'][] = $array;
                $updated_data[] = $product->sku;
            }
        }

        $new_woocommerce_product_ids = [];
        if (!empty($product_data)) {
            $response = $woocommerce->post('products/batch', $product_data);

            if (!empty($response->create)) {
                foreach ($response->create as $key => $value) {
                    $new_product = $new_products[$key];
                    if($value->id != 0){
                        $new_product->woocommerce_product_id = $value->id;
                    } else {
                        if(!empty($value->error->data->resource_id)){
                            $new_product->woocommerce_product_id = $value->error->data->resource_id;
                        }
                    }
                    $new_product->save();

                    $new_woocommerce_product_ids[] = $new_product->woocommerce_product_id;
                }
            }
        }

        //Create log
        if(!empty($created_data)){
            if($sync_type == 'new'){
                $this->createSyncLog($business_id, $user_id, 'new_products', 'created', $created_data);
            } else {
                $this->createSyncLog($business_id, $user_id, 'all_products', 'created', $created_data);
            }
        }
        if(!empty($updated_data)){
            $this->createSyncLog($business_id, $user_id, 'all_products', 'updated', $updated_data);
        }

        //Sync variable product variations
        $this->syncProductVariations($business_id, $sync_type, $new_woocommerce_product_ids);

        if(empty($created_data) && empty($updated_data)){
            if($sync_type == 'new'){
                $this->createSyncLog($business_id, $user_id, 'new_products');
            } else {
                $this->createSyncLog($business_id, $user_id, 'all_products');
            }
        }
	}

    /**
     * Synchronizes pos variation templates with Woocommerce product attributes
     * @param int $business_id
     * @return Void
     */
    public function syncVariationAttributes($business_id)
    {
        $woocommerce = $this->woo_client($business_id);
        $query = VariationTemplate::where('business_id', $business_id);

        $last_synced = $this->getLastSync($business_id, 'all_products', false);

        //Limit query to last sync
        if (!empty($last_synced)) {
            $query->where('updated_at', '>', $last_synced);
        }

        $attributes = $query->get();

        $data = [];
        $new_attrs = [];
        foreach ($attributes as $attr) {
            if(empty($attr->woocommerce_attr_id)){
                $data['create'][] = ['name' => $attr->name];
                $new_attrs[] = $attr;
            } else {
                $data['update'][] = [
                    'name' => $attr->name,
                    'id' => $attr->woocommerce_attr_id
                ];
            }
        }

        if(!empty($data)){
            $response = $woocommerce->post('products/attributes/batch', $data);

            //update woocommerce_attr_id
            if(!empty($response->create)){
                foreach ($response->create as $key => $value) {
                    $new_attr = $new_attrs[$key];
                    if($value->id != 0){
                        $new_attr->woocommerce_attr_id = $value->id;
                    } else {
                        $all_attrs = $woocommerce->get('products/attributes');
                        foreach ($all_attrs as $attr) {
                            if(strtolower($attr->name) == strtolower($new_attr->name)){
                                $new_attr->woocommerce_attr_id = $attr->id;
                            }
                        }
                    }
                    $new_attr->save();
                }
            }
        }
    }

    /**
     * Synchronizes pos products variations with Woocommerce product variations
     * @param int $business_id
     * @param string $sync_type
     * @param array $new_woocommerce_product_ids (woocommerce product id of newly created products to sync)
     * @return Void
     */
    public function syncProductVariations($business_id, $sync_type = 'all', $new_woocommerce_product_ids = []){
        //woocommerce api client object
        $woocommerce = $this->woo_client($business_id);
        $woocommerce_api_settings = $this->get_api_settings($business_id);

        $query = Product::where('business_id', $business_id)
                        ->where('type', 'variable')
                        ->with(['variations', 
                            'variations.variation_location_details', 
                            'variations.product_variation', 
                            'variations.product_variation.variation_template']);

        if ($sync_type == 'new') {
            $query->whereIn('woocommerce_product_id', $new_woocommerce_product_ids);
        }

        $variable_products = $query->get();
        $business_location_id = $woocommerce_api_settings->location_id;
        foreach ($variable_products as $product) {

            //Skip product if last updated is less than last sync
            $last_updated = $product->updated_at;

            $last_stock_updated = $this->getLastStockUpdated($business_location_id,  $product->id);

            if(!empty($last_stock_updated)){
                $last_updated = strtotime($last_stock_updated) > strtotime($last_updated) ?
                        $last_stock_updated : $last_updated;
            }
            if(!empty($last_synced) && strtotime($last_updated) < strtotime($last_synced)){
                continue;
            }

            $variations = $product->variations;

            $variation_data = [];
            $new_variations = [];
            foreach ($variations as $variation) {
                $variation_arr = [
                    'sku' => $variation->sub_sku,
                    'manage_stock' => $product->enable_stock == 1 ? true : false,
                ];

                if(!empty($variation->product_variation->variation_template->woocommerce_attr_id)){
                    $variation_arr['attributes'][] = [
                        'id' => $variation->product_variation->variation_template->woocommerce_attr_id,
                        'option' => $variation->name
                    ];
                }

                $price = $woocommerce_api_settings->product_tax_type == 'exc' ? $variation->default_sell_price : $variation->sell_price_inc_tax;

                if(!empty($woocommerce_api_settings->default_selling_price_group)) {
                    $group_prices = $this->productUtil->getVariationGroupPrice($variation->id, $woocommerce_api_settings->default_selling_price_group, $product->tax_id);

                    $price = $woocommerce_api_settings->product_tax_type == 'exc' ? $group_prices['price_exc_tax'] : $group_prices['price_inc_tax'];
                }

                //Set product stock
                $qty_available = 0;
                if ($product->enable_stock == 1) {
                    $variation_location_details = $variation->variation_location_details;
                    foreach ($variation_location_details as $vld) {
                        if($vld->location_id == $business_location_id){
                            $qty_available = $vld->qty_available;
                        }
                    }
                }

                if(empty($variation->woocommerce_variation_id)){
                    if(in_array('quantity', $woocommerce_api_settings->product_fields_for_create)){
                        $variation_arr['stock_quantity'] = $qty_available;
                    }
                    $variation_arr['regular_price'] = $price;
                    $new_variations[] = $variation;

                    $variation_data['create'][] = $variation_arr;
                } else {
                    $variation_arr['id'] = $variation->woocommerce_variation_id;

                    if(in_array('quantity', $woocommerce_api_settings->product_fields_for_update)) {
                        $variation_arr['stock_quantity'] = $qty_available;
                    }
                    //assign price
                    if(in_array('price', $woocommerce_api_settings->product_fields_for_update)) {
                        $variation_arr['regular_price'] = $price;
                    }

                    $variation_data['update'][] = $variation_arr;
                }
            }
           
            if(!empty($variation_data)){
                $response = $woocommerce->post('products/' . $product->woocommerce_product_id . '/variations/batch', $variation_data);

                //update woocommerce_variation_id
                if (!empty($response->create)) {
                    foreach ($response->create as $key => $value) {
                        $new_variation = $new_variations[$key];
                        if($value->id != 0){
                            $new_variation->woocommerce_variation_id = $value->id;
                        } else {
                            if(!empty($value->error->data->resource_id)){
                                $new_variation->woocommerce_variation_id = $value->error->data->resource_id;
                            }
                        }
                        $new_variation->save();
                    }
                }
            }
        }
    }

	/**
     * Synchronizes Woocommers Orders with POS sales
     * @param int $business_id
     * @param int $user_id
     * @return void
     */
	public function syncOrders($business_id, $user_id)
	{
		
		$last_synced = $this->getLastSync($business_id, 'orders', false);
		$orders = $this->getAllResponse($business_id, 'orders');
		
		$woocommerce_sells = Transaction::where('business_id', $business_id)
    							->whereNotNull('woocommerce_order_id')
    							->with('sell_lines', 'sell_lines.product', 'payment_lines')
    							->get();

		$new_orders = [];
		$updated_orders = [];

		$woocommerce_api_settings = $this->get_api_settings($business_id);
		$business = Business::find($business_id);
        $business_data = [
        	'id' => $business_id,
            'accounting_method' => $business->accounting_method,
            'location_id' => $woocommerce_api_settings->location_id
        ];

        $created_data = [];
        $updated_data = [];
        $create_error_data = [];
        $update_error_data = [];

		foreach ($orders as $order) {
			//Only consider orders modified after last sync
			if((!empty($last_synced) && strtotime($order->date_modified) <= strtotime($last_synced)) || in_array($order->status, ['auto-draft'])){
				continue;
			}
			//Search if order already exists
			$sell = $woocommerce_sells->filter(function($item) use ($order) {
			    return $item->woocommerce_order_id == $order->id;
			})->first();


			if(empty($sell)) {
				$created = $this->createNewSaleFromOrder($business_id, $user_id, $order, $business_data);
				$created_data[] = $order->number;

                if($created !== true){
                    $create_error_data[] = $created;
                }

			} else {
				$updated = $this->updateSaleFromOrder($business_id,$user_id, $order, $sell, $business_data);
				$updated_data[] = $order->number;

                if($updated !== true){
                    $update_error_data[] = $updated;
                }
			}
		}

		//Create log
        if(!empty($created_data)){
        	$this->createSyncLog($business_id, $user_id, 'orders', 'created', $created_data, $create_error_data);
        }
        if(!empty($updated_data)){
        	$this->createSyncLog($business_id, $user_id, 'orders', 'updated', $updated_data, $update_error_data);
        }

        if(empty($created_data) && empty($updated_data)){
            $error_data = $create_error_data + $update_error_data;
	        $this->createSyncLog($business_id, $user_id, 'orders', null, [], $error_data);
	    }

	}

	/**
     * Creates new sales in POSfrom woocommerce order list
     * @param id $business_id
     * @param id $user_id
     * @param obj $order
     * @param array $business_data
     */
	public function createNewSaleFromOrder($business_id, $user_id, $order, $business_data)
	{

		$input = $this->formatOrderToSale($business_id, $user_id,$order);

        if(!empty($input['has_error'])){
            return $input['has_error'];
        }

		$invoice_total = [
			'total_before_tax' => $order->total,
			'tax' => 0,
		];

        DB::beginTransaction();

		$transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id);
		$transaction->woocommerce_order_id = $order->id;
		$transaction->save();

		//Create sell lines
		$this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id'], false, null, ['woocommerce_line_items_id' => 'line_item_id']);

		$this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment'],$business_id, $user_id);

		if ($input['status'] == 'final') {
            //update product stock
            foreach ($input['products'] as $product) {
                if ($product['enable_stock']) {
                    $this->productUtil->decreaseProductQuantity(
                        $product['product_id'],
                        $product['variation_id'],
                        $input['location_id'],
                        $this->productUtil->num_uf($product['quantity'])
                    );
                }
            }

             //Update payment status
	        $transaction->payment_status = 'paid';
	        $transaction->save();

            try {
                $this->transactionUtil->mapPurchaseSell($business_data, $transaction->sell_lines, 'purchase');
            } catch (PurchaseSellMismatch $e) {
                DB::rollBack();

                return [
                    'error_type' => 'order_insuficient_product_qty',
                    'order_number' => $order->number,
                    'msg' => $e->getMessage()
                ];
            }
		}

        DB::commit();

        return true;
	}

	/**
     * Formats Woocommerce order response to pos sale request
     * @param id $business_id
     * @param id $user_id
     * @param obj $order
     * @param obj $sell = null
     */
	public function formatOrderToSale($business_id, $user_id, $order, $sell = null)
	{

		$woocommerce_api_settings = $this->get_api_settings($business_id);

		//Create sell line data
		$product_lines = [];

		//For updating sell lines
		$sell_lines = [];
		if (!empty($sell)) {
			$sell_lines = $sell->sell_lines;
		}

		foreach ($order->line_items as $product_line) {
			$product = Product::where('business_id', $business_id)
							->where('woocommerce_product_id', $product_line->product_id)
							->with(['variations'])
							->first();

			$unit_price = $product_line->total / $product_line->quantity;
			$line_tax = !empty($product_line->total_tax) ? $product_line->total_tax : 0;
			$unit_line_tax = $line_tax / $product_line->quantity;
			$unit_price_inc_tax = $unit_price + $unit_line_tax;
			if (!empty($product)) {

                //Set sale line variation;If single product then first variation
                //else search for woocommerce_variation_id in all the variations
				if($product->type == 'single'){
					$variation = $product->variations->first();
                } else {
                    foreach ($product->variations as $v) {
                        if($v->woocommerce_variation_id == $product_line->variation_id){
                            $variation = $v;
                        }
                    }
                }

				//Check if line tax exists append to sale line data
				$tax_id = null;
				if(!empty($product_line->taxes)){
					foreach ($product_line->taxes as $tax) {

						$pos_tax = TaxRate::where('business_id', $business_id)
						->where('woocommerce_tax_rate_id', $tax->id)
						->first();

						if(!empty($pos_tax)){
							$tax_id = $pos_tax->id;
							break;
						}
					}
				}

				$product_data = [
					'product_id' => $product->id,
					'unit_price' => $unit_price,
					'unit_price_inc_tax' => $unit_price_inc_tax,
					'variation_id' => $variation->id,
					'quantity' => $product_line->quantity,
					'enable_stock' => $product->enable_stock,
					'item_tax' => $line_tax,
					'tax_id' => $tax_id,
					'line_item_id' => $product_line->id
				];
				
				//append transaction_sell_lines_id if update 
				if(!empty($sell_lines)){
					foreach ($sell_lines as $sell_line) {
						if($sell_line->woocommerce_line_items_id == 
							$product_line->id) {
							$product_data['transaction_sell_lines_id'] = $sell_line->id;
						}
					}
				}

				$product_lines[] = $product_data;
				
			} else {

                return ['has_error' => 
                        [
                            'error_type' => 'order_product_not_found',
                            'order_number' => $order->number,
                            'product' => $product_line->name . ' SKU:' . $product_line->sku
                        ]
                    ];
                exit;
			}
		}

		//Get customer details
		$order_customer_id = $order->customer_id;

        //If Customer empty skip order
        if(empty($order_customer_id)){
            return ['has_error' => 
                    [
                        'error_type' => 'order_customer_empty',
                        'order_number' => $order->number
                    ]
                ];
            exit;
        }

        //woocommerce api client object
        $woocommerce = $this->woo_client($business_id);

		$order_customer = $woocommerce->get('customers/' . $order_customer_id );
		$customer = Contact::where('business_id', $business_id)
							->where('email', $order_customer->email)
							->OnlyCustomers()
							->first();

		//If customer not found create new
		if(empty($customer)){
			$ref_count = $this->transactionUtil->setAndGetReferenceCount('contacts', $business_id);
			$contact_id = $this->transactionUtil->generateReferenceNumber('contacts', $ref_count, $business_id);

			$customer_data = [
				'business_id' => $business_id,
				'type' => 'customer',
				'name' => $order_customer->first_name . ' ' . $order_customer->last_name,
				'email' => $order_customer->email,
				'contact_id' => $contact_id,
				'mobile' => $order_customer->billing->phone,
				'city' => $order_customer->billing->city,
				'state' => $order_customer->billing->state,
				'country' => $order_customer->billing->country,
				'created_by' => $user_id
			];
			$customer = Contact::create($customer_data);
		}

		$sell_status = $this->woocommerceOrderStatusToPosSellStatus($order->status);
		$new_sell_data = [
			'business_id' => $business_id,
			'location_id' => $woocommerce_api_settings->location_id,
			'contact_id' => $customer->id,
			'discount_type' => 'fixed',
			'discount_amount' => $order->discount_total,
			'shipping_charges' => $order->shipping_total,
			'final_total' => $order->total,
			'created_by' => $user_id,
			'status' => $sell_status,
			'payment_status' => 'paid',
			'additional_notes' => '',
			'transaction_date' => $order->date_created,
			'customer_group_id' => $customer->customer_group_id,
			'tax_rate_id' => null,
			'sale_note' => null,
			'commission_agent' => null,
			'invoice_no' => $order->number
		];

		$payment = [
			'amount' => $order->total,
			'method' => 'cash',
			'card_transaction_number' => '',
            'card_number' => '',
            'card_type' => '',
            'card_holder_name' => '',
            'card_month' => '',
            'card_security' => '',
            'cheque_number' =>'',
            'bank_account_number' => '',
            'note' => $order->payment_method_title,
            'paid_on' => $order->date_paid
		];

		if(!empty($sell) && count($sell->payment_lines) > 0){
			$payment['payment_id'] = $sell->payment_lines->first()->id;
		}

		$new_sell_data['products'] = $product_lines;
		$new_sell_data['payment'] = [$payment];

		return $new_sell_data;
	}

	/**
     * Updates existing sale
     * @param id $business_id
     * @param id $user_id
     * @param obj $order
     * @param obj $sell
     * @param array $business_data
     */
	public function updateSaleFromOrder($business_id,$user_id, $order, $sell, $business_data)
	{

		$input = $this->formatOrderToSale($business_id, $user_id, $order, $sell);

        if(!empty($input['has_error'])){
            return $input['has_error'];
        }

		$invoice_total = [
			'total_before_tax' => $order->total,
			'tax' => 0,
		];

		$status_before = $sell->status;

        DB::beginTransaction();
		$transaction = $this->transactionUtil->updateSellTransaction($sell, $business_id, $input, $invoice_total, $user_id);

		//Update Sell lines
        $deleted_lines = $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id'], true, $status_before);

        $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);

        //Update payment status
        $transaction->payment_status = 'paid';
        $transaction->save();

        //Update product stock
        $this->productUtil->adjustProductStockForInvoice($status_before, $transaction, $input);

        try {
            $this->transactionUtil->adjustMappingPurchaseSell($status_before, $transaction, $business_data, $deleted_lines);
        } catch (PurchaseSellMismatch $e) {
            DB::rollBack();
            return [
                'error_type' => 'order_insuficient_product_qty',
                'order_number' => $order->number,
                'msg' => $e->getMessage()
            ];
        }

        DB::commit();

        return true;

	}

	/**
     * Creates sync log in the database
     * @param id $business_id
     * @param id $user_id
     * @param string $type
     * @param array $errors = null
     */
	public function createSyncLog($business_id, $user_id, $type, $operation = null, $data = [], $errors = null)
	{
		WoocommerceSyncLog::create([
			'business_id' => $business_id,
			'sync_type' => $type,
			'created_by' => $user_id,
			'operation_type' => $operation,
			'data' => json_encode($data),
            'details' => !empty($errors) ? json_encode($errors) : null
		]);
	}

	/**
     * Retrives last synced date from the database
     * @param id $business_id
     * @param string $type
     * @param bool $for_humans = true
     */
	public function getLastSync($business_id, $type, $for_humans = true)
	{
		$last_sync = WoocommerceSyncLog::where('business_id', $business_id)
							->where('sync_type', $type)
							->max('created_at');
		if(!empty($last_sync) && $for_humans ){
			$last_sync = \Carbon::createFromFormat('Y-m-d H:i:s', $last_sync)->diffForHumans();
		}
		return $last_sync;
	}

	public function woocommerceOrderStatusToPosSellStatus($status)
	{
		$status_array = [
			'pending' => 'draft', 
			'processing' => 'final', 
			'on-hold' => 'draft', 
			'completed' => 'final', 
			'cancelled' => 'draft', 
			'refunded' => 'draft', 
			'failed' => 'draft'
		];

		return $status_array[$status];
	}

	/**
     * Splits response to list of 100 and merges all
     * @param int $business_id
     * @param string $endpoint
     * @param array $params = []
     *
     * @return array
     */
	public function getAllResponse($business_id, $endpoint, $params = []){

		//woocommerce api client object
		$woocommerce = $this->woo_client($business_id);

		$page = 1;
		$list = [];
		$all_list = [];
		$params['per_page'] = 100;

		do{
			$params['page'] = $page;
		  try {
		    $list = $woocommerce->get($endpoint, $params);
		  } catch (\Exception $e){
		    return [];
		  }
		  $all_list = array_merge($all_list,$list);
		  $page++;
		} while (count($list) > 0);

		return $all_list;
	}

	/**
     * Retrives all tax rates from woocommerce api
     * @param id $business_id
     *
     * @param obj $tax_rates
     */
	public function getTaxRates($business_id){
		$tax_rates = $this->getAllResponse($business_id, 'taxes');
		return $tax_rates;
	}

    public function getLastStockUpdated($location_id, $product_id)
    {
        $last_updated = VariationLocationDetails::where('location_id', $location_id)
                                    ->where('product_id', $product_id)
                                    ->max('updated_at');

        return $last_updated;
    }
}