<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\Api\Auth\AuthController;
Route::get('/', function () {
    return "Can't display API";
});
Route::group(
    [
        'middleware' => 'api'
    ],
    function () {
        Route::prefix('auth')->group(
            function () {
                Route::post('login', 'Api\Auth\AuthController@login');
                Route::post('logout', 'Api\Auth\AuthController@logout');
                //Route::post('refresh', 'Api\Auth\AuthController@refresh');
                Route::post('me', 'Api\Auth\AuthController@me');
            }
        );
        Route::group(
            ['prefix' => 'application', 'middleware' => 'jwt'],
            function () {
                Route::get('setting/version', 'Api\Application\SettingController@version');
                Route::get('setting/logging', 'Api\Application\SettingController@logging');
                Route::post('setting/access', 'Api\Application\SettingController@access');
                Route::post('setting/user', 'Api\Application\SettingController@user');
                Route::post('setting/warehouse', 'Api\Application\SettingController@warehouse');
                Route::post('setting/workcenter', 'Api\Application\SettingController@workcenter');

                Route::post('log/error', 'Api\Application\LogController@error');
                Route::post('log/menu', 'Api\Application\LogController@menu');
                Route::post('log/menu_action', 'Api\Application\LogController@menuAction');

                Route::get('error', 'Api\Application\ApplicationController@error');
                Route::get('time', 'Api\Application\ApplicationController@serverTime');
                Route::post('value_increment', 'Api\Application\ApplicationController@autoIncrement');
                Route::get('variable', 'Api\Application\ApplicationController@globalVariabel');
            }
        );
        Route::group(
            ['prefix' => 'master', 'middleware' => 'jwt'],
            function () {
                //Bank
                Route::get('bank', 'Api\Master\BankController@index');

                //BOM
                Route::get('bom/{id}', 'Api\Master\BOMController@show');
                Route::get('bom/product/{id}', 'Api\Master\BOMController@showByProduct');
                Route::get('bom/workcenter/{id}', 'Api\Master\BOMController@showByWorkcenter');
                
                //city
                Route::get('city', 'Api\Master\CityController@index');
                Route::get('city/check', 'Api\Master\CityController@check');
                Route::get('city/{id}', 'Api\Master\CityController@show')->where('id', '[0-9]+');
                Route::post('city', 'Api\Master\CityController@create');
                Route::put('city', 'Api\Master\CityController@update');

                //Color Type
                Route::get('color_type', 'Api\Master\ColorTypeController@index');
                Route::get('color_type/check', 'Api\Master\ColorTypeController@check');
                Route::get('color_type/{id}', 'Api\Master\ColorTypeController@show')->where('id', '[0-9]+');
                Route::post('color_type', 'Api\Master\ColorTypeController@create');
                Route::put('color_type', 'Api\Master\ColorTypeController@update');
                Route::delete('color_type/{id}', 'Api\Master\ColorTypeController@destroy');

                //Company Type
                Route::get('company_type', 'Api\Master\CompanyTypeController@index');
                Route::get('company_type/check', 'Api\Master\CompanyTypeController@check');
                Route::get('company_type/{id}', 'Api\Master\CompanyTypeController@show')->where('id', '[0-9]+');
                Route::post('company_type', 'Api\Master\CompanyTypeController@create');
                Route::put('company_type', 'Api\Master\CompanyTypeController@update');

                //country
                Route::get('country', 'Api\Master\CountryController@index');
                Route::get('country/check', 'Api\Master\CountryController@check');
                Route::get('country/{id}', 'Api\Master\CountryController@show')->where('id', '[0-9]+');
                Route::post('country', 'Api\Master\CountryController@create');
                Route::put('country', 'Api\Master\CountryController@update');

                //Customer
                Route::get('customer', 'Api\Master\CustomerController@index');
                Route::get('customer/{id}', 'Api\Master\CustomerController@show')->where('id', '[0-9]+');
                Route::get('customer/retail_type/{id}', 'Api\Master\CustomerController@showByRetail');

                //Customer Group
                Route::get('customer_group', 'Api\Master\CustomerGroupController@index');
                Route::get('customer_group/check', 'Api\Master\CustomerGroupController@check');
                Route::get('customer_group/{id}', 'Api\Master\CustomerGroupController@show');
                Route::post('customer_group', 'Api\Master\CustomerGroupController@create');
                Route::put('customer_group', 'Api\Master\CustomerGroupController@update');
                Route::delete('customer_group/{id}', 'Api\Master\CustomerGroupController@destroy');

                //Customer Group Member
                Route::get('customer_group_member/{id}', 'Api\Master\CustomerGroupMemberController@show');
                Route::post('customer_group_member', 'Api\Master\CustomerGroupMemberController@create');
                Route::delete('customer_group_member/{id}', 'Api\Master\CustomerGroupMemberController@destroy');
                Route::get('customer_group_member/check', 'Api\Master\CustomerGroupMemberController@check');

                //Driving License
                Route::get('department', 'Api\Master\DepartmentController@index');

                //Driver
                Route::get('driver', 'Api\Master\DriverController@index');
                Route::get('driver/check', 'Api\Master\DriverController@check');
                Route::get('driver/{id}', 'Api\Master\DriverController@show')->where('id', '[0-9]+');
                Route::post('driver', 'Api\Master\DriverController@create');
                Route::put('driver', 'Api\Master\DriverController@update');

                //Driving License
                Route::get('driving_license', 'Api\Master\DrivingLicenseController@index');

                //Employee
                Route::get('employee', 'Api\Master\EmployeeController@index');
                Route::get('employee/check', 'Api\Master\EmployeeController@check');

                //Employment Status
                Route::get('employment_status', 'Api\Master\EmploymentStatusController@index');

                //Gender
                Route::get('gender', 'Api\Master\GenderController@index');

                //Health Insurance
                Route::get('health_insurance', 'Api\Master\HealthInsuranceController@index');


                //machine
                Route::get('machine', 'Api\Master\MachineController@index');
                Route::get('machine/check', 'Api\Master\MachineController@check');
                Route::get('machine/workcenter', 'Api\Master\MachineController@show_by_workcenter');
                Route::get('machine/{id}', 'Api\Master\MachineController@show');
                Route::post('machine', 'Api\Master\MachineController@create');
                Route::put('machine', 'Api\Master\MachineController@update');

                //menu
                Route::get('menu', 'Api\Master\MenuController@index');
                Route::get('menu/check', 'Api\Master\MenuController@check');
                Route::get('menu/{id}', 'Api\Master\MenuController@show');
                Route::post('menu', 'Api\Master\MenuController@create');
                Route::put('menu', 'Api\Master\MenuController@update');
                Route::delete('menu/{id}', 'Api\Master\MenuController@destroy');

                // //Mold
                // Route::get('mold/check','Api\Master\MoldController@check');
                // Route::get('mold/product_item/{id}','Api\Master\MoldController@showByProductItem');
                // Route::get('mold/workcenter/{id}','Api\Master\MoldController@index');
                // Route::get('mold/{id}','Api\Master\MoldController@show');
                // Route::post('mold','Api\Master\MoldController@create');

                //Mold Status
                Route::get('mold_status','Api\Master\MoldStatusController@index');

                //Position
                Route::get('position', 'Api\Master\PositionController@index');

                //Privilege
                Route::get('privilege', 'Api\Master\PrivilegeController@index');
                Route::post('privilege', 'Api\Master\PrivilegeController@create');
                Route::get('privilege/user', 'Api\Master\PrivilegeController@user');
                Route::get('privilege/user/{id}', 'Api\Master\PrivilegeController@showMenuByUser');
                Route::get('privilege/menu/{id}', 'Api\Master\PrivilegeController@showUserByMenu');
                Route::post('privilege/copy', 'Api\Master\PrivilegeController@copy');

                //Privilege Warehouse
                Route::get('privilege_warehouse', 'Api\Master\PrivilegeWarehouseController@index');
                Route::get('privilege_warehouse/check', 'Api\Master\PrivilegeWarehouseController@check');
                Route::get('privilege_warehouse/{id}', 'Api\Master\PrivilegeWarehouseController@show')->where('id', '[0-9]+');
                Route::post('privilege_warehouse', 'Api\Master\PrivilegeWarehouseController@create');
                Route::put('privilege_warehouse', 'Api\Master\PrivilegeWarehouseController@update');
                Route::delete('privilege_warehouse', 'Api\Master\PrivilegeWarehouseController@update');

                //Privilege Workcenter
                Route::get('privilege_workcenter', 'Api\Master\PrivilegeWorkcenterController@index');
                Route::get('privilege_workcenter/check', 'Api\Master\PrivilegeWorkcenterController@check');
                Route::get('privilege_workcenter/{id}', 'Api\Master\PrivilegeWorkcenterController@show')->where('id', '[0-9]+');
                Route::post('privilege_workcenter', 'Api\Master\PrivilegeWorkcenterController@create');
                Route::put('privilege_workcenter', 'Api\Master\PrivilegeWorkcenterController@update');
                Route::delete('privilege_workcenter', 'Api\Master\PrivilegeWorkcenterController@destroy');

                //Product
                Route::get('product/{id}', 'Api\Master\ProductController@show');
                Route::get('product/product_group/{id}', 'Api\Master\ProductController@showByProductGroup');
                Route::get('product/product_type/{id}', 'Api\Master\ProductController@showByProductType');

                //Product Appearance
                Route::get('product_appearance', 'Api\Master\ProductAppearanceController@index');
                Route::get('product_appearance/{id}', 'Api\Master\ProductAppearanceController@show');
                Route::post('product_appearance', 'Api\Master\ProductAppearanceController@create');
                Route::put('product_appearance', 'Api\Master\ProductAppearanceController@update');

                //Product Brand
                Route::get('product_brand', 'Api\Master\ProductBrandController@index');
                Route::get('product_brand/check', 'Api\Master\ProductBrandController@check');
                Route::get('product_brand/{id}', 'Api\Master\ProductBrandController@show')->where('id', '[0-9]+');
                Route::post('product_brand', 'Api\Master\ProductBrandController@create');
                Route::put('product_brand', 'Api\Master\ProductBrandController@update');

                //Product Customer
                Route::get('product_customer', 'Api\Master\ProductCustomerController@index');
                Route::get('product_customer/check', 'Api\Master\ProductCustomerController@check');
                Route::get('product_customer/{id}', 'Api\Master\ProductCustomerController@show')->where('id', '[0-9]+');
                Route::get('product_customer/customer/{id}', 'Api\Master\ProductCustomerController@showCustomer');
                Route::post('product_customer', 'Api\Master\ProductCustomerController@create');
                Route::put('product_customer', 'Api\Master\ProductCustomerController@update');
                Route::delete('product_customer/{id}', 'Api\Master\ProductCustomerController@destroy');

                //Product Design
                Route::get('product_design', 'Api\Master\ProductDesignController@index');
                Route::get('product_design/check', 'Api\Master\ProductDesignController@check');
                Route::get('product_design/{id}', 'Api\Master\ProductDesignController@show')->where('id', '[0-9]+');
                Route::post('product_design', 'Api\Master\ProductDesignController@create');
                Route::put('product_design', 'Api\Master\ProductDesignController@update');
                Route::delete('product_design/{id}', 'Api\Master\ProductDesignController@destroy');

                //Product Group
                Route::get('product_group', 'Api\Master\ProductGroupController@index');
                Route::get('product_group/check', 'Api\Master\ProductGroupController@check');
                Route::get('product_group/{id}', 'Api\Master\ProductGroupController@show')->where('id', '[0-9]+');
                Route::post('product_group', 'Api\Master\ProductGroupController@create');
                Route::put('product_group', 'Api\Master\ProductGroupController@update');

                //Product Item
                Route::get('product_item/kind/{id}', 'Api\Master\ProductItemController@index');
                Route::get('product_item/check', 'Api\Master\ProductItemController@check');
                Route::get('product_item/{id}', 'Api\Master\ProductItemController@show')->where('id', '[0-9]+');
                Route::post('product_item', 'Api\Master\ProductItemController@create');
                Route::put('product_item', 'Api\Master\ProductItemController@update');
                Route::delete('product_item/{id}', 'Api\Master\ProductItemController@destroy');

                //Product License Type
                Route::get('product_license_type', 'Api\Master\ProductItemController@index');

                //Product Series
                Route::get('product_series', 'Api\Master\ProductSeriesController@index');
                Route::get('product_series/check', 'Api\Master\ProductSeriesController@check');
                Route::get('product_series/{id}', 'Api\Master\ProductSeriesController@show');
                Route::post('product_series', 'Api\Master\ProductSeriesController@create');
                Route::put('product_series', 'Api\Master\ProductSeriesController@update');
                Route::delete('product_series/{id}', 'Api\Master\ProductSeriesController@destroy');

                //Product Type
                Route::get('product_type', 'Api\Master\ProductTypeController@index');
                Route::get('product_type/check', 'Api\Master\ProductTypeController@check');
                Route::get('product_type/{id}', 'Api\Master\ProductTypeController@show');
                Route::post('product_type', 'Api\Master\ProductTypeController@create');
                Route::put('product_type', 'Api\Master\ProductTypeController@update');
                
                //Product Workcenter
                Route::get('product_workcenter/check', 'Api\Master\ProductWorkcenterController@check');
                Route::get('product_workcenter/workcenter/{id}', 'Api\Master\ProductWorkcenterController@index');
                Route::get('product_workcenter/{id}', 'Api\Master\ProductWorkcenterController@show');
                Route::post('product_workcenter', 'Api\Master\ProductWorkcenterController@create');
                Route::put('product_workcenter', 'Api\Master\ProductWorkcenterController@update');
                Route::delete('product_workcenter/{id}', 'Api\Master\ProductWorkcenterController@destroy');

                //Religion
                Route::get('religion', 'Api\Master\ReligionController@index');

                //Retail Type
                Route::get('retail_type', 'Api\Master\RetailTypeController@index');
                Route::get('retail_type/check', 'Api\Master\RetailTypeController@check');
                Route::get('retail_type/{id}', 'Api\Master\RetailTypeController@show')->where('id', '[0-9]+');
                Route::post('retail_type', 'Api\Master\RetailTypeController@create');
                Route::put('retail_type', 'Api\Master\RetailTypeController@update');

                //Salary Status
                Route::get('salary_status', 'Api\Master\SalaryStatusController@index');

                //Section
                Route::get('section/department/{id}', 'Api\Master\SectionController@showByDepartment');

                //State
                Route::get('state', 'Api\Master\StateController@index');
                Route::get('state/check', 'Api\Master\StateController@check');
                Route::get('state/{id}', 'Api\Master\StateController@show')->where('id', '[0-9]+');
                Route::post('state', 'Api\Master\StateController@create');
                Route::put('state', 'Api\Master\StateController@update');

                //Unit
                Route::get('unit/section/{id}', 'Api\Master\UnitController@showBySection');

                //uom
                Route::get('uom', 'Api\Master\UOMController@index');
                Route::get('uom/check', 'Api\Master\UOMController@check');
                Route::get('uom/{id}', 'Api\Master\UOMController@show');
                Route::post('uom', 'Api\Master\UOMController@create');
                Route::put('uom', 'Api\Master\UOMController@update');

                //user
                Route::get('user', 'Api\Master\UserController@index');
                Route::get('user/{id}', 'Api\Master\UserController@index')->where('id', '[0-9]+');
                Route::get('user/check', 'Api\Master\UserController@check');
                Route::post('user', 'Api\Master\UserController@create');
                Route::put('user', 'Api\Master\UserController@update');
                Route::put('user/reset_password/{id}', 'Api\Master\UserController@resetPassword');
                Route::put('user/change_password', 'Api\Master\UserController@changePassword');

                //Vehicle
                Route::get('vehicle', 'Api\Master\VehicleController@index');
                Route::get('vehicle/check', 'Api\Master\VehicleController@check');
                Route::get('vehicle/{id}', 'Api\Master\VehicleController@show');
                Route::post('vehicle', 'Api\Master\VehicleController@create');
                Route::put('vehicle', 'Api\Master\VehicleController@update');

                //warehouse
                Route::get('warehouse/group/{id}', 'Api\Master\WarehouseController@showByWarehouseGroup');
                Route::get('warehouse/{id}', 'Api\Master\WarehouseController@show')->where('id', '[0-9]+');
                Route::post('warehouse', 'Api\Master\WarehouseController@create');
                Route::put('warehouse', 'Api\Master\WarehouseController@update');
                Route::delete('warehouse/{id}', 'Api\Master\WarehouseController@destroy');
                Route::get('warehouse/check', 'Api\Master\WarehouseController@check');

                //warehouse_group
                Route::get('warehouse_group', 'Api\Master\WarehouseGroupController@index');
                Route::get('warehouse_group/check', 'Api\Master\WarehouseGroupController@check');
                Route::get('warehouse_group/{id}', 'Api\Master\WarehouseGroupController@show');
                Route::post('warehouse_group', 'Api\Master\WarehouseGroupController@create');
                Route::put('warehouse_group', 'Api\Master\WarehouseGroupController@update');
                Route::delete('warehouse_group/{id}', 'Api\Master\WarehouseGroupController@destroy');

                //workcenter
                Route::get('workcenter', 'Api\Master\WorkcenterController@index');
                Route::get('workcenter/check', 'Api\Master\WorkcenterController@check');
                Route::get('workcenter/code/{id}', 'Api\Master\WorkcenterController@showByCode');
                Route::get('workcenter/{id}', 'Api\Master\WorkcenterController@show');
                Route::post('workcenter', 'Api\Master\WorkcenterController@create');
                Route::put('workcenter', 'Api\Master\WorkcenterController@update');
                Route::delete('workcenter/{id}', 'Api\Master\WorkcenterController@destroy');
                
                //Work Order Status
                Route::get('work_order_status', 'Api\Master\WorkOrderStatusController@index');

                Route::get('work_order_type', 'Api\Master\WorkOrderTypeController@index');
            }
        );
        Route::group(
            ['prefix' => 'sales', 'middleware' => 'jwt'],
            function () {
                Route::get('service_level/{id}', 'Api\Sales\ServiceLevel@show');

                Route::get('sales_order', 'Api\Sales\SalesOrderController@index');
                Route::get('sales_order/close/{id}', 'Api\Sales\SalesOrderController@close');
                Route::get('sales_order/outstanding/{id}', 'Api\Sales\SalesOrderController@outstanding');
                Route::get('sales_order/process/{id}', 'Api\Sales\SalesOrderController@process');
                Route::post('sales_order/release', 'Api\Sales\SalesOrderController@process');
                Route::get('sales_order/{id}', 'Api\Sales\SalesOrderController@show');

                Route::get('sales_order_detail', 'Api\Sales\SalesOrderController@detail');
                Route::get('sales_order_detail/full', 'Api\Sales\SalesOrderController@joined');
                Route::get('sales_order_detail/{id}', 'Api\Sales\SalesOrderController@detail_show');

                Route::get('so_tracker', 'Api\Sales\SalesOrderTrackerController@index');
            }
        );
        Route::group(
            ['prefix' => 'procurement', 'middleware' => 'jwt'],
            function () {
                Route::get('purchase_order', 'Api\Procurement\PurchaseOrderController@index');
                Route::get('purchase_order/outstanding', 'Api\Procurement\PurchaseOrderController@outstanding');
                Route::get('purchase_order/outstanding_detail', 'Api\Procurement\PurchaseOrderController@outstanding_detail');
                Route::get('purchase_order/full', 'Api\Procurement\PurchaseOrderController@joined');

                Route::get('purchase_order/{id}', 'Api\Procurement\PurchaseOrderController@show');
                Route::post('purchase_order', 'Api\Procurement\PurchaseOrderController@create');

                Route::get('purchase_request', 'Api\Procurement\PurchaseRequestController@index');
                Route::get('purchase_request/full', 'Api\Procurement\PurchaseRequestController@joined');
                Route::get('purchase_request/{id}', 'Api\Procurement\PurchaseRequestController@show');
            }
        );

        Route::group(
            ['prefix' => 'ppic', 'middleware' => 'jwt'],
            function () {
                Route::get('work_order', 'Api\PPIC\WorkOrderController@index');
                Route::get('work_order/workcenter/{id}', 'Api\PPIC\WorkOrderController@showByWorkcenter');
                Route::get('work_order/import/check_bom_product','Api\PPIC\WorkOrderController@showImportCheckBOMAndProduct');
                Route::get('work_order/import/check_product_workcenter','Api\PPIC\WorkOrderController@showImportCheckProductAndWorkcenter');
                Route::get('work_order/check', 'Api\PPIC\WorkOrderController@check');
                Route::get('work_order/{id}', 'Api\PPIC\WorkOrderController@show');
                Route::post('work_order', 'Api\PPIC\WorkOrderController@create');
                Route::put('work_order', 'Api\PPIC\WorkOrderController@update');
                Route::delete('work_order/{id}', 'Api\PPIC\WorkOrderController@destroy');

                
            }
        );

        Route::group(['prefix' => 'oem', 'middleware' => 'jwt'], function () {

            Route::get('delivery_order', 'Api\OEM\DeliveryOrderController@index');
            Route::get('delivery_order/check', 'Api\OEM\DeliveryOrderController@check');
            Route::get('delivery_order/full', 'Api\OEM\DeliveryOrderController@joined');
            Route::get('delivery_order/history', 'Api\OEM\DeliveryOrderController@history');
            Route::get('delivery_order/history/{id}', 'Api\OEM\DeliveryOrderController@show_history');
            Route::get('delivery_order/purchase_order/{id}', 'Api\OEM\DeliveryOrderController@show_by_po');
            Route::get('delivery_order/schedule', 'Api\OEM\DeliveryOrderController@schedule');
            Route::get('delivery_order/{id}', 'Api\OEM\DeliveryOrderController@show');
            Route::post('delivery_order', 'Api\OEM\DeliveryOrderController@create');
            Route::post('delivery_order/detail', 'Api\OEM\DeliveryOrderController@update');
            Route::delete('delivery_order/{id}', 'Api\OEM\DeliveryOrderController@destroy');

            Route::get('delivery_schedule', 'Api\OEM\DeliveryScheduleController@index');
            Route::get('delivery_schedule/show', 'Api\OEM\DeliveryScheduleController@show');
            Route::get('delivery_schedule/check', 'Api\OEM\DeliveryScheduleController@check');
            Route::post('delivery_schedule', 'Api\OEM\DeliveryScheduleController@create');
            Route::put('delivery_schedule', 'Api\OEM\DeliveryScheduleController@update');
            Route::delete('delivery_schedule', 'Api\OEM\DeliveryScheduleController@destroy');

            Route::get('material_customer', 'Api\OEM\MaterialCustomerController@index');
            Route::get('material_customer/full', 'Api\OEM\MaterialCustomerController@joined');
            Route::get('material_customer/material/{id}', 'Api\OEM\MaterialCustomerController@show_material');
            Route::get('material_customer/customer/{id}', 'Api\OEM\MaterialCustomerController@show_customer');
            Route::get('material_customer/product_customer/{id}', 'Api\OEM\MaterialCustomerController@show_product_customer');
            Route::get('material_customer/{id}', 'Api\OEM\MaterialCustomerController@show');
            Route::post('material_customer', 'Api\OEM\MaterialCustomerController@create');
            Route::post('material_customer/detail', 'Api\OEM\MaterialCustomerController@update');

            Route::get('material_incoming', 'Api\OEM\MaterialIncomingController@index');
            Route::get('material_incoming/full', 'Api\OEM\MaterialIncomingController@joined');
            Route::get('material_incoming/{id}', 'Api\OEM\MaterialIncomingController@show');
            Route::post('material_incoming', 'Api\OEM\MaterialIncomingController@create');
            Route::post('material_incoming/detail', 'Api\OEM\MaterialIncomingController@update');
            Route::delete('material_incoming/{id}', 'Api\OEM\MaterialIncomingController@destroy');

            Route::get('purchase_order', 'Api\OEM\PurchaseOrderController@index');
            Route::get('purchase_order/check', 'Api\OEM\PurchaseOrderController@check');
            Route::get('purchase_order/full', 'Api\OEM\PurchaseOrderController@joined');
            Route::get('purchase_order/outstanding/lookup', 'Api\OEM\PurchaseOrderController@outstanding_lookup');
            Route::get('purchase_order/outstanding/schedule', 'Api\OEM\PurchaseOrderController@outstanding_schedule');
            Route::get('purchase_order/outstanding/validating', 'Api\OEM\PurchaseOrderController@outstanding_validating');
            Route::get('purchase_order/remaining/{id}', 'Api\OEM\PurchaseOrderController@remaining');
            Route::get('purchase_order/{id}', 'Api\OEM\PurchaseOrderController@show');
            Route::post('purchase_order', 'Api\OEM\PurchaseOrderController@create');
            Route::post('purchase_order/detail', 'Api\OEM\PurchaseOrderController@update');
            Route::delete('purchase_order', 'Api\OEM\PurchaseOrderController@destroy_po');
            Route::delete('purchase_order/{id}', 'Api\OEM\PurchaseOrderController@destroy');
            
           
            
        });
    }
);
