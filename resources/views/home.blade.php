@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">网站</div>

                <div class="card-body">
                    <div class="btn-group">
                        <a href="http://{{$_SERVER['HTTP_HOST']}}/block_news">
                        <button type="button" class="btn btn-default">行业新闻</button>
                        </a>
                        <a href="http://{{$_SERVER['HTTP_HOST']}}/we_message">
                        <button type="button" class="btn btn-default">微信消息</button>
                        </a>
                      </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
