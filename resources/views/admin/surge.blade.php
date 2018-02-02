@extends('admin.layout')
@section('title')Пиковые промежутки@endsection
@section('content')
	<div class="container">
		<div class="col-lg-12">
        {!! Form::open(array('url' => route( 'admin.surge.save' ), 'class' => 'surge-form')) !!}
            {!! Form::textarea('surge', $surge) !!}
            {!! Form::submit('Сохранить') !!}
        {!! Form::close() !!}
        </div>
        {{--@if($message)
             n,
        @endif--}}
    </div>
    <script>
        $('.surge-form').submit(function(e){
            $.post( $(".surge-form").attr("action"), $(this).serialize()
                ).done(function() {
                    $.notify("Сохранено", "success");
                }).fail(function(){
                        $.notify("Не сохранено", "error");
                });
            e.preventDefault();
        });

    </script>
@endsection