<?php

use Illuminate\Http\Request;
//use App\Http\Controllers\Api\Auth\AuthController;

Route::group(
    [
        'middleware' => 'api'
    ],
    function () {
        Route::prefix('auth')->group(
            function ($router) {
                Route::post('login', 'Api\Auth\AuthController@login');
                Route::post('logout', 'Api\Auth\AuthController@logout');
                //Route::post('refresh', 'Api\Auth\AuthController@refresh');
                Route::post('me', 'Api\Auth\AuthController@me');
            }
        );
        Route::prefix('application')->group(
            function () {
                Route::get('setting/version', 'Api\Application\SettingController@version')->middleware('jwt');
                Route::get('setting/logging', 'Api\Application\SettingController@logging')->middleware('jwt');
                Route::post('setting/access', 'Api\Application\SettingController@access')->middleware('jwt');
                Route::post('setting/user', 'Api\Application\SettingController@user')->middleware('jwt');
                Route::post('setting/warehouse', 'Api\Application\SettingController@warehouse')->middleware('jwt');
                Route::post('setting/workcenter', 'Api\Application\SettingController@workcenter')->middleware('jwt');

                Route::post('log/error', 'Api\Application\LogController@error')->middleware('jwt');
                Route::post('log/menu', 'Api\Application\LogController@menu')->middleware('jwt');
                Route::post('log/menu_action', 'Api\Application\LogController@menuAction')->middleware('jwt');

                Route::get('error', 'Api\Application\ApplicationController@error')->middleware('jwt');
                Route::get('time', 'Api\Application\ApplicationController@serverTime')->middleware('jwt');
                Route::post('value_increment', 'Api\Application\ApplicationController@autoIncrement')->middleware('jwt');
                Route::get('variable', 'Api\Application\ApplicationController@globalVariabel')->middleware('jwt');
            }
        );
        Route::prefix('master')->group(
            function () {
                //BOM
                Route::get('bom/{id}', 'Api\Master\BOMController@show')->middleware('jwt');
                Route::get('bom/workcenter/{id}', 'Api\Master\BOMController@showByWorkcenter')->middleware('jwt');

                //city
                Route::get('city', 'Api\Master\CityController@index')->middleware('jwt');
                Route::get('city/check', 'Api\Master\CityController@check')->middleware('jwt');
                Route::get('city/{id}', 'Api\Master\CityController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::post('city', 'Api\Master\CityController@create')->middleware('jwt');
                Route::put('city', 'Api\Master\CityController@update')->middleware('jwt');


                //Color Type
                Route::get('color_type', 'Api\Master\ColorTypeController@index')->middleware('jwt');
                Route::get('color_type/check', 'Api\Master\ColorTypeController@check')->middleware('jwt');
                Route::get('color_type/{id}', 'Api\Master\ColorTypeController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::post('color_type', 'Api\Master\ColorTypeController@create')->middleware('jwt');
                Route::put('color_type', 'Api\Master\ColorTypeController@update')->middleware('jwt');
                Route::delete('color_type/{id}', 'Api\Master\ColorTypeController@destroy')->middleware('jwt');

                //Company Type
                Route::get('company_type', 'Api\Master\CompanyTypeController@index')->middleware('jwt');
                Route::get('company_type/check', 'Api\Master\CompanyTypeController@check')->middleware('jwt');
                Route::get('company_type/{id}', 'Api\Master\CompanyTypeController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::post('company_type', 'Api\Master\CompanyTypeController@create')->middleware('jwt');
                Route::put('company_type', 'Api\Master\CompanyTypeController@update')->middleware('jwt');

                //country
                Route::get('country', 'Api\Master\CountryController@index')->middleware('jwt');
                Route::get('country/check', 'Api\Master\CountryController@check')->middleware('jwt');
                Route::get('country/{id}', 'Api\Master\CountryController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::post('country', 'Api\Master\CountryController@create')->middleware('jwt');
                Route::put('country', 'Api\Master\CountryController@update')->middleware('jwt');

                //Customer
                Route::get('customer', 'Api\Master\CustomerController@index')->middleware('jwt');
                Route::get('customer/{id}', 'Api\Master\CustomerController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::get('customer/retail_type/{id}', 'Api\Master\CustomerController@showByRetail')->middleware('jwt');

                //Customer Group
                Route::get('customer_group', 'Api\Master\CustomerGroupController@index')->middleware('jwt');
                Route::get('customer_group/check', 'Api\Master\CustomerGroupController@check')->middleware('jwt');
                Route::get('customer_group/{id}', 'Api\Master\CustomerGroupController@show')->middleware('jwt');
                Route::post('customer_group', 'Api\Master\CustomerGroupController@create')->middleware('jwt');
                Route::put('customer_group', 'Api\Master\CustomerGroupController@update')->middleware('jwt');
                Route::delete('customer_group/{id}', 'Api\Master\CustomerGroupController@destroy')->middleware('jwt');

                //Customer Group Member
                Route::get('customer_group_member/{id}', 'Api\Master\CustomerGroupMemberController@showr')->middleware('jwt');
                Route::post('customer_group_member', 'Api\Master\CustomerGroupMemberController@create')->middleware('jwt');
                Route::delete('customer_group_member/{id}', 'Api\Master\CustomerGroupMemberController@destroy')->middleware('jwt');
                Route::get('customer_group_member/check', 'Api\Master\CustomerGroupMemberController@check')->middleware('jwt');

                //Driver
                Route::get('driver', 'Api\Master\DriverController@index')->middleware('jwt');
                Route::get('driver/check', 'Api\Master\DriverController@check')->middleware('jwt');
                Route::get('driver/{id}', 'Api\Master\DriverController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::post('driver', 'Api\Master\DriverController@create')->middleware('jwt');
                Route::put('driver', 'Api\Master\DriverController@update')->middleware('jwt');

                //Employee
                Route::get('employee', 'Api\Master\EmployeeController@index')->middleware('jwt');

                // //Extension
                // Route::get('extension', 'Api\Master\ExtensionController@index')->middleware('jwt');
                // Route::get('extension/{id}', 'Api\Master\ExtensionController@show')->middleware('jwt');
                // Route::post('extension', 'Api\Master\ExtensionController@create')->middleware('jwt');
                // Route::put('extension', 'Api\Master\ExtensionController@update')->middleware('jwt');
                // Route::delete('extension/{id}', 'Api\Master\ExtensionController@destroy')->middleware('jwt');
                // Route::get('extension/check', 'Api\Master\ExtensionController@check')->middleware('jwt');

                //menu
                Route::get('menu', 'Api\Master\MenuController@index')->middleware('jwt');
                Route::get('menu/check', 'Api\Master\MenuController@check')->middleware('jwt');
                Route::get('menu/{id}', 'Api\Master\MenuController@show')->middleware('jwt');
                Route::post('menu', 'Api\Master\MenuController@create')->middleware('jwt');
                Route::put('menu', 'Api\Master\MenuController@update')->middleware('jwt');
                Route::delete('menu/{id}', 'Api\Master\MenuController@destroy')->middleware('jwt');

                //Privilege
                Route::get('privilege', 'Api\Master\PrivilegeController@index')->middleware('jwt');
                Route::post('privilege', 'Api\Master\PrivilegeController@create')->middleware('jwt');
                Route::get('privilege/user', 'Api\Master\PrivilegeController@user')->middleware('jwt');
                Route::get('privilege/user/{id}', 'Api\Master\PrivilegeController@showMenuByUser')->middleware('jwt');
                Route::get('privilege/menu/{id}', 'Api\Master\PrivilegeController@showUserByMenu')->middleware('jwt');
                Route::post('privilege/copy', 'Api\Master\PrivilegeController@copy')->middleware('jwt');

                //Privilege Warehouse
                Route::get('privilege_warehouse', 'Api\Master\PrivilegeWarehouseController@index')->middleware('jwt');
                Route::get('privilege_warehouse/check', 'Api\Master\PrivilegeWarehouseController@check')->middleware('jwt');
                Route::get('privilege_warehouse/{id}', 'Api\Master\PrivilegeWarehouseController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::post('privilege_warehouse', 'Api\Master\PrivilegeWarehouseController@create')->middleware('jwt');
                Route::put('privilege_warehouse', 'Api\Master\PrivilegeWarehouseController@update')->middleware('jwt');
                Route::delete('privilege_warehouse', 'Api\Master\PrivilegeWarehouseController@update')->middleware('jwt');

                //Privilege Workcenter
                Route::get('privilege_workcenter', 'Api\Master\PrivilegeWorkcenterController@index')->middleware('jwt');
                Route::get('privilege_workcenter/check', 'Api\Master\PrivilegeWorkcenterController@check')->middleware('jwt');
                Route::get('privilege_workcenter/{id}', 'Api\Master\PrivilegeWorkcenterController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::post('privilege_workcenter', 'Api\Master\PrivilegeWorkcenterController@create')->middleware('jwt');
                Route::put('privilege_workcenter', 'Api\Master\PrivilegeWorkcenterController@update')->middleware('jwt');
                Route::delete('privilege_workcenter', 'Api\Master\PrivilegeWorkcenterController@destroy')->middleware('jwt');

                //Product
                Route::get('product/{id}', 'Api\Master\ProductController@show')->middleware('jwt');
                Route::get('product/product_group/{id}', 'Api\Master\ProductController@showByProductGroup')->middleware('jwt');
                Route::get('product/product_type/{id}', 'Api\Master\ProductController@showByProductType')->middleware('jwt');

                //Product Appearance
                Route::get('product_appearance', 'Api\Master\ProductAppearanceController@index')->middleware('jwt');
                Route::get('product_appearance/{id}', 'Api\Master\ProductAppearanceController@show')->middleware('jwt');
                Route::post('product_appearance', 'Api\Master\ProductAppearanceController@create')->middleware('jwt');
                Route::put('product_appearance', 'Api\Master\ProductAppearanceController@update')->middleware('jwt');

                //Product Brand
                Route::get('product_brand', 'Api\Master\ProductBrandController@index')->middleware('jwt');
                Route::get('product_brand/check', 'Api\Master\ProductBrandController@check')->middleware('jwt');
                Route::get('product_brand/{id}', 'Api\Master\ProductBrandController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::post('product_brand', 'Api\Master\ProductBrandController@create')->middleware('jwt');
                Route::put('product_brand', 'Api\Master\ProductBrandController@update')->middleware('jwt');

                //Product Customer
                Route::get('product_customer', 'Api\Master\ProductCustomerController@index')->middleware('jwt');
                Route::get('product_customer/check', 'Api\Master\ProductCustomerController@check')->middleware('jwt');
                Route::get('product_customer/{id}', 'Api\Master\ProductCustomerController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::get('product_customer/customer/{id}', 'Api\Master\ProductCustomerController@showCustomer')->middleware('jwt');
                Route::post('product_customer', 'Api\Master\ProductCustomerController@create')->middleware('jwt');
                Route::put('product_customer', 'Api\Master\ProductCustomerController@update')->middleware('jwt');
                Route::delete('product_customer/{id}', 'Api\Master\ProductCustomerController@destroy')->middleware('jwt');

                //Product Design
                Route::get('product_design', 'Api\Master\ProductDesignController@index')->middleware('jwt');
                Route::get('product_design/check', 'Api\Master\ProductDesignController@check')->middleware('jwt');
                Route::get('product_design/{id}', 'Api\Master\ProductDesignController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::post('product_design', 'Api\Master\ProductDesignController@create')->middleware('jwt');
                Route::put('product_design', 'Api\Master\ProductDesignController@update')->middleware('jwt');
                Route::delete('product_design/{id}', 'Api\Master\ProductDesignController@destroy')->middleware('jwt');

                //Product Group
                Route::get('product_group', 'Api\Master\ProductGroupController@index')->middleware('jwt');
                Route::get('product_group/check', 'Api\Master\ProductGroupController@check')->middleware('jwt');
                Route::get('product_group/{id}', 'Api\Master\ProductGroupController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::post('product_group', 'Api\Master\ProductGroupController@create')->middleware('jwt');
                Route::put('product_group', 'Api\Master\ProductGroupController@update')->middleware('jwt');

                //Product Item
                Route::get('product_item/kind/{id}', 'Api\Master\ProductItemController@index')->middleware('jwt');
                Route::get('product_item/check', 'Api\Master\ProductItemController@check')->middleware('jwt');
                Route::get('product_item/{id}', 'Api\Master\ProductItemController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::post('product_item', 'Api\Master\ProductItemController@create')->middleware('jwt');
                Route::put('product_item', 'Api\Master\ProductItemController@update')->middleware('jwt');
                Route::delete('product_item/{id}', 'Api\Master\ProductItemController@destroy')->middleware('jwt');

                //Product License Type
                Route::get('product_license_type', 'Api\Master\ProductItemController@index')->middleware('jwt');

                //Product Series
                Route::get('product_series', 'Api\Master\ProductSeriesController@index')->middleware('jwt');
                Route::get('product_series/check', 'Api\Master\ProductSeriesController@check')->middleware('jwt');
                Route::get('product_series/{id}', 'Api\Master\ProductSeriesController@show')->middleware('jwt');
                Route::post('product_series', 'Api\Master\ProductSeriesController@create')->middleware('jwt');
                Route::put('product_series', 'Api\Master\ProductSeriesController@update')->middleware('jwt');
                Route::delete('product_series/{id}', 'Api\Master\ProductSeriesController@destroy')->middleware('jwt');

                //Product Type
                Route::get('product_type', 'Api\Master\ProductTypeController@index')->middleware('jwt');
                Route::get('product_type/{id}', 'Api\Master\ProductTypeController@show')->middleware('jwt');
                Route::post('product_type', 'Api\Master\ProductTypeController@create')->middleware('jwt');
                Route::put('product_type', 'Api\Master\ProductTypeController@update')->middleware('jwt');
                Route::get('product_type/check', 'Api\Master\ProductTypeController@check')->middleware('jwt');

                //Product Workcenter
                Route::get('product_workcenter/{id}', 'Api\Master\ProductWorkcenterController@show')->middleware('jwt');

                //Retail Type
                Route::get('retail_type', 'Api\Master\RetailTypeController@index')->middleware('jwt');
                Route::get('retail_type/check', 'Api\Master\RetailTypeController@check')->middleware('jwt');
                Route::get('retail_type/{id}', 'Api\Master\RetailTypeController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::post('retail_type', 'Api\Master\RetailTypeController@create')->middleware('jwt');
                Route::put('retail_type', 'Api\Master\RetailTypeController@update')->middleware('jwt');

                //State
                Route::get('state', 'Api\Master\StateController@index')->middleware('jwt');
                Route::get('state/check', 'Api\Master\StateController@check')->middleware('jwt');
                Route::get('state/{id}', 'Api\Master\StateController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::post('state', 'Api\Master\StateController@create')->middleware('jwt');
                Route::put('state', 'Api\Master\StateController@update')->middleware('jwt');

                //uom
                Route::get('uom', 'Api\Master\UOMController@index')->middleware('jwt');
                Route::get('uom/check', 'Api\Master\UOMController@check')->middleware('jwt');
                Route::get('uom/{id}', 'Api\Master\UOMController@show')->middleware('jwt');
                Route::post('uom', 'Api\Master\UOMController@create')->middleware('jwt');
                Route::put('uom', 'Api\Master\UOMController@update')->middleware('jwt');

                //user
                Route::get('user', 'Api\Master\UserController@index')->middleware('jwt');
                Route::get('user/{id}', 'Api\Master\UserController@index')->where('id', '[0-9]+')->middleware('jwt');
                Route::get('user/check', 'Api\Master\UserController@check')->middleware('jwt');
                Route::post('user', 'Api\Master\UserController@create')->middleware('jwt');
                Route::put('user', 'Api\Master\UserController@update')->middleware('jwt');
                Route::put('user/reset_password/{id}', 'Api\Master\UserController@resetPassword')->middleware('jwt');
                Route::put('user/change_password', 'Api\Master\UserController@changePassword')->middleware('jwt');

                //Vehicle
                Route::get('vehicle', 'Api\Master\VehicleController@index')->middleware('jwt');
                Route::get('vehicle/check', 'Api\Master\VehicleController@check')->middleware('jwt');
                Route::get('vehicle/{id}', 'Api\Master\VehicleController@show')->middleware('jwt');
                Route::post('vehicle', 'Api\Master\VehicleController@create')->middleware('jwt');
                Route::put('vehicle', 'Api\Master\VehicleController@update')->middleware('jwt');

                //warehouse
                Route::get('warehouse/group/{id}', 'Api\Master\WarehouseController@showByWarehouseGroup')->middleware('jwt');
                Route::get('warehouse/{id}', 'Api\Master\WarehouseController@show')->where('id', '[0-9]+')->middleware('jwt');
                Route::post('warehouse', 'Api\Master\WarehouseController@create')->middleware('jwt');
                Route::put('warehouse', 'Api\Master\WarehouseController@update')->middleware('jwt');
                Route::delete('warehouse/{id}', 'Api\Master\WarehouseController@destroy')->middleware('jwt');
                Route::get('warehouse/check', 'Api\Master\WarehouseController@check')->middleware('jwt');

                //warehouse_group
                Route::get('warehouse_group', 'Api\Master\WarehouseGroupController@index')->middleware('jwt');
                Route::get('warehouse_group/check', 'Api\Master\WarehouseGroupController@check')->middleware('jwt');
                Route::get('warehouse_group/{id}', 'Api\Master\WarehouseGroupController@show')->middleware('jwt');
                Route::post('warehouse_group', 'Api\Master\WarehouseGroupController@create')->middleware('jwt');
                Route::put('warehouse_group', 'Api\Master\WarehouseGroupController@update')->middleware('jwt');
                Route::delete('warehouse_group/{id}', 'Api\Master\WarehouseGroupController@destroy')->middleware('jwt');

                //workcenter
                Route::get('workcenter', 'Api\Master\WorkcenterController@index')->middleware('jwt');
                Route::get('workcenter/check', 'Api\Master\WorkcenterController@check')->middleware('jwt');
                Route::get('workcenter/{id}', 'Api\Master\WorkcenterController@show')->middleware('jwt');
                Route::post('workcenter', 'Api\Master\WorkcenterController@create')->middleware('jwt');
                Route::put('workcenter', 'Api\Master\WorkcenterController@update')->middleware('jwt');
                Route::delete('workcenter/{id}', 'Api\Master\WorkcenterController@destroy')->middleware('jwt');
            }
        );
        Route::prefix('sales')->group(
            function () {
                Route::get('service_level/{id}', 'Api\Sales\ServiceLevel@show')->middleware('jwt');

                Route::get('sales_order', 'Api\Sales\SalesOrderController@index')->middleware('jwt');
                Route::get('sales_order/close/{id}', 'Api\Sales\SalesOrderController@close')->middleware('jwt');
                Route::get('sales_order/outstanding/{id}', 'Api\Sales\SalesOrderController@outstanding')->middleware('jwt');
                Route::get('sales_order/process/{id}', 'Api\Sales\SalesOrderController@process')->middleware('jwt');
                Route::post('sales_order/release', 'Api\Sales\SalesOrderController@process')->middleware('jwt');
                Route::get('sales_order/{id}', 'Api\Sales\SalesOrderController@show')->middleware('jwt');

                Route::get('sales_order_detail', 'Api\Sales\SalesOrderController@detail')->middleware('jwt');
                Route::get('sales_order_detail/full', 'Api\Sales\SalesOrderController@joined')->middleware('jwt');
                Route::get('sales_order_detail/{id}', 'Api\Sales\SalesOrderController@detail_show')->middleware('jwt');

                Route::get('so_tracker', 'Api\Sales\SalesOrderTrackerController@index')->middleware('jwt');
            }
        );
        Route::prefix('procurement')->group(
            function () {
                Route::get('purchase_order', 'Api\Procurement\PurchaseOrderController@index')->middleware('jwt');
                Route::get('purchase_order/outstanding', 'Api\Procurement\PurchaseOrderController@outstanding')->middleware('jwt');
                Route::get('purchase_order/outstanding_detail', 'Api\Procurement\PurchaseOrderController@outstanding_detail')->middleware('jwt');
                Route::get('purchase_order/full', 'Api\Procurement\PurchaseOrderController@joined')->middleware('jwt');

                Route::get('purchase_order/{id}', 'Api\Procurement\PurchaseOrderController@show')->middleware('jwt');
                Route::post('purchase_order', 'Api\Procurement\PurchaseOrderController@create')->middleware('jwt');

                Route::get('purchase_request', 'Api\Procurement\PurchaseRequestController@index')->middleware('jwt');
                Route::get('purchase_request/full', 'Api\Procurement\PurchaseRequestController@joined')->middleware('jwt');
                Route::get('purchase_request/{id}', 'Api\Procurement\PurchaseRequestController@show')->middleware('jwt');
            }
        );
        Route::prefix('oem')->group(
            function () {
                Route::get('deliveryorder/detail/{id}', 'Api\OEM\DeliveryOrderController@show_detail'); //->middleware('jwt');
                Route::get('testmodel', 'TestController@modeltest');
                Route::get('material_customer','Api\OEM\MaterialCustomerController@index')->middleware('jwt');
                Route::get('material_customer/full','Api\OEM\MaterialCustomerController@joined')->middleware('jwt');
		Route::get('material_customer/customer/{id}','Api\OEM\MaterialCustomerController@show_customer')->middleware('jwt');
                Route::get('material_customer/material/{id}','Api\OEM\MaterialCustomerController@show_material')->middleware('jwt');
                Route::get('material_customer/product_customer/{id}','Api\OEM\MaterialCustomerController@show_product_customer')->middleware('jwt');
                Route::get('material_customer/{id}','Api\OEM\MaterialCustomerController@show')->middleware('jwt');
                Route::post('material_customer','Api\OEM\MaterialCustomerController@create')->middleware('jwt');
                Route::post('material_customer/detail','Api\OEM\MaterialCustomerController@update')->middleware('jwt');
            }
        );
    }
);

// Route::fallback(function () {
//     return response()->json(['error' => 'Resource not found.'], 404);
// })->name('fallback');
