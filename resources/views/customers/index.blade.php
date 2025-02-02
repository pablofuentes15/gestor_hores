@extends('layout')

@section('title', __('message.control_panel')." - ". __('message.customers'))

@section('content')
@if ($message = Session::get('success'))

<div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong>{{ $message }}</strong>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
<div class="row py-2">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>{{ __('message.customers') }}</h2>
        </div>
    </div>
</div>


@if ($errors->any())
<div class="alert alert-danger mt-3">
    @php
    $show_create_edit = true
    @endphp
    <strong>{{__('message.woops!')}}</strong> {{__('message.input_problems')}}<br><br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ ucfirst($error) }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="px-2 py-3 create_edit_container">
    <div id="addEditHeader" class="d-inline-flex align-content-stretch align-items-center ml-3">
        
        <h3 class="d-inline-block m-0">{{ ($customer_to_edit == null) ? __('message.add_new')." ".__('message.customer') : __('message.edit')." ".__('message.customer') }}</h3>
        <i class="bi bi-chevron-down px-2 bi bi-chevron-down fa-lg" id="addEditChevronDown"></i>
    </div>

    <div id="addEditContainer">
        <div class="alert alert-info m-2 mt-3">
            <strong>{{__('message.fields_are_required')}}</strong>
        </div>
        
        <form action="{{ ($customer_to_edit == null) ? route('customers.store',$lang) : route('customers.update',[$customer_to_edit->id, $lang]) }}" method="POST" class="px-3 pt-2">
            @csrf

            <div class="row">

                <div class="form-group col-xs-12 col-sm-6 col-md-3 form_group_new_edit">  
                    <label for="newEditName">{{ __('message.name') }}:</label>
                    <input id="newEditName" type="text" name="name" class="form-control" placeholder="{{__('message.enter')}} {{ __('message.name') }}" value="{{ ($customer_to_edit == null) ? old('name') : old('name', $customer_to_edit->name) }}">
                </div>

                <div class="form-group col-xs-12 col-sm-6 col-md-3 form_group_new_edit">
                    <label for="newEditEmail">{{ __('message.email') }}:</label>
                    <input id="newEditEmail" type="text" name="email" class="form-control" placeholder="{{__('message.enter')}} {{ __('message.email') }}" value="{{ ($customer_to_edit == null) ? old('email') : old('email', $customer_to_edit->email) }}">
                </div>

                <div class="form-group col-xs-12 col-sm-6 col-md-3 form_group_new_edit">
                    <label for="newEditPhone">{{ __('message.phone') }}:</label>
                    <input id="newEditPhone" type="text" name="phone" class="form-control" placeholder="{{__('message.enter')}} {{ __('message.phone') }}" value="{{ ($customer_to_edit == null) ? old('phone') : old('phone', $customer_to_edit->phone) }}">
                </div>

                <div class="form-group col-xs-12 col-sm-6 col-md-3 form_group_new_edit">
                    <label for="newEditTaxNumber">{{__('message.tax_number')}}:</label>
                    <input id="newEditTaxNumber" type="text" name="tax_number" class="form-control" placeholder="{{__('message.enter')}} {{__('message.tax_number')}}" value="{{ ($customer_to_edit == null) ? old('tax_number') : old('tax_number', $customer_to_edit->tax_number) }}">
                </div>

                <div class="form-group col-xs-12 col-sm-6 col-md-3 form_group_new_edit">
                    <label for="newEditContactPerson">{{ __('message.contact_person') }}:</label>
                    <input id="newEditContactPerson" type="text" name="contact_person" class="form-control" placeholder="{{__('message.enter')}} {{ __('message.contact_person') }}" value="{{ ($customer_to_edit == null) ? old('contact_person') : old('contact_person', $customer_to_edit->contact_person) }}">
                </div>

                <div class="form-group col-xs-12 col-sm-8 col-md-5 form_group_new_edit">
                    <label for="newEditDesc">{{__('message.observations')}}:</label>
                    <textarea id="newEditDesc" class="form-control" name="description" placeholder="{{__('message.enter')." ".__('message.observations')}}">{{ ($customer_to_edit == null) ? old('description') : old('description', $customer_to_edit->description) }}</textarea>
                </div>

                <div class="form-group d-flex justify-content-end col-12 pr-0 mb-0">
                    @if ($customer_to_edit !== null)
                    <a class="btn general_button mr-0" href="{{route('customers.cancel_edit',$lang)}}">{{__('message.cancel')}}</a>
                    @endif
                    <button type="submit" class="btn general_button mr-2">{{ ($customer_to_edit == null) ? __('message.save') : __('message.update')}}</button>

                </div>
            </div>
        </form>
    </div>

