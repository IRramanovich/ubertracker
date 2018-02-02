@extends('admin.layout')
@section('title')Автомобили@endsection
@section('content')
    <div>
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {!! Form::open(['url' => route( 'admin.cars.create' ), 'method' => 'get']) !!}
        <div class="row">
            <div class="col-md-2">
                <div>{!! Form::label('gov_number', 'Гос. номер автомобиля:') !!}</div>
                <div>{!! Form::text('gov_number') !!}</div>
            </div>
            <div class="col-md-2">
                <div>{!! Form::label('model', 'Марка:') !!}</div>
                <div>{!! Form::text('model') !!}</div>
            </div>
            <div class="col-md-2">
                <div>{!! Form::label('year_productions', 'Год производства:') !!}</div>
                <div>{!! Form::selectRange('year_productions', 1980, 2016) !!}</div>
            </div>
            <div class="col-md-2">
                <div>{!! Form::label('buy_date', 'Дата покупки:') !!}</div>
                <div>{!! Form::date('buy_date', \Carbon\Carbon::now()) !!}</div>
            </div>
            <div class="col-md-2">
                {!! Form::submit('Добавить автомобиль') !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <div class="row">
        @if(isset($page) && $page == 'index')
            {!! Form::open(['url' => route('admin.cars.store'), 'method' => 'post']) !!}
            {!! Form::submit('Показать заблокированые автомобили') !!}
            {!! Form::close() !!}
        @elseif(isset($page))
            {!! Form::open(['url' => route('admin.cars.index'), 'method' => 'get']) !!}
            {!! Form::submit('Пказать активные автомобили') !!}
            {!! Form::close() !!}
        @endif

    </div>
    <div>
        <table class="table table-striped table-bordered table-hover table-left-frame" id="cars">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Гос. номер</th>
                    <th>Марка</th>
                    <th>Год производства</th>
                    <th>Дата покупки</th>
                    <th>Статус</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
            @if($cars)
                @foreach($cars as $car)
                    <tr>
                        <td>{{$car->id}}</td>
                        <td>{{$car->car_gov_number}}</td>
                        <td>{{$car->car_model}}</td>
                        <td>{{$car->production_year}}</td>
                        <td>{{$car->buy_date}}</td>
                        <td>
                            @if($car->car_bloked)
                                Заблокирован
                            @else
                                Активен
                            @endif
                        </td>
                        <td>
                            {!! Form::open(['url' => route( 'admin.cars.update', $car->id), 'method' => 'put']) !!}
                            @if($car->car_bloked)
                                {!! Form::hidden('type', 'unblock_car') !!}
                                {!! Form::submit('Разблокировать автомобиль') !!}
                            @else
                                {!! Form::hidden('type', 'block_car') !!}
                                {!! Form::submit('Заблокировать автомобиль') !!}
                            @endif
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>

    <script>
        $('#cars').DataTable({
            bFilter: true,
            bSortClasses: false,
            bSearchable: true,
            bSort: true,
            "aaSorting": [[ 0, "asc" ]]
        });
    </script>
@endsection