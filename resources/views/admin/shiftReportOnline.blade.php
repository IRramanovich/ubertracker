@if ($shift->end)
    <p>Завершена смена {{ $driver }}</p>
@else
    <p>Предварительный расчет по смене {{ $driver }}</p>
@endif

<div>Начало смены: {{ $shiftStart }}</div>
<div>Конец смены(время для текущих смен): {{ $shiftEnd }}</div>

<div>Суммарно заработал: {{ round($totalSumm/0.75, 2) }}</div>
<div>Добавлено по пику: {{ $surgeFee }}</div>

<div>Количество поездок: {{ $tripsCount }}</div>
<div>Количество пропущеных поездок: {{ $shift->drop_order }}</div>
<div>Общее расстояние: {{ $totalDistance }}</div>
<div>Общая продолжительность поездок: {{ $totalDuration }} ({{$timePercentage}}% от {{ $shiftDuration }})</div>
<br>


@if ($surges)
    <p>Задел следующие пиковые времена:</p>
    @foreach ($surges as $daySurges)
        @foreach ($daySurges as $surge)
            {{ $surge['start']->formatLocalized('%a')}} {{ $surge['start']->format('H:i')}}-{{ $surge['end']->format('H:i')}}
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
                        <td style="border: 1px solid; padding: 5px;">Начало</td>
                        <td style="border: 1px solid; padding: 5px;">Конец</td>
                        <td style="border: 1px solid; padding: 5px;">Продолжительность</td>
                        <td style="border: 1px solid; padding: 5px;">Расстояние</td>
                    </tr>
                    @foreach ($surge['offline'] as $off)
                        <tr>
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

<p>Был офлайн в не время пиков: {{ $offlineTotalTime }}</p>
@if (!$offlineTime->isEmpty())
    <table cellpadding="0" style="border-collapse: collapse;">
        <tr>
            <td style="border: 1px solid; padding: 5px;">Начало</td>
            <td style="border: 1px solid; padding: 5px;">Конец</td>
            <td style="border: 1px solid; padding: 5px;">Продолжительность</td>
            <td style="border: 1px solid; padding: 5px;">Расстояние</td>
        </tr>
        @foreach ($offlineTime as $off)
            <tr>
                <td style="border: 1px solid; padding: 5px;">{{ $off->start }}</td>
                <td style="border: 1px solid; padding: 5px;">{{ $off->end }}</td>
                <td style="border: 1px solid; padding: 5px;">{{ gmdate("H:i:s", $off->offlineDuration) }}</td>
                <td style="border: 1px solid; padding: 5px;">{{ $off->distance }}</td>
            </tr>
        @endforeach
    </table>
@endif

<p>Список всех поездок:</p>
<table cellpadding="0" style="border-collapse: collapse;" class="table-hover" id="trips">
    <thead>
        <tr>
            <td style="border: 1px solid; padding: 5px;">Начало</td>
            <td style="border: 1px solid; padding: 5px;">Конец</td>
            <td style="border: 1px solid; padding: 5px;">Продолжительность</td>
            <td style="border: 1px solid; padding: 5px;">Расстояние</td>
            <td style="border: 1px solid; padding: 5px;">Стоимость</td>
        </tr>
    </thead>
    @foreach ($trips as $trip)
        <tr data-trip-id='{{ $trip->trip_id }}'>
            <td style="border: 1px solid; padding: 5px;">{!! \Carbon\Carbon::parse($trip->date)->timezone('Europe/Minsk') !!}</td>
            <td style="border: 1px solid; padding: 5px;">{!! \Carbon\Carbon::parse($trip->date)->timezone('Europe/Minsk')->addSeconds( $trip->duration ) !!}</td>
            <td style="border: 1px solid; padding: 5px;">{{ gmdate("H:i:s",$trip->duration) }}</td>
            <td style="border: 1px solid; padding: 5px;">{!! $trip->distance * 1.6 !!}</td>
            <td style="border: 1px solid; padding: 5px;">{!! round($trip->total/0.75,2) !!}</td>
        </tr>
    @endforeach
</table>
<br/>
@if($shift->drop_order > 0)
<p>Список всех пропущеных заказов:</p>
<table cellpadding="0" style="border-collapse: collapse;" class="table-hover" id="dropOrder">
    <thead>
    <tr>
        <td style="border: 1px solid; padding: 5px;">Начало</td>
        <td style="border: 1px solid; padding: 5px;">Конец</td>
        <td style="border: 1px solid; padding: 5px;">Продолжительность</td>
        <td style="border: 1px solid; padding: 5px;">На какой статус сменился</td>
    </tr>
    </thead>
    @foreach ($dropOrders as $dropOrder)
        <tr data-trip-id='{{ $dropOrder['id'] }}' longitude="{!! $dropOrder['longitude'] !!}" latitude="{!! $dropOrder['latitude'] !!}">
            <td style="border: 1px solid; padding: 5px;">{!! $dropOrder['startStatus'] !!}</td>
            <td style="border: 1px solid; padding: 5px;">{!! $dropOrder['endStatus'] !!}</td>
            <td style="border: 1px solid; padding: 5px;">{{ gmdate("H:i:s",$dropOrder['startStatus']->diffInSeconds($dropOrder['endStatus'])) }}</td>
            <td style="border: 1px solid; padding: 5px;">{!! $dropOrder['status'] !!}</td>
        </tr>
    @endforeach
</table>
@endif

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