</div>

<div id="filterDiv" class="p-4 my-3">
    <div class="mb-4" id="filterTitleContainer">
        <div class="d-flex align-content-stretch align-items-center">
            <h3 class="d-inline-block m-0">{{ __('message.filters') }}</h3><i class=" px-2 bi bi-chevron-down fa-lg" id="filterChevronDown"></i>
        </div>
    </div>
    <div id="filtersContainer">
        <form action="{{ route($lang.'_customers.index') }}" method="GET" class="row"> 
            @csrf

            <div class="form-group col-xs-12 col-sm-6 col-md-3">  
                <label for="filterName">{{ __('message.name') }}:</label>
                <input id="filterName" type="text" name="name" class="form-control" placeholder="{{__('message.enter')}} {{ __('message.name') }}" value="@if(session('customer_name') != '%'){{session('customer_name')}}@endif">
            </div>

            <div class="form-group col-xs-12 col-sm-6 col-md-3">
                <label for="filterEmail">{{ __('message.email') }}:</label>
                <input id="filterEmail" type="text" name="email" class="form-control" placeholder="{{__('message.enter')}} {{ __('message.email') }}" value="@if(session('customer_email') != '%'){{session('customer_email')}}@endif">
            </div>

            <div class="form-group col-xs-12 col-sm-6 col-md-3">
                <label for="filterPhone">{{ __('message.phone') }}:</label>
                <input id="filterPhone" type="text" name="phone" class="form-control" placeholder="{{__('message.enter')}} {{ __('message.phone') }}" value="@if(session('customer_phone') != '%'){{session('customer_phone')}}@endif">
            </div>

            <div class="form-group col-xs-12 col-sm-6 col-md-3">
                <label for="filterTaxNumber">{{__('message.tax_number')}}:</label>
                <input id="filterTaxNumber" type="text" name="tax_number" class="form-control" placeholder="{{__('message.enter')}} {{__('message.tax_number')}}" value="@if(session('customer_tax_number') != '%'){{session('customer_tax_number')}}@endif">
            </div>

            <div class="form-group col-xs-12 col-sm-6 col-md-3">
                <label for="filterContactPerson">{{ __('message.contact_person') }}:</label>
                <input id="filterContactPerson" type="text" name="contact_person" class="form-control" placeholder="{{__('message.enter')}} {{ __('message.contact_person') }}" value="@if(session('customer_contact_person') != '%'){{session('customer_contact_person')}}@endif">
            </div>

            <div class="col-xs-6 col-sm-6 col-md-6 form-group align-self-end">

                <button type="button" class="btn m-0" id="datePopoverBtn" data-placement="top">{{ __('message.date_creation_interval') }}</button>

                <div class="popover fade bs-popover-top show invisible" id="datePopover" role="tooltip" style="position: absolute; transform: translate3d(-51px, -186px, 0px); top: 0px; left: 0px;" x-placement="top">
                    <div class="arrow" style="left: 114px;"></div>
                    <div class="popover-body">
                        <div class="row">
                            <div class="col-12">
                                <button type="button" class="close" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <div class="form-group mt-2">
                                    <label for="filterDateFrom">{{ __('message.from') }}:</label>
                                    <input id="filterDateFrom" autocomplete="off" name="date_from" type="text" class="datepicker form-control form-control-sm" value="@if(session('customer_date_from') != ''){{session('customer_date_from')}}@endif">
                                </div>
                                <div class="form-group">
                                    <label for="filterDateTo">{{ __('message.to') }}:</label><br>
                                    <input id="filterDateTo" autocomplete="off" type="text" name="date_to" class="datepicker form-control form-control-sm" value="@if(session('customer_date_to') != ''){{session('customer_date_to')}}@endif">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="form-group d-flex justify-content-end mb-0 col-12">
                <a href="{{ route('customers.delete_filters', $lang) }}" class="btn general_button mr-0 mb-2">{{ __('message.delete_all_filters') }}</a>
                <button type="submit" class="btn general_button mr-0 mb-2">{{ __('message.filter') }}</button>
            </div>

        </form>
    </div>
</div>

<div class="row py-2">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h3>{{ __('message.customers_list') }}</h3>
        </div>
    </div>
</div>

