<div class="col-md-6">
    <div class="form-group">
        <label>@lang('app.multiplier')</label>
        @php
            $multipliers = array_combine(\Aireset\HappyHour::$values['multiplier'], \Aireset\HappyHour::$values['multiplier']);
        @endphp
        {!! Form::select('multiplier', $multipliers, $edit ? $happyhour->multiplier : '', ['class' => 'form-control']) !!}
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label>@lang('app.wager')</label>
        @php
            $wagers = array_combine(\Aireset\HappyHour::$values['wager'], \Aireset\HappyHour::$values['wager']);
        @endphp
        {!! Form::select('wager', $wagers, $edit ? $happyhour->wager : '', ['class' => 'form-control']) !!}
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label>@lang('app.time')</label>
        @php
            $times = array_combine(\Aireset\HappyHour::$values['time'], \Aireset\HappyHour::$values['time']);
        @endphp
        {!! Form::select('time', \Aireset\HappyHour::$values['time'], $edit ? $happyhour->time : '', ['class' => 'form-control']) !!}
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label>@lang('app.status')</label>
        {!! Form::select('status', ['0' => __('app.disabled'), '1' => __('app.active')], $edit ? $happyhour->status : 1, ['id' => 'status', 'class' => 'form-control']) !!}
    </div>
</div>
