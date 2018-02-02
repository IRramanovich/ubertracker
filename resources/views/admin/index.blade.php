@extends('admin.layout')
@section('content')
	<div>
		<div class="col-lg-12">
			<h1 class="page-header">Статусы</h1>
			<div class="row">
				<div class="pull-left" >
					{!! Form::open(['url' => route('report'), 'method' => 'get']) !!}
					<span>С: </span>{!! Form::text('from', 'from', ['id' => 'datefrom_download']) !!}
					<span>По: </span>{!! Form::text('to', 'to', ['id' => 'dateto_download']) !!}
					{!! Form::submit('Скачать отчет') !!}
					{!! Form::close() !!}
				</div>
			</div>
			<div class="row">
				<div class="pull-left" >
					{!! Form::open(['url' =>'/admin', 'method' => 'get']) !!}
					<span>С: </span>{!! Form::text('from', $timeFrom, ['id' => 'datefrom_update']) !!}
					<span>По: </span>{!! Form::text('to', $timeTo, ['id' => 'dateto_update']) !!}
					{!! Form::submit('Отобразить') !!}
					{!! Form::close() !!}
				</div>
			</div>
		</div>
		<div class="col-lg-12">
			<table class="table table-striped table-bordered table-hover" id="statuses">
				<thead>
					<tr>
						<th>Driver</th>
						<th>Latitude</th>
						<th>Longitude</th>
						<th>Course</th>
						<th>Status</th>
						<th>Date</th>
					</tr>
				</thead>
				<tbody>
				@if ($statuses)
					@foreach ($statuses as $one)
						<tr>
							<td>{{$one->driver->getName()}}</td>
							<td>{{$one->latitude}}</td>
							<td>{{$one['longitude']}}</td>
							<td>{{$one['course']}}</td>
							<td>{{$one['status']}}</td>
							<td>{{$one['date']}}</td>
						</tr>
					@endforeach
				@endif
				</tbody>
			</table>
		</div>
	</div>
@endsection