<table class="table">
    @if (count($data) > 0)
    <thead>
        <tr class="thead-light">
            <th>Nº</th>
            <th>{{ __('message.name') }}</th>
            <th>{{ __('message.email') }}</th>
            <th>{{ __('message.phone') }}</th>
            <th>{{ __('message.observations') }}</th>
            <th>{{ __('message.tax_number') }}</th>
            <th>{{ __('message.contact_person') }}</th>
            <th>{{ __('message.created_at') }}</th>
            <th></th>
        </tr>
    </thead>
    @endif
    <tbody>
        @forelse ($data as $value)
        <tr>
            <td>{{ ++$i }}</td>
            <td>{{ $value->name }}</td>
            <td>{{ $value->email }}</td>
            <td>{{ $value->phone }}</td>
            <td>@if ($value->description == ''){{ __('message.no_description') }} @else {{ \Str::limit($value->description, 100) }} @endif</td>
            <td>{{ $value->tax_number }}</td>
            <td>{{ $value->contact_person }}</td>
            <td>{{ $value->created_at->format('d/m/y') }}</td>
            <td class="align-middle">
                <div class="validate_btns_container d-flex align-items-stretch justify-content-around">
                    
                    @php
                    $form_id = "editForm".$value->id;
                    $form_dom = "document.getElementById('editForm".$value->id."').submit();";
                    @endphp

                    <form action="{{ route($lang.'_customers.index') }}" method="GET" class="invisible" id="{{ $form_id }}"> 
                        @csrf
                        <input type="hidden" name="customer_id" value="{{ $value->id }}">
                    </form>

                    <a style="text-decoration: none" class="text-dark">
                        <i onclick="{{ $form_dom }}" class="bi bi-pencil-fill fa-lg"></i>
                    </a>
                    
                    
                    @php
                    $id = "exampleModal".$value->id;
                    @endphp

                    <a href="#{{$id}}" data-toggle="modal" data-target="#{{$id}}" style="text-decoration: none" class="text-dark">
                        <i class="bi bi-trash-fill fa-lg"></i>
                    </a>

                    <!-- Modal -->
                    <div class="modal fade" id="{{$id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <form action="{{ route('customers.destroy',[$value->id, $lang]) }}" method="POST"> 
                            @csrf
                            @method('DELETE')  

                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">{{ __('message.delete') }} {{ $value->name }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        {{ __('message.confirm') }} {{ __('message.delete') }} {{ __('message.the') }} {{ __("message.customer") }} <b>{{ $value->name }}</b>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('message.close') }}</button>
                                        <button type="submit" class="btn btn-success">{{ __('message.delete') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>   
                    
                </div>
               
            </td>
        </tr>
        @empty
    <li>{{__('message.no')}} {{__('message.customers')}} {{__('message.to_show')}}</li>
    @endforelse
</tbody>
</table> 
@if (count($data) > 0)
<form action="{{ route('customers.change_num_records', $lang) }}" method="GET"> 
    @csrf
    <div class="form-group d-flex align-items-center">
        <strong>{{ __('message.number_of_records') }}:</strong>
        <select name="num_records" id="numRecords" onchange="this.form.submit()" class="form-control form-select ml-2">
            <option value="10">10</option>
            <option value="50" @if(session('customers_num_records') == 50){{'selected'}}@endif>50</option>
            <option value="100" @if(session('customers_num_records') == 100){{'selected'}}@endif>100</option>
            <option value="all" @if(session('customers_num_records') == 'all'){{'selected'}}@endif>{{ __('message.all') }}</option>
        </select>
    </div>
</form>
@endif
<div id="paginationContainer">
    {!! $data->links() !!} 
</div>
@endsection
@section('js')
<script>
    
    var addEditCount = 1;
    $("#addEditHeader").click(function () {

        if (addEditCount % 2 == 0)
            $('#addEditChevronDown').css("transform", "rotate(0deg)");

        else
            $('#addEditChevronDown').css("transform", "rotate(180deg)");

        addEditCount++;

        // show hide paragraph on button click
        $("#addEditContainer").toggle(400);
    });
    
    var filterCount = 1;
    $("#filterTitleContainer").click(function () {

        if (filterCount % 2 == 0)
            $('#filterChevronDown').css("transform", "rotate(0deg)");

        else
            $('#filterChevronDown').css("transform", "rotate(180deg)");

        filterCount++;

        // show hide paragraph on button click
        $("#filtersContainer").toggle(400);
    });


    //Code for show filters when filter
    var show_create_edit = @json($show_create_edit);
    var show_filters = @json($show_filters);
    
    if (show_create_edit){
        $('#addEditChevronDown').css("transform", "rotate(180deg)");
        $('#addEditContainer').show(400);
        addEditCount = 2;
    }
    
    if (show_filters) {
        $('#filterChevronDown').css("transform", "rotate(180deg)");
        $('#filtersContainer').show(400);
        filterCount = 2;
    }
</script>
<script type="text/javascript" src="{{ URL::asset('js/customer_index.js') }}"></script>
@endsection