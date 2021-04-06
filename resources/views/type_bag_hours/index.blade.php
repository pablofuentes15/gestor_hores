@extends('layout')

@section('title', 'Control panel - Type bag hours')

@section('content')
<form action="{{ route('type-bag-hours.index') }}" method="POST"> 
    @csrf
    
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Name:</strong>
                <input type="text" name="name" class="form-control" placeholder="Enter Name">
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-success">Filter</button>
</form>
<div class="row py-2">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Bag hours types</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-success" href="{{ route('type-bag-hours.create') }}">Create New Bag hour type</a>
        </div>
    </div>
</div>
<table class="table table-bordered">
    <tr>
        <th>No</th>
        <th>Name</th>
        <th>Hour price</th>
        <th>Details</th>
        <th width="280px">Action</th>
    </tr>
    @foreach ($data as $key => $value)
    <tr>
        <td>{{ ++$i }}</td>
        <td>{{ $value->name }}</td>
        <td>{{ $value->hour_price }}€</td>
        <td>{{ \Str::limit($value->description, 100) }}</td>
        <td>
            <form action="{{ route('type-bag-hours.destroy',$value->id) }}" method="POST"> 
                <a class="btn btn-primary" href="{{ route('type-bag-hours.edit',$value->id) }}">Edit</a>
                @csrf
                @method('DELETE')      
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table> 
<div id="paginationContainer">
    {!! $data->links() !!} 
</div>
@endsection