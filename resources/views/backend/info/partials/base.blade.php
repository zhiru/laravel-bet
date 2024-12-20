<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>@lang('app.href')</label>
            <div class="input-group">
                <span class="input-group-addon">/backend/</span>
                <input type="text" class="form-control" name="link" value="{{ $edit ? $info->link : '' }}">
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>@lang('app.role')</label>
            @php
                $allRoles = \Aireset\Role::where('slug', '!=', 'user')->where('id', '<=', auth()->user()->role_id)->pluck('name');
            @endphp
            <select name="roles[]" id="roles" class="form-control" >
                <option value="" >---</option>
                @foreach ($allRoles as $role)
                    <option value="{{ $role }}" {{ (in_array($role, $roles))? 'selected="selected"' : '' }}>{{ $role }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>@lang('app.title')</label>
            <input type="text" class="form-control" name="title" required value="{{ $edit ? $info->title : '' }}">
        </div>
        <div class="form-group">
            <label>@lang('app.text')</label>
            <textarea name="text" class="form-control" name="text" required value="{{ $edit ? $info->text : '' }}">{{ $edit ? $info->text : '' }}</textarea>
        </div>
    </div>

    <div class="col-md-6">
        <label>@lang('app.shops')</label>
        @php
            $allShops = auth()->user()->shops();
        @endphp
        <select name="shops[]" id="shops" class="form-control" >
            <option value="" >---</option>
            @if( count($allShops) )
                @foreach ($allShops as $key=>$name)
                    <option value="{{ $key }}"
                            {{ ($edit && $info->shops && in_array($key, $info->shops->pluck('shop_id')->toArray()))? 'selected="selected"' : '' }}>{{ $name }}</option>
                @endforeach
             @endif
        </select>
    </div>
</div>
