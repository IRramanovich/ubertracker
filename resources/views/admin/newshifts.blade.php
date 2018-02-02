@extends('admin.layout')
@section('title')Смены(new)@endsection
@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="row">
            <div class="pull-left" >
                {!! Form::open(['url' => route('admin.newshifts.index'), 'method' => 'get']) !!}
                <span>С: </span>{!! Form::text('from', $timeFrom, ['id' => 'datefrom']) !!}
                <span>По: </span>{!! Form::text('to', $timeTo, ['id' => 'dateto']) !!}
                {!! Form::hidden('type', 'interval') !!}
                {!! Form::submit('Отобразить') !!}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="col-lg-12 shiftTable">
        <table class="table table-striped table-bordered table-hover table-left-frame table-hover" id="newshifts">
            <thead>
            <tr>
                <th>Водитель</th>
                <th>Автомобиль</th>
                <th>Начало смены</th>
                <th>Конец смены</th>
                <th>Офлайн в пик</th>
                <th>Офлайн в не пик</th>
                <th>Проп.</th>
                <th>Пробег начала</th>
                <th>Пробег конца</th>
                <th>Топливо начала</th>
                <th>Заправка</th>
                <th>Топливо конца</th>
                <th>Заправка газа</th>
                <th>Поездок</th>
                <th>Минут, оплачиваемых</th>
                <th>Км, оплачиваемых</th>
                <th>Км в час</th>
                <th>Сумма оплаты, тариф</th>
                <th>Сумма оплаты, пик</th>
                <th>Сумма оплаты, бонус</th>
                <th>Процент пиковый</th>
                <th>Процент бонус</th>
                <th>Длина смены</th>
                <th>Заказов за 12 часов</th>
                <th>Пробег</th>
                <th>Средняя длина заказа, км</th>
                <th>Средняя длина оплачиваемого заказа, км</th>
                <th>Доля холостого</th>
                <th>Израсходовано топлива</th>
                <th>Расход на 100 км</th>
                <th>Пробег по трекеру</th>
                <th>Доля пробега не по трекеру</th>
            </tr>
            </thead>
            <tbody>
            @if ($shifts)
                @foreach ($shifts as $one)
                    <tr data-link='{{ route('admin.newshifts.show', $one['id']) }}'>
                        <td>{{$one['driver']}}</td>
                        <td>{{$one['car']}}</td>
                        <td>{{$one['start']}}</td>
                        <td>{{$one['end']}}</td>
                        <td>{{gmdate("H:i:s", $one['offline_surge'])}}</td>
                        <td>{{gmdate("H:i:s", $one['offline_not_surge'])}}</td>
                        <td>{{$one['drop_order']}}</td>
                        <td>{{$one['mileage_start']}}</td>
                        <td>{{$one['mileage_end']}}</td>
                        <td>{{$one['fuel_start']}}</td>
                        <td>{{$one['refill']}}</td>
                        <td>{{$one['fuel_end']}}</td>
                        <td>{{$one['gas_refill']}}</td>
                        <td>{{$one['trips']}}</td>
                        <td>{{$one['duration']}}</td>
                        <td>{{$one['distance']}}</td>
                        <td>{{$one['km_per_hour']}}</td>
                        <td>{{$one['total']}}</td>
                        <td>{{$one['surge']}}</td>
                        <td>{{$one['bonus']}}</td>
                        <td>{{$one['percent_surge']}}%</td>
                        <td>{{$one['percent_bonus']}}%</td>
                        <td>{{gmdate("H:i:s",$one['shift_length'])}}</td>
                        <td>{{$one['order_by_day']}}</td>
                        <td>{{$one['mileage']}}</td>
                        <td>{{$one['middle_distance_order']}}</td>
                        <td>{{$one['middle_distance']}}</td>
                        <td>{{$one['proportion_singles']}}</td>
                        <td>{{$one['expenditure_fuel']}}</td>
                        <td>{{$one['expenditure_fuel_h']}}</td>
                        <td>{{$one['tracker_distance']}}</td>
                        <td>{{$one['tracker_distance_part']}}</td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
</div>
    <script>
        var table = $('#newshifts').DataTable({
            "paging": true,
//            bFilter: true,
//            bSortClasses: false,
//            bSearchable: true,
//            bSort: true,
            scrollx: 400,
//            "aaSorting": [[2, "desc"]],
//            "scrollX": true,
        });

        if(localStorage["currentPage"] != 0 && localStorage["currentPage"]){

            table.page(Number(localStorage["currentPage"])).draw('page');
            console.log( 'currentPage: '+localStorage["currentPage"] );
        }

        $('#newshifts').on( 'page.dt', function () {
            var info = table.page.info();
            localStorage.setItem("currentPage", info.page);
            console.log( 'Showing page: '+info.page+' of '+info.pages );
        } );


        $('#newshifts tbody').on('click', 'tr', function () {
            window.location = this.getAttribute('data-link');
        } );

        $( function() {
            $( "#datefrom" ).datepicker({
                dateFormat: "dd-mm-yy",
                onSelect: function(date) {
                },
            });
            $( "#dateto" ).datepicker({
                dateFormat: "dd-mm-yy",
                onSelect: function(date) {
                },
            });
        });
    </script>
@endsection