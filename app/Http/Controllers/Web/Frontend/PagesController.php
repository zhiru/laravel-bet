<?php
namespace Aireset\Http\Controllers\Web\Frontend
{
    class PagesController extends \Aireset\Http\Controllers\Controller
    {
        public function new_license()
        {
            $licensed = false;
            $checked = new \Aireset\Lib\LicenseDK();
            $license_notifications_array = $checked->aplVerifyLicenseDK(null, 0);
            if( $license_notifications_array['notification_case'] == 'notification_license_ok' )
            {
                $licensed = true;
            }
            return view('system.pages.new_license', compact('licensed'));
        }
        public function new_license_post(\Illuminate\Http\Request $request)
        {
            $email = trim($request->email);
            $code = trim($request->code);
            file_put_contents(base_path() . '/' . config('LicenseDK.APL_LICENSE_FILE_LOCATION'), '');
            $checked = new \Aireset\Lib\LicenseDK();
            $license_notifications_array = $checked->aplInstallLicenseDK($request->getSchemeAndHttpHost(), $email, $code);
            if( $license_notifications_array['notification_case'] == 'notification_license_ok' )
            {
                return redirect()->back()->withSuccess(trans('app.license_is_already_installed'));
            }
            if( $license_notifications_array['notification_case'] == 'notification_already_installed' )
            {
                return redirect()->back()->withSuccess(trans('app.license_is_already_installed'));
            }
            return redirect()->back()->withErrors([$license_notifications_array['notification_text']]);
        }
        public function jpstv($id = 0)
        {
            return view('system.pages.jpstv', compact('id'));
        }
        public function jpstv_json(\Illuminate\Http\Request $request)
        {
            $jNames = [
                'diamond',
                'platinum',
                'gold',
                'silver',
                'bronze',
                'iron'
            ];
            $jCnt = 0;
            $res = [
                'status' => 'error',
                'content' => [],
                'i' => 1
            ];
            $data = \Aireset\JPG::where('shop_id', $request->id)->get();
            foreach( $data as $jackpot )
            {
                $res['content'][] = [
                    'name' => $jNames[$jCnt],
                    'jackpot' => $jackpot->balance,
                    'user' => ''
                ];
                $jCnt++;
                if( $jCnt > 5 )
                {
                    break;
                }
            }
            return json_encode($res);
        }
        public function error_license()
        {
            return view('system.pages.error_license');
        }
    }

}
