@extends('admin.layout')
@section('title')Смена@endsection
@section('content')
    <div class="col-lg-6">
        <div class="col-lg-12">
            @include('admin.newshiftReportOnline')
        </div>
    </div>
    <div class="col-lg-6">
        @include('admin.map-shift')
    </div>
@endsection