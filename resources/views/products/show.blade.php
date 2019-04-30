<?php
    $p = new App\Helpers\Picker;
?>

@extends('../nav')

@section('content')

<section>
    <p></p>
    <div class="container">
        <div class="col-sm-5 cent">
          <div class="row text-left">
            <div class="card card-light form-card col-12 align-items-center">
            <div class="row ">
                
            <a class="pull-left" href="/"><h3 class="text-primary pull-left"><i class="fa fa-money" aria-hidden="true"></i> {{ $record->name }}</h3></a>
            </div>
               <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->errorCorrection('H')->size(180)->merge('/'.$record->img, .2)->margin(0)->generate($record->url)) !!} ">
                <strong>¥{{ $record->quota }}</strong> 
               <div class="row">
                <blockquote class="blockquote mb-5 text-left product-content">
                    <span class="badge badge-dark">芝麻分 ≥ {{ $record->zm }}</span>
                    <span class="badge badge-dark">实名手机号6个月以上</span><br>

                    @if($record->fs)
                        <span class="badge text-white water"><i class="fa fa-shower" aria-hidden="true"></i> 正在放水!</span>
                    @endif
                    @if($p->fresh($record->id))
                        <span class="badge text-white leaf"><i class="fa fa-leaf" aria-hidden="true"></i> 新品</span>
                    @endif
                    <br>
                    <small><i class="fa fa-rocket" aria-hidden="true"></i> 提现在请扫码, 或直接点下方链接</small><br>
                    <small><i class="fa fa-bell-o" aria-hidden="true"></i> 温馨提示: 借贷和风险防范属于您自身义务，务必谨慎。</small><br>

                    @if($record->org->code == 'rzd')
                    <p><small class="text-primary"><span class="badge badge-primary"><i class="fa fa-handshake-o" aria-hidden="true"></i> 报备产品</span> 下款率高于常规产品, 但每次申请都会在本平台生成订单, 您需要完成订单并按要求反馈, 否则无法申请其他同类产品; 不能接受的, 本平台将终止服务。</small></p>
                    @endif
                    
               </blockquote>
               </div>
            <a class="btn btn-primary btn-block text-white" href="#"><i class="fa fa-check-square-o" aria-hidden="true"></i> 我已知晓, 立即提现!</a>
            </div>
            
          </div>
        </div>
    </div>
</section>

@endsection