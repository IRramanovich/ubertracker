@extends('admin.layout')
@section('title')Смены@endsection
@section('content')
        <div class="col-xs-12 col-md-push-6 col-md-6 top-buffer">
            @include('admin.map-online')
        </div>
        <div class="col-xs-12 col-md-pull-6 col-md-6 top-buffer">
            <div class="col-lg-12">
                <div class="row">
                    <div class="pull-left" >
                        {!! Form::open(['url' => route('admin.shifts.index'), 'method' => 'get']) !!}
                        <span>С: </span>{!! Form::text('from', $timeFrom, ['id' => 'datefrom_update']) !!}
                        <span>По: </span>{!! Form::text('to', $timeTo, ['id' => 'dateto_update']) !!}
                        {!! Form::submit('Отобразить') !!}
                        {!! Form::close() !!}
                        {{--<div class="row">--}}
                            {{--<div class="pull-left" >--}}
                                {{--{!! Form::open(['url' => route('report'), 'method' => 'get']) !!}--}}
                                {{--<span>С: </span>{!! Form::text('from', 'from', ['id' => 'datefrom_download']) !!}--}}
                                {{--<span>По: </span>{!! Form::text('to', 'to', ['id' => 'dateto_download']) !!}--}}
                                {{--{!! Form::submit('Скачать отчет') !!}--}}
                                {{--{!! Form::close() !!}--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    </div>
                </div>
                <table class="table table-striped table-bordered table-hover table-left-frame table-hover" id="shifts">
                    <thead>
                    <tr>
                        <th>Водитель</th>
                        <th>Начало смены</th>
                        <th>Конец смены</th>
                        <th>Длина смены</th>
                        <th>Офлайн в пик</th>
                        <th>Офлайн в не пик</th>
                        <th>Проп.</th>
                        <th>Суммарно заработал</th>
                        <th>Добавлено по пику</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if ($shifts)
                        @foreach ($shifts as $one)
                            <tr data-link='{{ route('admin.shifts.show', $one->id) }}'>
                                <td>{{$one->driver->getName()}}</td>
                                <td>{{$one->start}}</td>
                                @if($one->end)
                                <td>{{$one->end}}</td>
                                <td>{{ gmdate("H:i", $one->start->diffInSeconds($one->end)) }}</td>
                                <td>{{ gmdate("H:i", $one->offline_surge) }}</td>
                                <td>{{ gmdate("H:i", $one->offline_not_surge) }}</td>
                                <td>{{ $one->drop_order }}</td>
                                <td>{{ round($one->total/0.75, 2) }}</td>
                                <td>{{ round($one->surge/0.75, 2) }}</td>
                                @else
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                @endif
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
        <script>
            $('#shifts').DataTable({
                bFilter: true,
                bSortClasses: false,
                bSearchable: true,
                bSort: true,
                "aaSorting": [[ 1, "desc" ]],
            });

            $('#shifts tbody').on('click', 'tr', function () {
                window.location = this.getAttribute('data-link');
            } );

            $('#shifts tbody').on('contextmenu', 'tr',function(event) {
                event.preventDefault();
                var url = this.getAttribute('data-link');
                var win = window.open(url, '_blank');
                win.focus();
            });
        </script>
@endsection