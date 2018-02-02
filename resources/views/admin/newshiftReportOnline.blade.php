@if ($shift->end)
    <p>Завершена смена: {{ $driver }}</p>
@else
    <p>Предвариетлный расчет по смене {{ $driver }}</p>
@endif

    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
<div style="margin: 10px;">
    {!! Form::open(['url' => route('admin.newshifts.update',$shift->id), 'method' => 'put']) !!}
    {!! Form::hidden('type', 'counted') !!}
    {!! Form::submit('Пересчитать смену') !!}
    {!! Form::close() !!}
</div>
<div class="changeField">
    {!! Form::open(['url' => route('admin.newshifts.update',$shift->id), 'method' => 'put']) !!}
    {!! Form::hidden('type', 'shiftData') !!}
    <div>Пробег начала:  {!! Form::text('mileage_start',$shift->mileage_start) !!}</div>
    <div>Пробег конца:   {!! Form::text('mileage_end',$shift->mileage_end) !!}</div>
    <div>Топливо начала: {!! Form::text('fuel_start',$shift->fuel_start) !!}</div>
    <div>Заправка:       {!! Form::text('refill',$shift->refill) !!}</div>
    <div>Топливо конца:  {!! Form::text('fuel_end',$shift->fuel_end) !!}</div>
    <div>Заправка газа:  {!! Form::text('gas_refill',$shift->gas_refill) !!}</div>
    <div>Автомобиль:     {!! Form::select('car_id', $cars, $shift->car_id) !!}</div>
    <div>{!! Form::submit('Сохранить') !!}</div>
    {!! Form::close() !!}
</div>

<div>Начало смены: {{ $shiftStart }}</div>
<div>Конец смены (время для текущих смен): {{ $shiftEnd }}</div>

<div>Суммарно заработал: {{ round($totalSumm, 2) }}</div>
<div>Добавлено по пику: {{ $surgeFee }}</div>

<div>Количество поездок: {{ $tripsCount }}</div>
<div>Количество пропущеных поездок: {{ $shift->drop_order }}</div>
<div>Общее расстояние: {{ round($totalDistance,3) }}</div>
<div>Общая продолжительность поездок: {{ $totalDuration }} ({{$timePercentage}}% от {{ $shiftDuration }})</div>
<br>
<div> </div>

@if ( array_sum($statusDistance) != 0)
<table cellpadding="0" style="border-collapse: collapse;">
    <tr>
        <td style="border: 1px solid; padding: 5px;">Статус</td>
        <td style="border: 1px solid; padding: 5px;">Дистанция</td>
        <td style="border: 1px solid; padding: 5px;">%</td>
    </tr>
    <tr>
        <td style="border: 1px solid; padding: 5px; background-color: rgba(255, 0, 0, 0.5)">Ждет заказ</td>
        <td style="border: 1px solid; padding: 5px;">{{ $statusDistance['Open'] }}</td>
        <td style="border: 1px solid; padding: 5px;">{{ round(($statusDistance['Open'] * 100) / array_sum($statusDistance), 2)}}</td>
    </tr>
    <tr>
        <td style="border: 1px solid; padding: 5px; background-color: rgba(0, 18, 255, 0.5)">Предложен заказ</td>
        <td style="border: 1px solid; padding: 5px;">{{ $statusDistance['Dispatched'] }}</td>
        <td style="border: 1px solid; padding: 5px;">{{ round(($statusDistance['Dispatched'] * 100) / array_sum($statusDistance), 2)}}</td>
    </tr>
    <tr>
        <td style="border: 1px solid; padding: 5px; background-color: rgba(255, 240, 0, 0.5)">Едет к заказу</td>
        <td style="border: 1px solid; padding: 5px;">{{ $statusDistance['Accepted'] }}</td>
        <td style="border: 1px solid; padding: 5px;">{{ round(($statusDistance['Accepted'] * 100) / array_sum($statusDistance), 2)}}</td>
    </tr>
    <tr>
        <td style="border: 1px solid; padding: 5px; background-color: rgba(255, 150, 0, 0.5)">Прибыл к заказу</td>
        <td style="border: 1px solid; padding: 5px;">{{ $statusDistance['Arrived'] }}</td>
        <td style="border: 1px solid; padding: 5px;">{{ round(($statusDistance['Arrived'] * 100) / array_sum($statusDistance), 2)}}</td>
    </tr>
    <tr>
        <td style="border: 1px solid; padding: 5px; background-color: rgba(32, 206, 94, 0.5)">Выполняет заказ</td>
        <td style="border: 1px solid; padding: 5px;">{{ $statusDistance['DrivingClient'] }}</td>
        <td style="border: 1px solid; padding: 5px;">{{ round(($statusDistance['DrivingClient'] * 100) / array_sum($statusDistance), 2)}}</td>
    </tr>
    <tr>
        <td style="border: 1px solid; padding: 5px; background-color: rgba(0, 0, 0, 0.5)">Офлайн</td>
        <td style="border: 1px solid; padding: 5px;">{{ $statusDistance['Offline'] }}</td>
        <td style="border: 1px solid; padding: 5px;">{{ round(($statusDistance['Offline'] * 100) / array_sum($statusDistance), 2)}}</td>
    </tr>
    <tr>
        <td style="border: 1px solid; padding: 5px;">Всего</td>
        <td style="border: 1px solid; padding: 5px;">{{ array_sum($statusDistance) }}</td>
        <td style="border: 1px solid; padding: 5px;">{{ 100 }}</td>
    </tr>
