<?php

namespace Modules\Woocommerce\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\Business;
use Modules\Woocommerce\Utils\WoocommerceUtil;

use DB;

class WooCommerceSyncOrder extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'pos:WooCommerceSyncOrder {business_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs all orders from Woocommerce App to POS';

    /**
     * All Utils instance.
     *
     */
    protected $woocommerceUtil;

    /**
     * Create a new command instance.
     *
     * @param WoocommerceUtil $woocommerceUtil
     * @return void
     */
    public function __construct(WoocommerceUtil $woocommerceUtil)
    {
        parent::__construct();

        $this->woocommerceUtil = $woocommerceUtil;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {

            DB::beginTransaction();
            $business_id = $this->argument('business_id');

            $business = Business::findOrFail($business_id);
            $user_id = $business->owner_id;

            //Set timezone to business timezone
            $timezone =$business->time_zone;
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
           
            $this->woocommerceUtil->syncOrders($business_id, $user_id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            print_r("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
        }
        
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['business_id', InputArgument::REQUIRED, 'ID of the business'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    // protected function getOptions()
    // {
    //     return [
    //         ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
    //     ];
    // }
}
