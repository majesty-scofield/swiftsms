@if($contact->cache)
    <div class="row match-height">

        <div class="col-lg-4 col-sm-6 col-12">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="fw-bolder mb-0">{{ $contact->readCache('TotalSubscribers') }}</h2>
                        <p class="card-text">{{ __('locale.labels.total') }}</p>
                    </div>

                    <div>
                        <i class="font-large-3 text-primary" data-feather="users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-sm-6 col-12">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="fw-bolder mb-0">{{ $contact->readCache('SubscribersCount') }}</h2>
                        <p class="card-text">{{ __('locale.contacts.active_contacts') }}</p>
                    </div>

                    <div>
                        <i class="font-large-3 text-success" data-feather="user-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-sm-6 col-12">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="fw-bolder mb-0">{{ $contact->readCache('UnsubscribesCount') }}</h2>
                        <p class="card-text">{{ __('locale.contacts.inactive_contacts') }}</p>
                    </div>
                    <div>
                        <i class="font-large-3 text-danger" data-feather="user-x"></i>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endif


<div id="datatables-basic">

    <div class="mb-3 mt-2">
        @can('view_contact')
            <div class="btn-group">
                <button
                        class="btn btn-primary fw-bold dropdown-toggle me-1"
                        type="button"
                        id="bulk_actions"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                >
                    {{ __('locale.labels.actions') }}
                </button>
                <div class="dropdown-menu" aria-labelledby="bulk_actions">
                    <a class="dropdown-item bulk-subscribe" href="#"><i
                                data-feather="check"></i> {{ __('locale.labels.subscribe') }}</a>
                    <a class="dropdown-item bulk-unsubscribe" href="#"><i
                                data-feather="stop-circle"></i> {{ __('locale.labels.unsubscribe') }}</a>
                    <a class="dropdown-item bulk-copy" href="#"><i
                                data-feather="copy"></i> {{ __('locale.buttons.copy') }}</a>
                    <a class="dropdown-item bulk-move" href="#"><i
                                data-feather="move"></i> {{ __('locale.buttons.move') }}</a>
                    <a class="dropdown-item bulk-delete" href="#"><i
                                data-feather="trash"></i> {{ __('locale.datatables.bulk_delete') }}</a>
                </div>
            </div>
        @endcan

        @can('create_contact')
            <div class="btn-group">
                <a href="{{route('customer.contact.create', $contact->uid)}}"
                   class="btn btn-success waves-light waves-effect fw-bold me-1"> {{__('locale.buttons.add_new')}} <i
                            data-feather="plus-circle"></i></a>
            </div>
        @endcan

        @can('view_contact')
            <div class="btn-group">
                <a href="{{ route('customer.contact.import', $contact->uid) }}"
                   class="btn btn-secondary waves-light waves-effect fw-bold me-1"> {{__('locale.buttons.import')}} <i
                            data-feather="upload"></i></a>
            </div>

            <div class="btn-group  me-1">
                <a href="{{ route('customer.contact.export', $contact->uid) }}"
                   class="btn btn-info waves-light waves-effect fw-bold"> {{__('locale.buttons.export')}} <i
                            data-feather="download"></i></a>
            </div>


            <div class="btn-group">
                <button
                        class="btn btn-outline-primary fw-bold dropdown-toggle"
                        type="button"
                        id="columns"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                >
                    {{ __('locale.labels.columns') }}
                </button>
                <div class="dropdown-menu" aria-labelledby="columns">
                    @php
                        $key = 6;
                    @endphp
                    @foreach ($contact->getFields as  $field)
                        @if ($field->tag != "PHONE")
                            <a class="dropdown-item toggle-vis" href="#" data-column="{{$key++}}"><i class="toggle-icon"
                                                                                                     data-feather="eye"></i> {{ $field->label }}
                            </a>
                        @endif
                    @endforeach

                </div>
            </div>
        @endcan


    </div>

    <div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <table class="table datatables-basic">
                    <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>{{ __('locale.labels.id') }}</th>
                        <th>{{__('locale.menu.Contacts')}}</th>
                        <th>{{__('locale.labels.updated_at')}}</th>
                        <th>{{__('locale.labels.status')}}</th>
                        @foreach ($contact->getFields as $key => $field)
                            @if ($field->tag != "PHONE")
                                <th>{{ $field->label }}</th>
                            @endif
                        @endforeach
                        <th>{{__('locale.labels.actions')}}</th>

                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>
