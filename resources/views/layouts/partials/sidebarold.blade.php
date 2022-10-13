@inject('request', 'Illuminate\Http\Request')

<!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar Menu -->
      <ul class="sidebar-menu">

        <!-- Call superadmin module if defined -->
        @if(Module::has('Superadmin'))
          @include('superadmin::layouts.partials.sidebar')
        @endif

        <!-- <li class="header">HEADER</li> -->
        <li class="{{ $request->segment(1) == 'home' ? 'active' : '' }}">
          <a href="{{action('HomeController@index')}}">
            <i class="fa fa-dashboard"></i> <span>
            @lang('home.home')</span>
          </a>
        </li>
         <li class="{{ $request->segment(1) == 'upload-logo' ? 'active' : '' }}">
          <a href="{{route('image.upload.get')}}" >
            <i class="fa fa-arrow-circle-up"></i>
           Logo</span>
          </a>
        </li>
        @if(auth()->user()->can('user.view') || auth()->user()->can('user.create') || auth()->user()->can('roles.view'))
        <li class="treeview {{ in_array($request->segment(1), ['roles', 'users', 'sales-commission-agents']) ? 'active active-sub' : '' }}">
            <a href="#">
                <i class="fa fa-users"></i>
                <span class="title">@lang('user.user_management')</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
              @can( 'user.view' )
                <li class="{{ $request->segment(1) == 'users' ? 'active active-sub' : '' }}">
                  <a href="{{action('ManageUserController@index')}}">
                      <i class="fa fa-user"></i>
                      <span class="title">
                          @lang('user.users')
                      </span>
                  </a>
                </li>
              @endcan
              @can('roles.view')
                <li class="{{ $request->segment(1) == 'roles' ? 'active active-sub' : '' }}">
                  <a href="{{action('RoleController@index')}}">
                      <i class="fa fa-briefcase"></i>
                      <span class="title">
                        @lang('user.roles')
                      </span>
                  </a>
                </li>
              @endcan
              {{-- @can('user.create')
                <li class="{{ $request->segment(1) == 'sales-commission-agents' ? 'active active-sub' : '' }}">
                  <a href="{{action('SalesCommissionAgentController@index')}}">
                      <i class="fa fa-handshake-o"></i>
                      <span class="title">
                        @lang('lang_v1.sales_commission_agents')
                      </span>
                  </a>
                </li>
              @endcan --}}
            </ul>
        </li>
        @endif
        {{-- Ecommerce --}}
        @if(auth()->user()->can('user.view') || auth()->user()->can('user.create') || auth()->user()->can('roles.view'))
        <li class="treeview {{ in_array($request->segment(1), ['roles', 'users', 'sales-commission-agents']) ? 'active active-sub' : '' }}">
            <a href="#">
                <i class="fa fa-users"></i>
                <span class="title">Ecommerce</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
              <li class="{{ $request->segment(1) == 'ecom-sells' ? 'active' : '' }}">
                <a href="{{route('orders.sell')}}">
                  <i class="fa fa-arrow-circle-up"></i>
                  Ecommerce Sells</span>
                </a>
            </li>
                <li class="{{ $request->segment(1) == 'orders' ? 'active' : '' }}">
                    <a href="{{route('orders.view')}}">
                      <i class="fa fa-arrow-circle-up"></i>
                      Orders ({{\App\Order2::where("delivery_status","pending")->get()->count()}})</span>
                    </a>
                </li>
                <li class="{{ $request->segment(1) == 'orders' ? 'active' : '' }}">
                    <a href="{{env('APP_URL')}}/orders?delivery_status=cancelled&date=&search=">
                      <i class="fa fa-arrow-circle-up"></i>
                    Cancelled  Orders</span>
                    </a>
                </li>
                <li class="{{ $request->segment(1) == 'ecommerce-users' ? 'active' : '' }}">
                    <a href="{{route('users.ecommerce.view')}}">
                      <i class="fa fa-arrow-circle-up"></i>
                      Customers </span>
                    </a>
                </li>
                <li class="{{ $request->segment(1) == 'ecommerce-slider' ? 'active' : '' }}">
                    <a href="{{route('slider.view')}}">
                      <i class="fa fa-arrow-circle-up"></i>
                      Slider </span>
                    </a>
                </li>
                <li class="{{ $request->segment(1) == 'ecommerce-slider' ? 'active' : '' }}">
                    <a href="{{route('delivery-man.view')}}">
                      <i class="fa fa-arrow-circle-up"></i>
                      Delivery Man </span>
                    </a>
                </li>

            </ul>
        </li>
        @endif
        <!-- HRM Management Menu-->
        @if(auth()->user()->can('hrm_employyes.view') || auth()->user()->can('hrm_employyes.create') || auth()->user()->can('hrm_transaction.view') || auth()->user()->can('hrm_transaction.create') || auth()->user()->can('hrm_attendence.view') || auth()->user()->can('hrm_attendence.create'))
        @if(in_array('hr_management', $enabled_modules))
        <li class="treeview {{ in_array($request->segment(1), ['hrm_transaction', 'hrm_employyes', 'hrm_attendence']) ? 'active active-sub' : '' }}">
            <a href="#">
                <i class="fa fa-users"></i>
                <span class="title">HR Management</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
              @can( 'hrm_employyes.view' )
                <li class="{{ $request->segment(1) == 'hrm_employyes' ? 'active active-sub' : '' }}">
                  <a href="{{action('HrmController@index')}}">
                      <i class="fa fa-user"></i>
                      <span class="title">
                          Employees
                      </span>
                  </a>
                </li>
              @endcan
              @can('hrm_transaction.view')
                <li class="{{ $request->segment(1) == 'hrm_transaction' ? 'active active-sub' : '' }}">
                  <a href="{{action('HrmTransactionController@index')}}">
                      <i class="fa fa-briefcase"></i>
                      <span class="title">
                       Transactions
                      </span>
                  </a>
                </li>
              @endcan
              @can('hrm_attendence.view')
                <li class="{{ $request->segment(1) == 'hrm_attendence' ? 'active active-sub' : '' }}">
                  <a href="{{action('HrmAttendenceController@index')}}">
                      <i class="fa fa-handshake-o"></i>
                      <span class="title">
                        Attendence
                      </span>
                  </a>
                </li>
              @endcan
            </ul>
        </li>
        @endif
        @endif
        <!-- End HRM Management Menu -->

        @if(auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') )
          <li class="treeview {{ in_array($request->segment(1), ['contacts', 'customer-group']) ? 'active active-sub' : '' }}" id="tour_step4">
            <a href="#" id="tour_step4_menu"><i class="fa fa-address-book"></i> <span>@lang('contact.contacts')</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              @can('supplier.view')
                <li class="{{ $request->input('type') == 'supplier' ? 'active' : '' }}"><a href="{{action('ContactController@index', ['type' => 'supplier'])}}"><i class="fa fa-star"></i> @lang('report.supplier')</a></li>
              @endcan

              @can('customer.view')
                <li class="{{ $request->input('type') == 'customer' ? 'active' : '' }}"><a href="{{action('ContactController@index', ['type' => 'customer'])}}"><i class="fa fa-star"></i> @lang('report.customer')</a></li>

                <li class="{{ $request->segment(1) == 'customer-group' ? 'active' : '' }}"><a href="{{action('CustomerGroupController@index')}}"><i class="fa fa-users"></i> @lang('lang_v1.customer_groups')</a></li>
              @endcan

              @if(auth()->user()->can('supplier.create') || auth()->user()->can('customer.create') )
                <li class="{{ $request->segment(1) == 'contacts' && $request->segment(2) == 'import' ? 'active' : '' }}"><a href="{{action('ContactController@getImportContacts')}}"><i class="fa fa-download"></i> @lang('lang_v1.import_contacts')</a></li>
              @endcan

            </ul>
          </li>
        @endif

        @if(auth()->user()->can('product.view') ||
        auth()->user()->can('product.create') ||
        auth()->user()->can('brand.view') ||
        auth()->user()->can('unit.view') ||
        auth()->user()->can('category.view') ||
        auth()->user()->can('brand.create') ||
        auth()->user()->can('unit.create') ||
        auth()->user()->can('category.create') )
          <li class="treeview {{ in_array($request->segment(1), ['variation-templates', 'products', 'labels', 'import-products', 'import-opening-stock', 'selling-price-group', 'brands', 'units', 'categories']) ? 'active active-sub' : '' }}" id="tour_step5">
            <a href="#" id="tour_step5_menu"><i class="fa fa-cubes"></i> <span>@lang('sale.products')</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              @can('product.view')
                <li class="{{ $request->segment(1) == 'products' && $request->segment(2) == '' ? 'active' : '' }}"><a href="{{action('ProductController@index')}}"><i class="fa fa-list"></i>@lang('lang_v1.list_products')</a></li>
              @endcan
              @can('product.create')
                <li class="{{ $request->segment(1) == 'products' && $request->segment(2) == 'create' ? 'active' : '' }}"><a href="{{action('ProductController@create')}}"><i class="fa fa-plus-circle"></i>@lang('product.add_product')</a></li>
              @endcan
              @can('product.view')
                <li class="{{ $request->segment(1) == 'labels' && $request->segment(2) == 'show' ? 'active' : '' }}"><a href="{{action('LabelsController@show')}}"><i class="fa fa-barcode"></i>@lang('barcode.print_labels')</a></li>
              @endcan
              @can('product.create')
                <li class="{{ $request->segment(1) == 'variation-templates' ? 'active' : '' }}"><a href="{{action('VariationTemplateController@index')}}"><i class="fa fa-circle-o"></i><span>@lang('product.variations')</span></a></li>
              @endcan
              @can('product.create')
                <li class="{{ $request->segment(1) == 'import-products' ? 'active' : '' }}"><a href="{{action('ImportProductsController@index')}}"><i class="fa fa-download"></i><span>@lang('product.import_products')</span></a></li>
              @endcan
              @can('product.opening_stock')
                <li class="{{ $request->segment(1) == 'import-opening-stock' ? 'active' : '' }}"><a href="{{action('ImportOpeningStockController@index')}}"><i class="fa fa-download"></i><span>@lang('lang_v1.import_opening_stock')</span></a></li>
              @endcan
              @can('product.create')
                <li class="{{ $request->segment(1) == 'selling-price-group' ? 'active' : '' }}"><a href="{{action('SellingPriceGroupController@index')}}"><i class="fa fa-circle-o"></i><span>@lang('lang_v1.selling_price_group')</span></a></li>
              @endcan

              @if(auth()->user()->can('unit.view') || auth()->user()->can('unit.create'))
                <li class="{{ $request->segment(1) == 'units' ? 'active' : '' }}">
                  <a href="{{action('UnitController@index')}}"><i class="fa fa-balance-scale"></i> <span>@lang('unit.units')</span></a>
                </li>
              @endif

              @if(auth()->user()->can('category.view') || auth()->user()->can('category.create'))
                <li class="{{ $request->segment(1) == 'categories' ? 'active' : '' }}">
                  <a href="{{action('CategoryController@index')}}"><i class="fa fa-tags"></i> <span>@lang('category.categories') </span></a>
                </li>
              @endif

              @if(auth()->user()->can('brand.view') || auth()->user()->can('brand.create'))
                <li class="{{ $request->segment(1) == 'brands' ? 'active' : '' }}">
                  <a href="{{action('BrandController@index')}}"><i class="fa fa-diamond"></i> <span>@lang('brand.brands')</span></a>
                </li>
              @endif
            </ul>
          </li>
        @endif
        @if(auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create') || auth()->user()->can('purchase.update') )
        <li class="treeview {{in_array($request->segment(1), ['purchases', 'purchase-return']) ? 'active active-sub' : '' }}" id="tour_step6">
          <a href="#" id="tour_step6_menu"><i class="fa fa-arrow-circle-down"></i> <span>@lang('purchase.purchases')</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            @can('purchase.view')
              <li class="{{ $request->segment(1) == 'purchases' && $request->segment(2) == null ? 'active' : '' }}"><a href="{{action('PurchaseController@index')}}"><i class="fa fa-list"></i>@lang('purchase.list_purchase')</a></li>
            @endcan
            @can('purchase.create')
              <li class="{{ $request->segment(1) == 'purchases' && $request->segment(2) == 'create' ? 'active' : '' }}"><a href="{{action('PurchaseController@create')}}"><i class="fa fa-plus-circle"></i> @lang('purchase.add_purchase')</a></li>
            @endcan
            @can('purchase.update')
              <li class="{{ $request->segment(1) == 'purchase-return' ? 'active' : '' }}"><a href="{{action('PurchaseReturnController@index')}}"><i class="fa fa-undo"></i> @lang('lang_v1.list_purchase_return')</a></li>
            @endcan
          </ul>
        </li>
        @endif
      
 {{-- <li class="{{ $request->segment(1) == 'orders' ? 'active' : '' }}">
          <a href="{{route('orders.redirect')}}" target="_blank">
            <i class="fa fa-arrow-circle-up"></i>
            Ecom Dashboard</span>
          </a>
        </li> --}}
        @if(auth()->user()->can('sell.view') || auth()->user()->can('sell.create') || auth()->user()->can('direct_sell.access') )
          <li class="treeview {{  in_array( $request->segment(1), ['sells', 'pos', 'sell-return']) ? 'active active-sub' : '' }}" id="tour_step7">
            <a href="#" id="tour_step7_menu"><i class="fa fa-arrow-circle-up"></i> <span>@lang('sale.sale')</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              @can('direct_sell.access')
                <li class="{{ $request->segment(1) == 'sells' && $request->segment(2) == null ? 'active' : '' }}" ><a href="{{action('SellController@index')}}"><i class="fa fa-list"></i>@lang('lang_v1.all_sales')</a></li>
                <li class="{{ $request->segment(1) == 'sells' && $request->segment(2) == null ? 'active' : '' }}" ><a href="{{action('SellController@dueSell')}}"><i class="fa fa-list"></i>Due Sell</a></li>
              @endcan
              @can('direct_sell.access')
                <li class="{{ $request->segment(1) == 'sells' && $request->segment(2) == 'discount-sell' ? 'active' : '' }}" ><a href="{{action('SellController@getDiscountSell')}}"><i class="fa fa-list"></i>Discount Sell</a></li>
              @endcan
              @can('direct_sell.access')
                <li class="{{ $request->segment(1) == 'sells' && $request->segment(2) == 'create' ? 'active' : '' }}"><a href="{{action('SellController@create')}}"><i class="fa fa-plus-circle"></i>@lang('sale.add_sale')</a></li>
              @endcan
              @can('sell.view')
                <li class="{{ $request->segment(1) == 'pos' && $request->segment(2) == null ? 'active' : '' }}" ><a href="{{action('SellPosController@index')}}"><i class="fa fa-list"></i>@lang('sale.list_pos')</a></li>
              @endcan
              @can('sell.create')
                <li class="{{ $request->segment(1) == 'pos' && $request->segment(2) == 'create' ? 'active' : '' }}"><a href="{{action('SellPosController@create')}}"><i class="fa fa-plus-circle"></i>@lang('sale.pos_sale')</a></li>
                <li class="{{ $request->segment(1) == 'sells' && $request->segment(2) == 'drafts' ? 'active' : '' }}" ><a href="{{action('SellController@getDrafts')}}"><i class="fa fa-pencil-square" aria-hidden="true"></i>@lang('lang_v1.list_drafts')</a></li>

                <li class="{{ $request->segment(1) == 'sells' && $request->segment(2) == 'quotations' ? 'active' : '' }}" ><a href="{{action('SellController@getQuotations')}}"><i class="fa fa-pencil-square" aria-hidden="true"></i>@lang('lang_v1.list_quotations')</a></li>
              @endcan
              @can('sell.view')
                <li class="{{ $request->segment(1) == 'sell-return' && $request->segment(2) == null ? 'active' : '' }}" ><a href="{{action('SellReturnController@index')}}"><i class="fa fa-undo"></i>@lang('lang_v1.list_sell_return')</a></li>
              @endcan
            </ul>
          </li>
        @endif

        @if(auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create') )
        <li style="display:none;" class="treeview {{ $request->segment(1) == 'stock-transfers' ? 'active active-sub' : '' }}">
          <a href="#"><i class="fa fa-truck" aria-hidden="true"></i> <span>@lang('lang_v1.stock_transfers')</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            @can('purchase.view')
              <li class="{{ $request->segment(1) == 'stock-transfers' && $request->segment(2) == null ? 'active' : '' }}"><a href="{{action('StockTransferController@index')}}"><i class="fa fa-list"></i>@lang('lang_v1.list_stock_transfers')</a></li>
            @endcan
            @can('purchase.create')
              <li class="{{ $request->segment(1) == 'stock-transfers' && $request->segment(2) == 'create' ? 'active' : '' }}"><a href="{{action('StockTransferController@create')}}"><i class="fa fa-plus-circle"></i>@lang('lang_v1.add_stock_transfer')</a></li>
            @endcan
          </ul>
        </li>
        @endif

        @if(auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create') )
        <li class="treeview {{ $request->segment(1) == 'stock-adjustments' ? 'active active-sub' : '' }}">
          <a href="#"><i class="fa fa-database" aria-hidden="true"></i> <span>@lang('stock_adjustment.stock_adjustment')</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            @can('purchase.view')
              <li class="{{ $request->segment(1) == 'stock-adjustments' && $request->segment(2) == null ? 'active' : '' }}"><a href="{{action('StockAdjustmentController@index')}}"><i class="fa fa-list"></i>@lang('stock_adjustment.list')</a></li>
            @endcan
            @can('purchase.create')
              <li class="{{ $request->segment(1) == 'stock-adjustments' && $request->segment(2) == 'create' ? 'active' : '' }}"><a href="{{action('StockAdjustmentController@create')}}"><i class="fa fa-plus-circle"></i>@lang('stock_adjustment.add')</a></li>
            @endcan

             <!--22.07.2020-->
            @can('purchase.create')
              <li style="display:none;" class="{{ $request->segment(1) == 'physical-stock' ? 'active' : '' }}"><a href="{{action('StockAdjustmentController@PhysicalStock')}}"><i class="fa fa-list"></i>Multiple Physical List</a></li>
              <li style="display:none;" class="{{ $request->segment(1) == 'multiple-product-physical-stock' ? 'active' : '' }}"><a href="{{action('StockAdjustmentController@multiProductPhysicalStock')}}"><i class="fa fa-plus-circle"></i>Multiple Physical Stock</a></li>
            @endcan

          </ul>
        </li>
        @endif

        @if(auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create') )
        <li style="display:none;" class="treeview {{ $request->segment(1) == 'stock-return' ? 'active active-sub' : '' }}">
          <a href="#"><i class="fa fa-database" aria-hidden="true"></i> <span>Purchase Return</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            @can('purchase.view')
              <li class="{{ $request->segment(1) == 'stock-return' && $request->segment(2) == null ? 'active' : '' }}"><a href="{{action('StockReturnController@index')}}"><i class="fa fa-list"></i>Purchase Return List</a></li>
            @endcan
            @can('purchase.create')
              <li class="{{ $request->segment(1) == 'stock-return' && $request->segment(2) == 'create' ? 'active' : '' }}"><a href="{{action('StockReturnController@create')}}"><i class="fa fa-plus-circle"></i>Purchase Return Add</a></li>
            @endcan
          </ul>
        </li>
        @endif

        @if(auth()->user()->can('expense.access'))
        <li class="treeview {{  in_array( $request->segment(1), ['expense-categories', 'expenses']) ? 'active active-sub' : '' }}">
          <a href="#"><i class="fa fa-minus-circle"></i> <span>@lang('expense.expenses')</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="{{ $request->segment(1) == 'expenses' && empty($request->segment(2)) ? 'active' : '' }}"><a href="{{action('ExpenseController@index')}}"><i class="fa fa-list"></i>@lang('lang_v1.list_expenses')</a></li>
            <li class="{{ $request->segment(1) == 'expenses' && $request->segment(2) == 'create' ? 'active' : '' }}"><a href="{{action('ExpenseController@create')}}"><i class="fa fa-plus-circle"></i>@lang('messages.add') @lang('expense.expenses')</a></li>
            <li class="{{ $request->segment(1) == 'expense-categories' ? 'active' : '' }}"><a href="{{action('ExpenseCategoryController@index')}}"><i class="fa fa-circle-o"></i>@lang('expense.expense_categories')</a></li>
          </ul>
        </li>
        @endif
        {{-- doctor --}}
        <li class="treeview {{ in_array($request->segment(1), ['variation-templates', 'products', 'labels', 'import-products', 'import-opening-stock', 'selling-price-group', 'brands', 'units', 'categories']) ? 'active active-sub' : '' }}" id="tour_step5">
            <a href="#" id="tour_step5_menu"><i class="fa fa-cubes"></i> <span>Hospital</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              @can('product.view')
              <li class="{{ $request->segment(1) == 'assistants' && $request->segment(2) == '' ? 'active' : '' }}"><a href="{{action('AssistantController@index')}}"><i class="fa fa-list"></i>Assistants</a></li>
            @endcan
              @can('product.view')
                <li class="{{ $request->segment(1) == 'doctors' && $request->segment(2) == '' ? 'active' : '' }}"><a href="{{action('DoctorController@index')}}"><i class="fa fa-list"></i>Doctors</a></li>
              @endcan
              @can('product.create')
                <li class="{{ $request->segment(1) == 'doctors' && $request->segment(2) == 'create' ? 'active' : '' }}"><a href="{{action('DoctorController@create')}}"><i class="fa fa-plus-circle"></i>Create Doctor</a></li>
              @endcan
              @can('product.create')
              <li class="{{ $request->segment(1) == 'services' && $request->segment(2) == 'create' ? 'active' : '' }}"><a href="{{action('ServiceController@index')}}"><i class="fa fa-plus-circle"></i>Services List</a></li>
            @endcan
              @can('product.create')
                <li class="{{ $request->segment(1) == 'services' && $request->segment(2) == 'create' ? 'active' : '' }}"><a href="{{action('ServiceController@create')}}"><i class="fa fa-plus-circle"></i>Create Service</a></li>
              @endcan
              @can('product.create')
              <li class="{{ $request->segment(1) == 'services' && $request->segment(2) == 'create' ? 'active' : '' }}"><a href="{{action('DoctorController@getCommissions')}}"><i class="fa fa-plus-circle"></i>Commissions</a></li>
            @endcan
              @can('product.create')
                <li class="{{ $request->segment(1) == 'services' && $request->segment(2) == 'create' ? 'active' : '' }}"><a href="{{action('DoctorController@createCommission')}}"><i class="fa fa-plus-circle"></i>Create Commission</a></li>
              @endcan
              @can('product.create')
              <li 
             class="{{ $request->segment(2) == 'doctors' ? 'active' : '' }}"
              >
              <a href="{{action('DoctorController@getDoctorSellReport')}}"><i class="fa fa-money"></i>Doctor Sell Report</a></li>
            @endcan
           

            </ul>
          </li>
           {{-- end of doctor --}}
        @if(auth()->user()->can('purchase_n_sell_report.view')
          || auth()->user()->can('contacts_report.view')
          || auth()->user()->can('supplier_report.view')
          || auth()->user()->can('stock_report.view')
          || auth()->user()->can('tax_report.view')
          || auth()->user()->can('trending_product_report.view')
          || auth()->user()->can('sales_representative.view')
          || auth()->user()->can('register_report.view')
          || auth()->user()->can('expense_report.view')
          )

          <li class="treeview {{  in_array( $request->segment(1), ['reports']) ? 'active active-sub' : '' }}" id="tour_step8">
            <a href="#" id="tour_step8_menu"><i class="fa fa-bar-chart-o"></i> <span>@lang('report.reports')</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
            {{-- @@@@@@@@@@@@ --}}
            {{-- only for admin --}}

            {{-- <li 
            class="{{ $request->segment(2) == 'doctors' ? 'active' : '' }}"
             >
             <a href="{{action('DoctorController@getDoctorReport')}}"><i class="fa fa-money"></i>Doctor Report</a></li> --}}




                <li class="{{ $request->segment(2) == 'profit-loss' ? 'active' : '' }}" ><a href="{{action('ReportController@ecomOrdersReport')}}"><i class="fa fa-money"></i>Ecommerce Order Report</a></li>
            {{-- only for admin --}}




              @can('profit_loss_report.view')
                <li class="{{ $request->segment(2) == 'profit-loss' ? 'active' : '' }}" ><a href="{{action('ReportController@getProfitLoss')}}"><i class="fa fa-money"></i>@lang('report.profit_loss')</a></li>
              @endcan

              @can('purchase_n_sell_report.view')
                <li class="{{ $request->segment(2) == 'supplier-sell-product' ? 'active' : '' }}" >
                  <a href="{{action('ReportController@supplierSellProduct')}}">
                    <i class="fa fa-bomb"></i>Supplier WISE SALES POSITION
                  </a>
                </li>
              @endcan

              @can('purchase_n_sell_report.view')
                <li class="{{ $request->segment(2) == 'supplier-all-stock' ? 'active' : '' }}" >
                  <a href="{{action('ReportController@supplierAllStock')}}">
                    <i class="fa fa-bomb"></i>Supplier Stock Summery
                  </a>
                </li>
              @endcan

              @can('purchase_n_sell_report.view')
                <li class="{{ $request->segment(2) == 'supplier-sell-summery' ? 'active' : '' }}" >
                  <a href="{{action('ReportController@supplierSellSumery')}}">
                    <i class="fa fa-bomb"></i>Supplier Wise Sell Summery
                  </a>
                </li>
              @endcan

              @can('purchase_n_sell_report.view')
                <li class="{{ $request->segment(2) == 'supplier-product-stock' ? 'active' : '' }}" >
                  <a href="{{action('ReportController@supplierProductStock')}}">
                    <i class="fa fa-bomb"></i>Supplier Wise DETAILS STOCK POSITION
                  </a>
                </li>
              @endcan

               @can('purchase_n_sell_report.view')
                <li class="{{ $request->segment(2) == 'supplier-stock-receive' ? 'active' : '' }}" >
                  <a href="{{action('ReportController@supplierStockReceive')}}">
                    <i class="fa fa-bomb"></i>Supplier Wise Stock Receipts
                  </a>
                </li>
              @endcan


              @can('purchase_n_sell_report.view')
                <li class="{{ $request->segment(2) == 'purchase-sell' ? 'active' : '' }}" ><a href="{{action('ReportController@getPurchaseSell')}}"><i class="fa fa-exchange"></i>@lang('report.purchase_sell_report')</a></li>
              @endcan

                @can('purchase_n_sell_report.view')
                <li class="{{ $request->segment(2) == 'purchase-daily-sell' ? 'active' : '' }}" ><a href="{{action('ReportController@getPurchaseSellOnly')}}"><i class="fa fa-exchange"></i>Daily Sale Report</a></li>
              @endcan


             <!----moinul--->
            @can('purchase_n_sell_report.view')
                <li class="{{ $request->segment(2) == 'sale-update-tracking' ? 'active' : '' }}" ><a href="{{action('ReportController@sellUpdateTracking')}}"><i class="fa fa-exchange"></i>Sell Update Tracking Report</a></li>
            @endcan

            <!----moinul--->
            @can('purchase_n_sell_report.view')
                <li class="{{ $request->segment(2) == 'sale-delete-tracking' ? 'active' : '' }}" ><a href="{{action('ReportController@sellDeleteTracking')}}"><i class="fa fa-exchange"></i>Sell Delete Tracking Report</a></li>
            @endcan



              @can('tax_report.view')
                <li class="{{ $request->segment(2) == 'tax-report' ? 'active' : '' }}" ><a href="{{action('ReportController@getTaxReport')}}"><i class="fa fa-tumblr" aria-hidden="true"></i>@lang('report.tax_report')</a></li>
              @endcan

              @can('contacts_report.view')
                <li class="{{ $request->segment(2) == 'customer-supplier' ? 'active' : '' }}" ><a href="{{action('ReportController@getCustomerSuppliers')}}"><i class="fa fa-address-book"></i>@lang('report.contacts')</a></li>
                <li class="{{ $request->segment(2) == 'customer-group' ? 'active' : '' }}" ><a href="{{action('ReportController@getCustomerGroup')}}"><i class="fa fa-users"></i>@lang('lang_v1.customer_groups_report')</a></li>
              @endcan

              @can('supplier_report.view')
               <li class="{{ $request->segment(2) == 'report-supplier' ? 'active' : '' }}" ><a href="{{action('ReportController@getSuppliers')}}"><i class="fa fa-address-book"></i>@lang('report.contacts2')</a></li>
                 @endcan

              @can('stock_report.view')
                <li class="{{ $request->segment(2) == 'stock-report' ? 'active' : '' }}" ><a href="{{action('ReportController@getStockReport')}}"><i class="fa fa-hourglass-half" aria-hidden="true"></i>@lang('report.stock_report')</a></li>
              @endcan

              <!----moinul--->
                @can('stock_report.view')
                <li style="display:none;" class="{{ $request->segment(2) == 'physical-stock-report' ? 'active' : '' }}" ><a href="{{action('ReportController@getPhysicalStockReport')}}"><i class="fa fa-hourglass-half" aria-hidden="true"></i>Physical @lang('report.stock_report')</a></li>
                @endcan

              @can('stock_report.view')
                <li class="{{ $request->segment(2) == 'stock-alert-report' ? 'active' : '' }}" ><a href="{{action('ReportController@getStockAlertReport')}}"><i class="fa fa-hourglass-half" aria-hidden="true"></i>Stock Alert Report</a></li>
              @endcan

              @can('stock_report.view')
                @if(session('business.enable_product_expiry') == 1)
                <li class="{{ $request->segment(2) == 'stock-expiry' ? 'active' : '' }}" ><a href="{{action('ReportController@getStockExpiryReport')}}"><i class="fa fa-calendar-times-o"></i>@lang('report.stock_expiry_report')</a></li>
                @endif
              @endcan

              @can('stock_report.view')
                <li class="{{ $request->segment(2) == 'lot-report' ? 'active' : '' }}" ><a href="{{action('ReportController@getLotReport')}}"><i class="fa fa-hourglass-half" aria-hidden="true"></i>@lang('lang_v1.lot_report')</a></li>
              @endcan

              @can('trending_product_report.view')
                <li class="{{ $request->segment(2) == 'trending-products' ? 'active' : '' }}" ><a href="{{action('ReportController@getTrendingProducts')}}"><i class="fa fa-line-chart" aria-hidden="true"></i>@lang('report.trending_products')</a></li>
              @endcan

              @can('stock_report.view')
                <li class="{{ $request->segment(2) == 'stock-adjustment-report' ? 'active' : '' }}" ><a href="{{action('ReportController@getStockAdjustmentReport')}}"><i class="fa fa-sliders"></i>@lang('report.stock_adjustment_report')</a></li>
              @endcan

              @can('purchase_n_sell_report.view')
                <li class="{{ $request->segment(2) == 'product-purchase-report' ? 'active' : '' }}" ><a href="{{action('ReportController@getproductPurchaseReport')}}"><i class="fa fa-arrow-circle-down"></i>@lang('lang_v1.product_purchase_report')</a></li>

                <li class="{{ $request->segment(2) == 'product-sell-report' ? 'active' : '' }}" ><a href="{{action('ReportController@getproductSellReport')}}"><i class="fa fa-arrow-circle-up"></i>@lang('lang_v1.product_sell_report')</a></li>

                <li class="{{ $request->segment(2) == 'purchase-payment-report' ? 'active' : '' }}" ><a href="{{action('ReportController@purchasePaymentReport')}}"><i class="fa fa-money"></i>@lang('lang_v1.purchase_payment_report')</a></li>

                <li class="{{ $request->segment(2) == 'sell-payment-report' ? 'active' : '' }}" ><a href="{{action('ReportController@sellPaymentReport')}}"><i class="fa fa-money"></i>@lang('lang_v1.sell_payment_report')</a></li>
                <li class="{{ $request->segment(2) == 'product-download' ? 'active' : '' }}" ><a href="{{action('ReportController@productDownload')}}"><i class="fa fa-money"></i>Product Download</a></li>
                <li class="{{ $request->segment(2) == 'product-review' ? 'active' : '' }}" ><a href="{{action('ReportController@productReview')}}"><i class="fa fa-money"></i>Product Review</a></li>
              @endcan

              @can('expense_report.view')
                <li class="{{ $request->segment(2) == 'expense-report' ? 'active' : '' }}" ><a href="{{action('ReportController@getExpenseReport')}}"><i class="fa fa-search-minus" aria-hidden="true"></i></i>@lang('report.expense_report')</a></li>
              @endcan

              @can('register_report.view')
                <li class="{{ $request->segment(2) == 'register-report' ? 'active' : '' }}" ><a href="{{action('ReportController@getRegisterReport')}}"><i class="fa fa-briefcase"></i>@lang('report.register_report')</a></li>
              @endcan

              @can('sales_representative.view')
                <li class="{{ $request->segment(2) == 'sales-representative-report' ? 'active' : '' }}" ><a href="{{action('ReportController@getSalesRepresentativeReport')}}"><i class="fa fa-user" aria-hidden="true"></i>@lang('report.sales_representative')</a></li>
              @endcan

              @if(in_array('tables', $enabled_modules))
                @can('purchase_n_sell_report.view')
                  <li class="{{ $request->segment(2) == 'table-report' ? 'active' : '' }}" ><a href="{{action('ReportController@getTableReport')}}"><i class="fa fa-table"></i>@lang('restaurant.table_report')</a></li>
                @endcan
              @endif
              @if(in_array('service_staff', $enabled_modules))
                @can('sales_representative.view')
                <li class="{{ $request->segment(2) == 'service-staff-report' ? 'active' : '' }}" ><a href="{{action('ReportController@getServiceStaffReport')}}"><i class="fa fa-user-secret"></i>@lang('restaurant.service_staff_report')</a></li>
                @endcan
              @endif

            </ul>
          </li>
        @endif
        @if(auth()->user()->can('ibuser.view') || auth()->user()->can('ibtransaction.create') || auth()->user()->can('ibcategory.create'))
          @if(in_array('internal_banking', $enabled_modules))
            <li class="treeview {{ in_array($request->segment(1), ['internal_banking', 'banking_category']) ? 'active active-sub' : '' }}" id="tour_step4">
              <a href="#" id="tour_step4_menu"><i class="fa fa-address-book"></i> <span>@lang('user.banking')</span>
                <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
              </a>
              <ul class="treeview-menu">
              @if(auth()->user()->can('ibuser.view') || auth()->user()->can('ibuser.create'))
                <li class="{{ $request->segment(1) == 'users' ? 'active' : '' }}"><a href="{{action('BankingController@index')}}"><i class="fa fa-star"></i> @lang('user.userName')</a></li>
              @endif
              @if(auth()->user()->can('ibtransaction.view') || auth()->user()->can('ibtransaction.create'))
                <li class="{{ $request->segment(1) == 'transaction' ? 'active' : '' }}"><a href="{{action('BankTransactionController@index')}}"><i class="fa fa-star"></i> @lang('user.transactions')</a></li>
              @endif
              @if(auth()->user()->can('ibcategory.view') || auth()->user()->can('ibcategory.create'))
                <li class="{{ $request->segment(1) == 'banking_category' ? 'active' : '' }}"><a href="{{action('BankingCategoryController@index')}}"><i class="fa fa-star"></i>Banking Categories</a></li>
              @endif
              </ul>
            </li>
          @endif
        @endif

          @if(auth()->user()->can('tbbank.view') || auth()->user()->can('tbpersonal.view'))
            @if(in_array('transfer_balance', $enabled_modules))
              <li class="treeview {{ in_array($request->segment(1), ['transfer_balance', 'personal']) ? 'active active-sub' : '' }}" id="tour_step4">
                <a href="#" id="tour_step4_menu"><i class="fa fa-address-book"></i> <span>Transfer Balance</span>
                  <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
                </a>
                <ul class="treeview-menu">
                  @if(auth()->user()->can('tbbank.view') || auth()->user()->can('tbbank.create'))
                    <li class="{{ $request->segment(1) == 'bank' ? 'active' : '' }}"><a href="{{action('TransferBalanceBankController@index')}}"><i class="fa fa-star"></i>Bank</a></li>
                  @endif
                  @if(auth()->user()->can('tbpersonal.view') || auth()->user()->can('tbpersonal.create'))
                    <li class="{{ $request->segment(1) == 'personal' ? 'active' : '' }}"><a href="{{action('TransferBalancePersonalController@index')}}"><i class="fa fa-star"></i>Personal</a></li>
                  @endif
                </ul>
              </li>
            @endif
          @endif

          @if(auth()->user()->can('rbbank.view') || auth()->user()->can('rbpersonal.view'))
            @if(in_array('receive_balance', $enabled_modules))
              <li class="treeview {{ in_array($request->segment(1), ['receive_balance', 'personal']) ? 'active active-sub' : '' }}" id="tour_step4">
                <a href="#" id="tour_step4_menu"><i class="fa fa-address-book"></i> <span>Receive Balance</span>
                  <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
                </a>
                <ul class="treeview-menu">
                  @if(auth()->user()->can('rbbank.view') || auth()->user()->can('rbbank.create'))
                    <li class="{{ $request->segment(1) == 'bank' ? 'active' : '' }}"><a href="{{action('ReceiveBalanceBankController@index')}}"><i class="fa fa-star"></i>Bank</a></li>
                  @endif
                  @if(auth()->user()->can('rbpersonal.view') || auth()->user()->can('rbpersonal.create'))
                    <li class="{{ $request->segment(1) == 'personal' ? 'active' : '' }}"><a href="{{action('ReceiveBalancePersonalController@index')}}"><i class="fa fa-star"></i>Personal</a></li>
                  @endif
                </ul>
              </li>
            @endif
          @endif

        @can('backup')
          <li class="treeview {{  in_array( $request->segment(1), ['backup']) ? 'active active-sub' : '' }}">
              <a href="{{action('BackUpController@index')}}"><i class="fa fa-dropbox"></i> <span>@lang('lang_v1.backup')</span>
              </a>
          </li>
        @endrole
        @if(auth()->user()->can('ikritem.view') || auth()->user()->can('ikdcategory.create') || auth()->user()->can('ikdlist.create'))
         @if(in_array('internal_kitchen', $enabled_modules))
          <li class="treeview {{ in_array($request->segment(1), ['internal_kitchen', 'dish_list']) ? 'active active-sub' : '' }}" id="tour_step4">
            <a href="#" id="tour_step4_menu"><i class="fa fa-fire"></i> <span>Internal Kitchen</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
            @if(auth()->user()->can('ikritem.view') || auth()->user()->can('ikritem.create'))
              <li class="{{ $request->segment(1) == 'raw_items' ? 'active' : '' }}"><a href="{{action('Restaurant\InternalKitchenController@index')}}"><i class="fa fa-star"></i> Raw Items</a></li>
            @endif
            @if(auth()->user()->can('ikdcategory.view') || auth()->user()->can('ikdcategory.create'))
              <li class="{{ $request->segment(1) == 'dish_category' ? 'active' : '' }}"><a href="{{action('Restaurant\DishCategoryController@index')}}"><i class="fa fa-star"></i> Dish Category</a></li>
            @endif
            @if(auth()->user()->can('ikdlist.view') || auth()->user()->can('ikdlist.create'))
              <li class="{{ $request->segment(1) == 'dish_list' ? 'active' : '' }}"><a href="{{action('Restaurant\DishListController@index')}}"><i class="fa fa-star"></i>Dish List</a></li>
            @endif
            </ul>
          </li>
       @endif
       @endif

         <!-- Waranty Management Menu-->
         @if(auth()->user()->can('warranty_customer.view') || auth()->user()->can('warranty_customer.create') || auth()->user()->can('warranty_supplier.view') || auth()->user()->can('warranty_supplier.create'))
        @if(in_array('warranty', $enabled_modules))
        <li class="treeview {{ in_array($request->segment(1), ['warranty_customer', 'warranty_supplier']) ? 'active active-sub' : '' }}">
            <a href="#">
                <i class="fa fa-users"></i>
                <span class="title">Warranty Management</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
              @can( 'warranty_customer.view' )
                <li class="{{ $request->segment(1) == 'warranty_customer' ? 'active active-sub' : '' }}">
                  <a href="{{action('CustomerWarrantyController@index')}}">
                      <i class="fa fa-user"></i>
                      <span class="title">
                          Customer Warranty
                      </span>
                  </a>
                </li>
              @endcan
              @can('warranty_supplier.view')
                <li class="{{ $request->segment(1) == 'warranty_supplier' ? 'active active-sub' : '' }}">
                  <a href="{{action('SupplierWarrantyController@index')}}">
                      <i class="fa fa-briefcase"></i>
                      <span class="title">
                       Supplier Warranty
                      </span>
                  </a>
                </li>
              @endcan
            </ul>
        </li>
        @endif
        @endif
        <!-- End Waranty Management Menu -->
        <!-- Call restaurant module if defined -->
        @if(in_array('tables', $enabled_modules) && in_array('service_staff', $enabled_modules) )
          @if(auth()->user()->can('crud_all_bookings') || auth()->user()->can('crud_own_bookings') )
          <li class="treeview {{ $request->segment(1) == 'bookings'? 'active active-sub' : '' }}">
              <a href="{{action('Restaurant\BookingController@index')}}"><i class="fa fa-calendar-check-o"></i> <span>@lang('restaurant.bookings')</span></a>
          </li>
          @endif
        @endif

        @if(in_array('kitchen', $enabled_modules))
          <li class="treeview {{ $request->segment(1) == 'modules' && $request->segment(2) == 'kitchen' ? 'active active-sub' : '' }}">
              <a href="{{action('Restaurant\KitchenController@index')}}"><i class="fa fa-fire"></i> <span>@lang('restaurant.kitchen')</span></a>
          </li>
        @endif

        @if(in_array('service_staff', $enabled_modules))
          <li class="treeview {{ $request->segment(1) == 'modules' && $request->segment(2) == 'orders' ? 'active active-sub' : '' }}">
              <a href="{{action('Restaurant\OrderController@index')}}"><i class="fa fa-list-alt"></i> <span>@lang('restaurant.orders')</span></a>
          </li>
        @endif

        @can('send_notifications')
          <li class="treeview {{  $request->segment(1) == 'notification-templates' ? 'active active-sub' : '' }}">
              <a href="{{action('NotificationTemplateController@index')}}"><i class="fa fa-envelope"></i> <span>@lang('lang_v1.notification_templates')</span>
              </a>
          </li>
        @endrole

        @if(auth()->user()->can('business_settings.access') || auth()->user()->can('barcode_settings.access') ||auth()->user()->can('invoice_settings.access') ||auth()->user()->can('tax_rate.view') ||auth()->user()->can('tax_rate.create'))
        <li class="treeview @if( in_array($request->segment(1), ['business', 'tax-rates', 'barcodes', 'invoice-schemes', 'business-location', 'invoice-layouts', 'printers', 'subscription']) || in_array($request->segment(2), ['tables', 'modifiers']) ) {{'active active-sub'}} @endif">
          <a href="#" id="tour_step2_menu"><i class="fa fa-cog"></i> <span>@lang('business.settings')</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu" id="tour_step3">
            @can('business_settings.access')
              <li class="{{ $request->segment(1) == 'business' ? 'active' : '' }}">
                <a href="{{action('BusinessController@getBusinessSettings')}}" id="tour_step2"><i class="fa fa-cogs"></i> @lang('business.business_settings')</a>
              </li>
              <li class="{{ $request->segment(1) == 'business-location' ? 'active' : '' }}" >
               {{-- aaaaaaaaaaaaaaaa --}}
               @role('Admin#9')
               <a href="{{action('BusinessLocationController@index')}}"><i class="fa fa-map-marker"></i> @lang('business.business_locations')</a>

           @endrole

              </li>
            @endcan
            @can('invoice_settings.access')
              <li class="@if( in_array($request->segment(1), ['invoice-schemes', 'invoice-layouts']) ) {{'active'}} @endif">
                <a href="{{action('InvoiceSchemeController@index')}}"><i class="fa fa-file"></i> <span>@lang('invoice.invoice_settings')</span></a>
              </li>
            @endcan

            {{-- @can('barcode_settings.access')
            <li class="{{ $request->segment(1) == 'barcodes' ? 'active' : '' }}">
              <a href="{{action('BarcodeController@index')}}"><i class="fa fa-barcode"></i> <span>@lang('barcode.barcode_settings')</span></a>
            </li>
            @endcan --}}

            <li class="{{ $request->segment(1) == 'printers' ? 'active' : '' }}">
              <a href="{{action('PrinterController@index')}}"><i class="fa fa-share-alt"></i> <span>@lang('printer.receipt_printers')</span></a>
            </li>

            @if(auth()->user()->can('tax_rate.view') || auth()->user()->can('tax_rate.create'))
              <li class="{{ $request->segment(1) == 'tax-rates' ? 'active' : '' }}">
                <a href="{{action('TaxRateController@index')}}"><i class="fa fa-bolt"></i> <span>@lang('tax_rate.tax_rates')</span></a>
              </li>
            @endif
            @if(in_array('tables', $enabled_modules))
               @can('business_settings.access')
                <li class="{{ $request->segment(1) == 'modules' && $request->segment(2) == 'tables' ? 'active' : '' }}">
                  <a href="{{action('Restaurant\TableController@index')}}"><i class="fa fa-table"></i> @lang('restaurant.tables')</a>
                </li>
              @endcan
            @endif
            {{-- @if(in_array('modifiers', $enabled_modules))
              @if(auth()->user()->can('product.view') || auth()->user()->can('product.create') )
                <li class="{{ $request->segment(1) == 'modules' && $request->segment(2) == 'modifiers' ? 'active' : '' }}">
                  <a href="{{action('Restaurant\ModifierSetsController@index')}}"><i class="fa fa-delicious"></i> @lang('restaurant.modifiers')</a>
                </li>
              @endif
            @endif --}}
            {{-- @if(Module::has('Superadmin'))
              @include('superadmin::layouts.partials.subscription')
            @endif --}}

          </ul>
        </li>
        @endif
        @can('account.access')
          @if(Module::has('Account') && in_array('account', $enabled_modules))
            @include('account::layouts.partials.sidebar')
          @endif
        @endcan

      </ul>
    </section>
  </aside>
