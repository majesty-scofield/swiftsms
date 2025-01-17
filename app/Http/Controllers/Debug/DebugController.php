<?php

    namespace App\Http\Controllers\Debug;

    use App\Http\Controllers\Controller;
    use App\Models\PaymentMethods;
    use Illuminate\Support\Facades\DB;

    class DebugController extends Controller
    {
        public function index()
        {
            DB::table('reports')->update(['sms_count' => DB::raw('cost')]);

            return redirect()->route('user.home');
        }

        public function removeJobs()
        {
            DB::table('job_monitors')->truncate();
            DB::table('job_batches')->truncate();
            DB::table('jobs')->truncate();
            DB::table('import_job_histories')->truncate();
            DB::table('failed_jobs')->truncate();

            return 'Job cleared successfully';
        }

        public function addGateways()
        {
            $check_exist = PaymentMethods::where('type', 'myFatoorah')->first();
            if ( ! $check_exist) {
                $data = PaymentMethods::create(

                    [
                        'name'    => PaymentMethods::TYPE_MYFATOORAH,
                        'type'    => PaymentMethods::TYPE_MYFATOORAH,
                        'options' => json_encode([
                            'api_token'        => 'rLtt6JWvbUHDDhsZnfpAhpYk4dxYDQkbcPTyGaKp2TYqQgG7FGZ5Th_WD53Oq8Ebz6A53njUoo1w3pjU1D4vs_ZMqFiz_j0urb_BH9Oq9VZoKFoJEDAbRZepGcQanImyYrry7Kt6MnMdgfG5jn4HngWoRdKduNNyP4kzcp3mRv7x00ahkm9LAK7ZRieg7k1PDAnBIOG3EyVSJ5kK4WLMvYr7sCwHbHcu4A5WwelxYK0GMJy37bNAarSJDFQsJ2ZvJjvMDmfWwDVFEVe_5tOomfVNt6bOg9mexbGjMrnHBnKnZR1vQbBtQieDlQepzTZMuQrSuKn-t5XZM7V6fCW7oP-uXGX-sMOajeX65JOf6XVpk29DP6ro8WTAflCDANC193yof8-f5_EYY-3hXhJj7RBXmizDpneEQDSaSz5sFk0sV5qPcARJ9zGG73vuGFyenjPPmtDtXtpx35A-BVcOSBYVIWe9kndG3nclfefjKEuZ3m4jL9Gg1h2JBvmXSMYiZtp9MR5I6pvbvylU_PP5xJFSjVTIz7IQSjcVGO41npnwIxRXNRxFOdIUHn0tjQ-7LwvEcTXyPsHXcMD8WtgBh-wxR8aKX7WPSsT1O8d8reb2aR7K3rkV3K82K_0OgawImEpwSvp9MNKynEAJQS6ZHe_J_l77652xwPNxMRTMASk1ZsJL',
                            'country_iso_code' => 'KWT',
                            'environment'      => 'sandbox',
                        ]),
                        'status'  => false,
                    ],

                );
                if ($data) {
                    return redirect()->route('admin.payment-gateways.show', $data->uid)->with([
                        'status'  => 'success',
                        'message' => 'Gateway was successfully Added',
                    ]);
                }

                return redirect()->route('login')->with([
                    'status'  => 'error',
                    'message' => __('locale.exceptions.something_went_wrong'),
                ]);

            }
        }

    }
