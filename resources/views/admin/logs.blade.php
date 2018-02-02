@extends('admin.layout')
@section('title')Логи@endsection
@section('content')
    {{--<div class="" id="file_list">--}}
        {{--<ul class="">--}}
            {{--@foreach($files_list as $file)--}}
                {{--<li>--}}
                    {{--<i class="icon-chevron-right"></i> {{$file}}--}}
                {{--</li>--}}
            {{--@endforeach--}}
        {{--</ul>--}}
    {{--</div>--}}
    {{--<div class="" id="file_listing">--}}
        {{--<h3>DaemonLog_Logs.log</h3>--}}
        {{--<table class="table-bordered">--}}
            {{--<thead>--}}
                {{--<tr>--}}
                    {{--<th>Date</th>--}}
                    {{--<th>Error code</th>--}}
                    {{--<th>Message</th>--}}
                    {{--<th>Driver</th>--}}
                    {{--<th>Start</th>--}}
                    {{--<th>ID</th>--}}
                {{--</tr>--}}
            {{--</thead>--}}
            {{--<tbody>--}}
            {{--@foreach($files['DaemonLog_Logs.log'] as $row)--}}
                {{--<tr>--}}
                    {{--<td>{{$row['date']}}</td>--}}
                    {{--<td>{{$row['data']->error_code}}</td>--}}
                    {{--<td>{{$row['data']->message}}</td>--}}
                    {{--<td>{{$row['responce']->driver_id}}</td>--}}
                    {{--<td>{{$row['responce']->start}}</td>--}}
                    {{--<td>{{$row['responce']->id}}</td>--}}
                {{--</tr>--}}
            {{--@endforeach--}}
            {{--</tbody>--}}
        {{--</table>--}}
    {{--</div>--}}

    {{--<div class="" id="file_listing">--}}
        {{--<h3>ShiftLog_Logs.log</h3>--}}
        {{--<table class="table-bordered">--}}
            {{--<thead>--}}
            {{--<tr>--}}
                {{--<th>Date</th>--}}
                {{--<th>Error code</th>--}}
                {{--<th>Message</th>--}}
                {{--<th>Driver</th>--}}
                {{--<th>Start</th>--}}
                {{--<th>ID</th>--}}
            {{--</tr>--}}
            {{--</thead>--}}
            {{--<tbody>--}}
            {{--@foreach($files['ShiftLog_Logs.log'] as $row)--}}
                {{--<tr>--}}
                    {{--<td>{{$row['date']}}</td>--}}
                    {{--<td>{{$row['data']->error_code}}</td>--}}
                    {{--<td>{{$row['data']->message}}</td>--}}
                    {{--<td>{{$row['responce']->driver_id}}</td>--}}
                    {{--<td>{{$row['responce']->start}}</td>--}}
                    {{--<td>{{$row['responce']->id}}</td>--}}
                {{--</tr>--}}
            {{--@endforeach--}}
            {{--</tbody>--}}
        {{--</table>--}}
    {{--</div>--}}
@endsection
