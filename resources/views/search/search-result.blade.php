@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-8 col-md-offset-2 text-center ">
      <br/>
        <form class="form" action="/search" method="get">
          <div class="input-group">
             <input name="q" type="text" class="form-control input-lg" placeholder="{{ trans('common.search_placeholder')}}">
             <span class="input-group-btn">
               <button class="btn btn-default btn-lg" type="button"><i class="fa fa-search fa-lg"></i></button>
             </span>
           </div><!-- /input-group -->
        </form>
    </div>
  </div>
  <hr>
    <div class="row">
        <div class="col-md-8">
          @include('artworks.thumbs-list',  array('number_of_columns' => 'col-md-4'))
        </div>
    </div>
</div>
@endsection
