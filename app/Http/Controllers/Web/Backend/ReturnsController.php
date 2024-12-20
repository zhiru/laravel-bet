<?php
namespace Aireset\Http\Controllers\Web\Backend
{
    class ReturnsController extends \Aireset\Http\Controllers\Controller
    {
        public function __construct()
        {
            $this->middleware('auth');
            $this->middleware('permission:access.admin.panel');
            $this->middleware('permission:returns.manage');
            $this->middleware('shopzero');
        }
        public function index(\Illuminate\Http\Request $request)
        {
/*            $checked = new \Aireset\Lib\LicenseDK();
            $license_notifications_array = $checked->aplVerifyLicenseDK(null, 0);
            if( $license_notifications_array['notification_case'] != 'notification_license_ok' )
            {
                return redirect()->route('frontend.page.error_license');
            }
            if( !$this->security() )
            {
                return redirect()->route('frontend.page.error_license');
            }*/
            $returns = \Aireset\Returns::where('shop_id', auth()->user()->shop_id)->get();
            return view('backend.returns.list', compact('returns'));
        }
        public function create()
        {
            return redirect()->route('backend.returns.list');
        }
        public function store(\Illuminate\Http\Request $request)
        {
            return redirect()->route('backend.returns.list');
            $data = $request->all();
            $request->validate(['percent' => 'required|in:' . implode(',', \Aireset\Returns::$values['percent'])]);
            $data['shop_id'] = auth()->user()->shop_id;
            $return = \Aireset\Returns::create($data);
            event(new \Aireset\Events\Returns\NewReturn($return));
            return redirect()->route('backend.returns.list')->withSuccess(trans('app.return_created'));
        }
        public function edit($returns)
        {
            $return = \Aireset\Returns::where('id', $returns)->first();
            if( !in_array($return->shop_id, auth()->user()->availableShops()) )
            {
                return redirect()->back()->withErrors([trans('app.wrong_shop')]);
            }
            return view('backend.returns.edit', compact('return'));
        }
        public function update(\Illuminate\Http\Request $request, \Aireset\Returns $return)
        {
            if( !in_array($return->shop_id, auth()->user()->availableShops()) )
            {
                return redirect()->back()->withErrors([trans('app.wrong_shop')]);
            }
            $request->validate(['percent' => 'required|in:' . implode(',', \Aireset\Returns::$values['percent'])]);
            $data = $request->only([
                'min_pay',
                'max_pay',
                'percent',
                'min_balance',
                'status'
            ]);
            $return->update($data);
            event(new \Aireset\Events\Returns\ReturnEdited($return));
            return redirect()->route('backend.returns.list')->withSuccess(trans('app.return_updated'));
        }
        public function delete(\Aireset\Returns $return)
        {
            return redirect()->route('backend.returns.list');
            if( !in_array($return->shop_id, auth()->user()->availableShops()) )
            {
                return redirect()->back()->withErrors([trans('app.wrong_shop')]);
            }
            event(new \Aireset\Events\Returns\DeleteReturn($return));
            \Aireset\Returns::where('id', $return->id)->delete();
            return redirect()->route('backend.returns.list')->withSuccess(trans('app.return_deleted'));
        }
/*        public function security()
        {
            if( config('LicenseDK.APL_INCLUDE_KEY_CONFIG') != 'wi9qydosuimsnls5zoe5q298evkhim0ughx1w16qybs2fhlcpn' )
            {
                return false;
            }
            if( md5_file(base_path() . '/app/Lib/LicenseDK.php') != '3c5aece202a4218a19ec8c209817a74e' )
            {
                return false;
            }
            if( md5_file(base_path() . '/config/LicenseDK.php') != '951a0e23768db0531ff539d246cb99cd' )
            {
                return false;
            }
            return true;
        }*/
    }

}
namespace
{
    function onkXppk3PRSZPackRnkDOJaZ9()
    {
        return 'OkBM2iHjbd6FHZjtvLpNHOc3lslbxTJP6cqXsMdE4evvckFTgS';
    }

}