</table>

<br>
@endif

@if ($surges)
    <p>Задел следующие пиковые времена:</p>
    @foreach ($surges as $daySurges)
        @foreach ($daySurges as $surge)
            {{trans('days.' . $surge['start']->formatLocalized('%a'))}} {{ $surge['start']->format('H:i')}}-{{ $surge['end']->format('H:i')}}
            <div>Онлайн за пиковое время: {{ $surge['onlineTime'] }}</div>
            <div>Должен был совершить поездок: {{ $surge['expectedTrips'] }}</div>
            <div>Поездок за пиковое время: {{ count($surge['trips']) }}</div>
            <div>Условие сработало: {{ $surge['tripCondition'] ? 'Да' : 'Нет' }}</div>
            <div>Денег заработал: {{ $surge['tripsFee'] }}</div>
            @if ($surge['tripCondition'])
                <div>Денег должен был заработать: {{ $surge['expectedFee'] }}</div>
                <div>Бонус: {{ $surge['bonus'] }}</div>
            @endif
            <div>Офлайн в этот промежуток: {{ $surge['offlineTime'] }}</div>
            @if ( $surge['offline'])
                <table cellpadding="0" style="border-collapse: collapse;">
                    <tr>
                        <td style="border: 1px solid; padding: 5px;">id</td>
                        <td style="border: 1px solid; padding: 5px;">Начало</td>
                        <td style="border: 1px solid; padding: 5px;">Конец</td>
                        <td style="border: 1px solid; padding: 5px;">Продолжительность</td>
                        <td style="border: 1px solid; padding: 5px;">Расстояние</td>
                    </tr>
                    @foreach ($surge['offline'] as $off)
                        <tr data-trip-id='{{ $off->end}}'>
                            <td style="border: 1px solid; padding: 5px;">{{ $off->id }}</td>
                            <td style="border: 1px solid; padding: 5px;">{{ $off->start }}</td>
                            <td style="border: 1px solid; padding: 5px;">{{ $off->end }}</td>
                            <td style="border: 1px solid; padding: 5px;">{{ gmdate("H:i:s", $off->offlineDuration) }}</td>
                            <td style="border: 1px solid; padding: 5px;">{{ $off->distance }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        @endforeach
    @endforeach
@endif
</br>
                </br>
<p>Был офлайн в не время пиков: {{ $offlineTotalTime }}</p>
@if (!$offlineTime->isEmpty())
    <table cellpadding="0" style="border-collapse: collapse;" class="table-hover" id="offline-trips-no-surge">
        <tr>
            <td style="border: 1px solid; padding: 5px;">id</td>
            <td style="border: 1px solid; padding: 5px;">Начало</td>
            <td style="border: 1px solid; padding: 5px;">Конец</td>
            <td style="border: 1px solid; padding: 5px;">Продолжительность</td>
            <td style="border: 1px solid; padding: 5px;">Расстояние</td>
        </tr>
        @foreach ($offlineTime as $off)
            <tr>
                <td style="border: 1px solid; padding: 5px;">{{ $off->id }}</td>
                <td style="border: 1px solid; padding: 5px;">{{ $off->start }}</td>
                <td style="border: 1px solid; padding: 5px;">{{ $off->end }}</td>
                <td style="border: 1px solid; padding: 5px;">{{ gmdate("H:i:s", $off->offlineDuration) }}</td>
                <td style="border: 1px solid; padding: 5px;">{{ $off->distance }}</td>
            </tr>
        @endforeach
    </table>
@endif
</br>
    </br>
<p>Список всех поездок:</p>
<table cellpadding="0" style="border-collapse: collapse;" class="table-hover" id="trips">
    <tr>
        <td style="border: 1px solid; padding: 5px;">№ П/П</td>
        <td style="border: 1px solid; padding: 5px;">Начало</td>
        <td style="border: 1px solid; padding: 5px;">Конец</td>
        <td style="border: 1px solid; padding: 5px;">Время</td>
        <td style="border: 1px solid; padding: 5px;">Км</td>
        <td style="border: 1px solid; padding: 5px;">Тариф</td>
    </tr>
    @foreach ($trips as $trip)
        <tr data-trip-id = '{{$trip->trip_id}}'>
            <td style="border: 1px solid; padding: 5px;">{{$num++}}</td>
            <td style="border: 1px solid; padding: 5px;">{!! \Carbon\Carbon::parse($trip->date)->timezone('Europe/Minsk') !!}</td>
            <td style="border: 1px solid; padding: 5px;">{!! \Carbon\Carbon::parse($trip->date)->timezone('Europe/Minsk')->addSeconds( $trip->duration ) !!}</td>
            <td style="border: 1px solid; padding: 5px;">{{ gmdate("H:i:s",$trip->duration) }}</td>
            <td style="border: 1px solid; padding: 5px;">{!! $trip->distance * 1.60934 !!}</td>
            <td style="border: 1px solid; padding: 5px;">{!! round($trip->total,2) !!}</td>
        </tr>
    @endforeach
</table>
<br/>
<p>Список всех пропущеных заказов:</p>
<table cellpadding="0" style="border-collapse: collapse;" class="table-hover" id="trips">
    <thead>
    <tr>
        <td style="border: 1px solid; padding: 5px;">Начало</td>
        <td style="border: 1px solid; padding: 5px;">Конец</td>
        <td style="border: 1px solid; padding: 5px;">Продолжительность</td>
        <td style="border: 1px solid; padding: 5px;">На какой статус сменился</td>
    </tr>
    </thead>
    @foreach ($dropOrders as $dropOrder)
        <tr data-trip-id='{{ $dropOrder['id'] }}'>
            <td style="border: 1px solid; padding: 5px;">{!! $dropOrder['startStatus'] !!}</td>
            <td style="border: 1px solid; padding: 5px;">{!! $dropOrder['endStatus'] !!}</td>
            <td style="border: 1px solid; padding: 5px;">{{ gmdate("H:i:s",$dropOrder['startStatus']->diffInSeconds($dropOrder['endStatus'])) }}</td>
            <td style="border: 1px solid; padding: 5px;">{!! $dropOrder['status'] !!}</td>
        </tr>
    @endforeach
</table>
<script>
    $('#shifts').DataTable({
        bFilter: true,
        bSortClasses: false,
        bSearchable: true,
        bSort: true,
        "aaSorting": [[ 1, "desc" ]]
    });

    var highlighted = false;
    var trip_highlighted = 0;
    var marker = null;
    var id = {{ $shift->id }}

    $.ajax({
        url: "/admin/newshifts",
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            id: id
        },
        success: function (data) {
            console.log('success!!');
            statuses = JSON.parse(data);
            draw();
        },
        error: function (request, status, error) {
            console.log('error!!');
            console.log(request.responseText);
        }
    });

    $('#trips tbody tr').click(function (e) {
        e.stopPropagation();

        var el = $(this);

        shiftPath.map(function(index, value){
            setOpacity(index, value, 0.3);
        });

        lines= $.grep(shiftPath, function(e){ return e.trip_id == el.attr('data-trip-id'); });
        $.each(lines, function(index, line){
            line.setOptions({strokeWeight: 7, 'zIndex': 100, strokeOpacity: 1});
        });
        if(trip_highlighted == el.attr('data-trip-id')){
            $.each(lines, function(index, line){
                line.setOptions({strokeWeight: 5, 'zIndex': 0});
            });

            shiftPath.map(function(index, value){
                setOpacity(index, value, 1);
            });

            trip_highlighted = 0;
        } else {
            trip_highlighted = el.attr('data-trip-id');
        }
    });

    $('#dropOrder tbody tr').click(function(e){
        e.stopPropagation();
        if(marker != null)
            marker.setMap(null);

        shiftPath.map(function(index, value){
            setOpacity(index, value, 0.3);
        });

        var el = $(this);

        var lat = el.attr('latitude').replace(/,/,"."),
                lng = el.attr('longitude').replace(/,/,".");

        var myLatLng = {lat: Number.parseFloat(lat), lng: Number.parseFloat(lng)};

        marker = addMarker(myLatLng);
    });

    $('body').click(function () {
        shiftPath.map(function(index, value){
            setOpacity(index, value, 1);
        });
        trip_highlighted = 0;

        marker.setMap(null);
    });

    function setOpacity(value, index, opacity){
        value.setOptions({strokeOpacity: opacity});
    }
</script>
