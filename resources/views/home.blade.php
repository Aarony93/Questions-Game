@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">

        <div class="col-sm-12 col-md-6 ">
            <div class="card text-center">
                <a target="_blank" href="{{ route('game') }}" class="text-decoration-none text-success">
                    <div class="card-header">
                        <h2 class="p-5"> Start a new game! </h2>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-sm-12 col-md-6">
            <div class="card">
                <div class="card-header">Score board</div>

                <div class="card-body">
                    @if ($games)
                        <?php $total = 0; ?>
                        @foreach ($games as $game)

                            <section class="row mt-1">
                                <div class="col-6 text-center text-primary">
                                    {{$game->title}}
                                </div>
                                <div class="col-6 text-center text-success">
                                    {{$game->points_user}} points out of {{$game->points_total}}
                                </div>
                            </section>

                            <?php $total += $game->points_user; ?>
                        @endforeach
                    @else
                        No records!
                    @endif

                    <hr/>

                    <section class="row total-row mt-2">
                        <div class="col-6 text-center text-primary">
                            <b>Total</b>
                        </div>
                        <div class="col-6 text-center text-success">
                            <b>{{$total}} points</b>
                        </div>
                    </section>

                </div>

            </div>
        </div>


    </div>
</div>
@endsection
