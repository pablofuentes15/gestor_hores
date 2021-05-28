@extends('layout')

@section('title')
{{ __("message.control_panel") }} - {{ __('message.add_new')." ".__('message.time_entry') }}
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>{{ __('message.add_new')." ".__('message.time_entry') }}</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route($lang.'_time_entries.index') }}"> {{__('message.back')}}</a>
        </div>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger mt-3">
    <strong>{{__('message.woops!')}}</strong> {{__('message.input_problems')}}<br><br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="alert alert-info mt-2">
    <strong>{{__('message.fields_are_required')}}</strong>
</div>

<form action="{{ route('time_entries.store',$lang) }}" method="POST" id="timeEntriesForm">
    @csrf
    <div id="timeEntryContainer1">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>*{{ __('message.user') }}: </strong>
                @if (count($users_data) > 0)
                <select name="users[]">
                    @foreach($users_data as $user)
                    <option value="{{ $user->id }}">{{$user->nickname}} -> @if ($user->role == 'admin'){{__('message.admin')}} @else{{__('message.worker')}} @endif -> {{__('message.name')}}: {{ $user->name }} {{ $user->surname }}. {{__('message.email')}}: {{$user->email}}. @if (isset($user->phone)) {{__('message.phone')}}: {{$user->phone}}@endif</option>
                    @endforeach
                </select>
                @endif
                <a href="{{ route($lang."_users.create") }}" type="button" class="btn btn-primary btn-sm">{{ __('message.create') }} {{ __('message.user') }}</a>
            </div>

        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group" id="projectSelectContainer">
                <strong>*{{ __('message.project') }}: </strong>
                <a href="{{ route($lang."_projects.create") }}" type="button" class="btn btn-primary btn-sm">{{ __('message.create') }} {{ __('message.project') }}</a>
            </div>
        </div>



        <div class="col-xs-8 col-sm-8 col-md-8">
            <div class="form-group">
                <strong>*{{__('message.hours')}}:</strong>
                <input type="number" name="hours[]" class="form-control" placeholder="{{__('message.enter')." ".__('message.hours_worked')}}" value="{{old('hours')}}">
            </div>
        </div>


        <div class="col-xs-12 col-sm-12 col-md-12" id="validatedContainer">
            <div class="form-group">
                <strong>*{{ __('message.state') }}:</strong><br>
                <input type="radio" id="validated" name="validate[]" value="1" checked>
                <label for="validated">{{__('message.validated')}}</label><br>
                <input type="radio" id="invalidated" name="validate[]" value="0">
                <label for="invalidated">{{__('message.invalidated')}}</label><br>  
            </div>
        </div> 

        <input hidden name="time_entry_id" value="1">
        <a type="button" class="btn btn-outline-success btn-sm" id="btnEntry" onclick="addEntry(1)">+</a>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
        <button type="submit" class="btn btn-primary">{{__('message.submit')}}</button>
    </div>



</form>
@endsection


@section('js')
<script>
        
    var countEntries = 1;
    function addEntry(containerId) {
        countEntries++;
        let clone = document.getElementById('timeEntryContainer1').cloneNode(true);
        clone.setAttribute('id', 'timeEntryContainer'+countEntries);
       
        document.getElementById('timeEntryContainer'+containerId).after(clone);
        
        document.getElementById('btnEntry').setAttribute('onclick', 'addEntry('+countEntries+')');
        
        //document.getElementById('timeEntryContainer'+containerId).getElementsByName('button')[0].setAttribute('name', 'prueva');
        


    }


    /*var projectsInUser;
    function onChangeUser(users_info) {

        //Create the select of projects
        let projectSelectHtml = document.createElement("select");
        projectSelectHtml.name = "projects";
        projectSelectHtml.setAttribute("onchange", "onChangeProject()");

        //Get the projects of the users from the json
        let userId = document.getElementsByName('users')[0].value;
        let res = users_info.filter((item) => {
            return item.id == userId;
        });
        projectsInUser = res[0]['projects'];

        if (projectsInUser.length > 0) {
            for (project of projectsInUser) {
                let option = document.createElement("option");
                option.value = project.id;
                option.innerText = project.name + " (" + project.customer + ")";
                projectSelectHtml.appendChild(option);
            }
        } else {
            let option = document.createElement("option");
            option.value = "no_project";
            option.innerText = "No projects asigned to this user";
            projectSelectHtml.disabled = true;
            projectSelectHtml.appendChild(option);
        }

        if (document.getElementsByName('projects')[0] != null) {
            document.getElementById("projectSelectContainer").removeChild(document.getElementsByName('projects')[0]);
        }

        document.getElementById("projectSelectContainer").insertBefore(projectSelectHtml, document.getElementById("projectSelectContainer").getElementsByTagName("a")[0]);


    }

    function onChangeProject() {

//        let projectId = document.getElementsByName('projects')[0].value;
//        
//        if (projectsInUser.length > 0) {
//            let res = projectsInUser.filter((item) => {
//                return item.id == projectId;
//            });
//            let bagHourInProject = res[0]['bag_hour'];
//            
//            if(bagHourInProject == true){
//                let inputedHoursContainerHtml = document.createElement("div");
//                inputedHoursContainerHtml.id = 'inputedHoursContainer';
//                
//                let inputedHoursTitleHtml = document.createElement("strong");
//                inputedHoursTitleHtml.innerText = "*{{__('message.hours_imputed')}}:";
//                let inputedHoursInputHtml = document.createElement("input");
//                inputedHoursInputHtml.type = "name";
//                inputedHoursInputHtml.name = "hours_imputed";
//                inputedHoursContainerHtml.appendChild(inputedHoursTitleHtml);
//                inputedHoursContainerHtml.appendChild(inputedHoursInputHtml);
//                document.getElementById("projectSelectContainer").insertBefore(projectSelectHtml, document.getElementById("projectSelectContainer").getElementsByTagName("a")[0]);
//            }
//        }
//        

    }

   
    //Get the object from json
    var users_info = @json($users_info);
            //Charge the projects depending on users on load page
            onChangeUser(users_info);

    window.onload = function () {

        //Listener for onchange user
        document.getElementsByName('users')[0].addEventListener("change", function () {
            onChangeUser(users_info);
        });

    }
     * 
     */
</script>
@endsection
