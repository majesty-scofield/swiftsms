<?php

    namespace App\Http\Controllers\Admin;

    use App\Http\Requests\PhoneNumbers\StoreNumber;
    use App\Http\Requests\PhoneNumbers\UpdateNumber;
    use App\Library\Tool;
    use App\Models\Currency;
    use App\Models\PhoneNumbers;
    use App\Models\User;
    use App\Repositories\Contracts\PhoneNumberRepository;
    use Generator;
    use Illuminate\Auth\Access\AuthorizationException;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\View\Factory;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    use Illuminate\View\View;
    use JetBrains\PhpStorm\NoReturn;
    use OpenSpout\Common\Exception\InvalidArgumentException;
    use OpenSpout\Common\Exception\IOException;
    use OpenSpout\Common\Exception\UnsupportedTypeException;
    use OpenSpout\Writer\Exception\WriterNotOpenedException;
    use Rap2hpoutre\FastExcel\FastExcel;
    use Symfony\Component\HttpFoundation\BinaryFileResponse;

    class PhoneNumberController extends AdminBaseController
    {

        protected PhoneNumberRepository $numbers;


        /**
         * PhoneNumberController constructor.
         *
         * @param PhoneNumberRepository $numbers
         */

        public function __construct(PhoneNumberRepository $numbers)
        {
            $this->numbers = $numbers;
        }

        /**
         * @return Application|Factory|View
         * @throws AuthorizationException
         */

        public function index(): Factory|View|Application
        {

            $this->authorize('view phone_numbers');

            $breadcrumbs = [
                ['link' => url(config('app.admin_path') . "/dashboard"), 'name' => __('locale.menu.Dashboard')],
                ['link' => url(config('app.admin_path') . "/dashboard"), 'name' => __('locale.menu.Sending')],
                ['name' => __('locale.menu.Phone Numbers')],
            ];

            return view('admin.PhoneNumbers.index', compact('breadcrumbs'));
        }


        /**
         * @param Request $request
         *
         * @return void
         * @throws AuthorizationException
         */
        #[NoReturn] public function search(Request $request): void
        {

            $this->authorize('view phone_numbers');

            $columns = [
                0 => 'responsive_id',
                1 => 'uid',
                2 => 'uid',
                3 => 'number',
                4 => 'user_id',
                5 => 'price',
                6 => 'status',
                7 => 'capabilities',
                8 => 'action',
            ];

            $totalData = PhoneNumbers::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir   = $request->input('order.0.dir');

            if (empty($request->input('search.value'))) {
                $numbers = PhoneNumbers::offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            } else {
                $search = $request->input('search.value');

                $numbers = PhoneNumbers::whereLike(['uid', 'number', 'price', 'status', 'user.first_name', 'user.last_name'], $search)
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();

                $totalFiltered = PhoneNumbers::whereLike(['uid', 'number', 'price', 'status', 'user.first_name', 'user.last_name'], $search)->count();

            }

            $data = [];
            if ( ! empty($numbers)) {
                foreach ($numbers as $number) {
                    $show = route('admin.phone-numbers.show', $number->uid);

                    if ($number->status == 'available') {
                        $status = '<span class="badge badge-light-primary text-uppercase">' . __('locale.labels.available') . '</span>';
                    } else if ($number->status == 'assigned') {
                        $status = '<span class="badge badge-light-success text-uppercase">' . __('locale.labels.assigned') . '</span>';
                    } else {
                        $status = '<span class="badge badge-light-danger text-uppercase">' . __('locale.labels.expired') . '</span>';
                    }

                    if ($number->user->is_admin) {
                        $assign_to = $number->user->displayName();
                    } else {

                        $customer_profile = route('admin.customers.show', $number->user->uid);
                        $customer_name    = $number->user->displayName();
                        $assign_to        = "<a href='$customer_profile' class='text-primary mr-1'>$customer_name</a>";
                    }


                    $nestedData['responsive_id'] = '';
                    $nestedData['avatar']        = route('admin.customers.avatar', $number->user->uid);
                    $nestedData['created_at']    = __('locale.labels.created_at') . ': ' . Tool::formatDateTime($number->created_at);
                    $nestedData['email']         = $number->user->email;
                    $nestedData['uid']           = $number->uid;
                    $nestedData['number']        = $number->number;
                    $nestedData['user_id']       = $assign_to;
                    $nestedData['price']         = "<div>
                                                        <p class='text-bold-600'>" . Tool::format_price($number->price, $number->currency->format) . " </p>
                                                        <p class='text-muted'>" . $number->displayFrequencyTime() . "</p>
                                                   </div>";
                    $nestedData['status']        = $status;
                    $nestedData['capabilities']  = $number->getCapabilities();
                    $nestedData['edit']          = $show;
                    $nestedData['delete']        = $number->uid;

                    $data[] = $nestedData;

                }
            }

            $json_data = [
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => $totalData,
                "recordsFiltered" => $totalFiltered,
                "data"            => $data,
            ];

            echo json_encode($json_data);
            exit();

        }


        /**
         * @return Application|Factory|View
         * @throws AuthorizationException
         */

        public function create(): Factory|View|Application
        {
            $this->authorize('create phone_numbers');

            $breadcrumbs = [
                ['link' => url(config('app.admin_path') . "/dashboard"), 'name' => __('locale.menu.Dashboard')],
                ['link' => url(config('app.admin_path') . "/phone-numbers"), 'name' => __('locale.menu.Phone Numbers')],
                ['name' => __('locale.phone_numbers.add_new_number')],
            ];

            $customers  = User::where('status', true)->get();
            $currencies = Currency::where('status', true)->get();

            return view('admin.PhoneNumbers.create', compact('breadcrumbs', 'currencies', 'customers'));
        }


        /**
         * update phone number information
         *
         * @param PhoneNumbers $number
         *
         * @return Application|Factory|\Illuminate\Contracts\View\View|RedirectResponse
         * @throws AuthorizationException
         */

        public function show(PhoneNumbers $number): \Illuminate\Contracts\View\View|Factory|RedirectResponse|Application
        {
            $this->authorize('edit phone_numbers');

            $breadcrumbs = [
                ['link' => url(config('app.admin_path') . "/dashboard"), 'name' => __('locale.menu.Dashboard')],
                ['link' => url(config('app.admin_path') . "/phone-numbers"), 'name' => __('locale.menu.Phone Numbers')],
                ['name' => __('locale.phone_numbers.update_number')],
            ];


            $customers  = User::where('status', true)->get();
            $currencies = Currency::where('status', true)->get();

            $capabilities = json_decode($number->capabilities, true);

            if (isset($capabilities) && is_array($capabilities) && count($capabilities) > 0) {
                return view('admin.PhoneNumbers.show', compact('breadcrumbs', 'number', 'customers', 'currencies', 'capabilities'));
            }

            return redirect()->route('admin.phone-numbers.index')->with([
                'status'  => 'error',
                'message' => __('locale.phone_numbers.phone_number_capabilities_not_found'),
            ]);

        }


        /**
         * @param StoreNumber  $request
         * @param PhoneNumbers $numbers
         *
         * @return RedirectResponse
         */

        public function store(StoreNumber $request, PhoneNumbers $numbers): RedirectResponse
        {

            if (config('app.stage') == 'demo') {
                return redirect()->route('admin.phone-numbers.index')->with([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }


            $this->numbers->store($request->input(), $numbers::billingCycleValues());

            return redirect()->route('admin.phone-numbers.index')->with([
                'status'  => 'success',
                'message' => __('locale.phone_numbers.number_successfully_added'),
            ]);

        }


        /**
         * @param PhoneNumbers $phone_number
         * @param UpdateNumber $request
         *
         * @return RedirectResponse
         */

        public function update(PhoneNumbers $phone_number, UpdateNumber $request): RedirectResponse
        {

            if (config('app.stage') == 'demo') {
                return redirect()->route('admin.phone-numbers.index')->with([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            $this->numbers->update($phone_number, $request->except('_method', '_token'), $phone_number::billingCycleValues());

            return redirect()->route('admin.phone-numbers.index')->with([
                'status'  => 'success',
                'message' => __('locale.phone_numbers.number_successfully_updated'),
            ]);
        }


        /**
         * @param PhoneNumbers $phone_number
         *
         * @return JsonResponse Controller|JsonResponse
         *
         * @throws AuthorizationException
         */
        public function destroy(PhoneNumbers $phone_number): JsonResponse
        {

            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            $this->authorize('delete phone_numbers');

            $this->numbers->destroy($phone_number);

            return response()->json([
                'status'  => 'success',
                'message' => __('locale.phone_numbers.number_successfully_deleted'),
            ]);

        }

        /**
         * Bulk Action with Enable, Disable and Delete
         *
         * @param Request $request
         *
         * @return JsonResponse
         * @throws AuthorizationException
         */

        public function batchAction(Request $request): JsonResponse
        {
            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            $action = $request->get('action');
            $ids    = $request->get('ids');

            switch ($action) {

                case 'destroy':
                    $this->authorize('delete phone_numbers');

                    $this->numbers->batchDestroy($ids);

                    return response()->json([
                        'status'  => 'success',
                        'message' => __('locale.phone_numbers.phone_numbers_deleted'),
                    ]);

                case 'available':
                    $this->authorize('edit phone_numbers');

                    $this->numbers->batchAvailable($ids);

                    return response()->json([
                        'status'  => 'success',
                        'message' => __('locale.phone_numbers.phone_numbers_available'),
                    ]);

            }

            return response()->json([
                'status'  => 'error',
                'message' => __('locale.exceptions.invalid_action'),
            ]);

        }


        /**
         * @return Generator
         */

        public function phoneNumbersGenerator(): Generator
        {
            foreach (PhoneNumbers::cursor() as $number) {
                yield $number;
            }
        }

        /**
         * @return RedirectResponse|BinaryFileResponse
         * @throws AuthorizationException
         */
        public function export(): BinaryFileResponse|RedirectResponse
        {
            if (config('app.stage') == 'demo') {
                return redirect()->route('admin.phone-numbers.index')->with([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }


            $this->authorize('view phone_numbers');

            try {
                $file_name = (new FastExcel($this->phoneNumbersGenerator()))->export(storage_path('Phone_Numbers_' . time() . '.xlsx'));

                return response()->download($file_name);

            } catch (IOException|InvalidArgumentException|UnsupportedTypeException|WriterNotOpenedException $e) {
                return redirect()->route('admin.phone-numbers.index')->with([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ]);
            }

        }

    }
