@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">

        <div class="col-sm-12 col-md-12 ">

            <h2 class="mb-3">
                <b>Millionaires Game</b>
                <span class="">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sunglasses" viewBox="0 0 16 16">
                        <path d="M3 5a2 2 0 0 0-2 2v.5H.5a.5.5 0 0 0 0 1H1V9a2 2 0 0 0 2 2h1a3 3 0 0 0 3-3 1 1 0 1 1 2 0 3 3 0 0 0 3 3h1a2 2 0 0 0 2-2v-.5h.5a.5.5 0 0 0 0-1H15V7a2 2 0 0 0-2-2h-2a2 2 0 0 0-1.888 1.338A2 2 0 0 0 8 6a2 2 0 0 0-1.112.338A2 2 0 0 0 5 5zm0 1h.941c.264 0 .348.356.112.474l-.457.228a2 2 0 0 0-.894.894l-.228.457C2.356 8.289 2 8.205 2 7.94V7a1 1 0 0 1 1-1"/>
                    </svg>
                </span>
            </h2>

            <h4 class="user-points-wrapper mb-3">
                <b>{{  auth()->user()->name}}</b>'s points :
                {{-- <span id="user-points"> {{$game_points ? $game_points : 0}} </span> --}}
                <span id="user-points"> 0 </span>
            </h4>

            <div id="{{$game_id}}" class="card border-info game-card">
                    <div class="card-header border-info">

                        <div id="{{$question->question_id}}" class="d-flex justify-content-between question-part">
                            <strong class="text-info">Question <span class="qnum">{{$question_number}}</span> of 5</strong>
                            <strong class="text-info">Question Points : <span class="qpoints">{{$question->points_question}}</span> </strong>
                        </div>

                        <hr class="border-info" />

                        <h4 class="p-3 line-height-2"> <strong>Question:</strong> {{$question->body}} ? </h4>
                    </div>


                    <div class="card-body">
                        <ul class="">
                            @foreach ($question->answers as $answer)
                                <li class="list-unstyled mt-1">
                                    <div class="form-check">
                                        <label data-answer-id="{{$answer['id']}}" role="button" class="form-check-label">
                                            <input type="checkbox" class="form-check-input" value=""
                                                @if ( isset($correct_answered_ids) && $correct_answered_ids && in_array($answer['id'],$correct_answered_ids))
                                                    checked
                                                    disabled
                                                @endif
                                            >
                                            <h5>{{ $answer['body'] }}</h5>
                                        </label>
                                    </div>
                                </li>
                            @endforeach
                        </ul>


                        <hr class="border-info" />

                        <div class="text-center">
                            @if($game_finished_status)
                                <a id="next-q" href="{{ route('home') }}" class="text-decoration-none text-white btn btn-info disabled" > Go to home </a>
                            @else
                                <a id="next-q" href="{{ route('game') }}" class="text-decoration-none text-white btn btn-info disabled" > Go to next question</a>
                            @endif

                        </div>

                    </div>

            </div>
        </div>

    </div>
</div>
@endsection
