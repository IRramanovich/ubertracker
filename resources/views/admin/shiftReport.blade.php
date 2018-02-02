<p>Завершена смена {{ $driver }}</p>

<p>Начало смены: {{ $shiftStart }}</p>
<p>Конец смены: {{ $shiftEnd }}</p>
{{--Офлайн в пик: {{ $surgeOfflineTime }}
Офлайн в не пик: {{ $offlineTime }}--}}

<p>Суммарно заработал: {{ $totalSumm }}</p>
<p>Добавлено по пику: {{ $surgeFee }}</p>

<p>Количество поездок: {{ $tripsCount }}</p>
<p>Общее расстояние: {{ $totalDistance }}</p>
<p>Общая продолжительность поездок: {{ $totalDuration }} ({{$timePercentage}}% от {{ $shiftDuration }})</p>
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
<table cellpadding="0" style="border-collapse: collapse;">
    <tr>
        <td style="border: 1px solid; padding: 5px;">Начало</td>
        <td style="border: 1px solid; padding: 5px;">Конец</td>
        <td style="border: 1px solid; padding: 5px;">Продолжительность</td>
        <td style="border: 1px solid; padding: 5px;">Расстояние</td>
        <td style="border: 1px solid; padding: 5px;">Стоимость</td>
    </tr>
@foreach ($trips as $trip)
    <tr>
        <td style="border: 1px solid; padding: 5px;">{!! \Carbon\Carbon::parse($trip->date)->timezone('Europe/Minsk') !!}</td>
        <td style="border: 1px solid; padding: 5px;">{!! \Carbon\Carbon::parse($trip->date)->timezone('Europe/Minsk')->addSeconds( $trip->duration ) !!}</td>
        <td style="border: 1px solid; padding: 5px;">{{ gmdate("H:i:s",$trip->duration) }}</td>
        <td style="border: 1px solid; padding: 5px;">{!! $trip->distance * 1.6 !!}</td>
        <td style="border: 1px solid; padding: 5px;">{!! $trip->total !!}</td>
    </tr>
@endforeach
</table>
