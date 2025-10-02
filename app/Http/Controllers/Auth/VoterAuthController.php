<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoterAuthController extends Controller
{
    public function __construct(private OtpService $otpService, private RecaptchaService $recaptcha)
    {
    }

    public function showLogin()
    {
        return view('voter.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'member_id' => 'required|string',
            'password' => 'required',
            'g-recaptcha-response' => 'nullable|string',
        ]);
        if (!$this->recaptcha->verify((string) $request->input('g-recaptcha-response', ''), 'voter_login')) {
            return back()->with('error', 'reCAPTCHA failed');
        }
        if (!Auth::attempt(['member_id' => $request->member_id, 'password' => $request->password, 'role' => 'voter'])) {
            return back()->with('error', 'Invalid credentials');
        }
        $request->session()->regenerate();
        session(['otp_verified' => false, 'face_verified' => false]);
        $this->otpService->generateAndSend(Auth::user(), 'login');
        return redirect()->route('voter.otp')->with('success', 'OTP sent');
    }

    public function showOtp()
    {
        return view('voter.auth.otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        /** @var User $user */
        $user = Auth::user();
        if ($user && $this->otpService->verify($user, $request->code, 'login')) {
            session(['otp_verified' => true]);
            return redirect()->route('voter.face');
        }
        return back()->with('error', 'Invalid code');
    }

    public function showFace()
    {
        return view('voter.auth.face');
    }

    public function verifyFace(Request $request)
    {
        $request->validate(['photo' => 'required|image|max:2048']);
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('voter.login');
        }
        // Simple face check: perceptual hash compare threshold
        $uploadedPath = $request->file('photo')->getRealPath();
        $hasher = new \Jenssegers\ImageHash\ImageHash(new \Jenssegers\ImageHash\Implementations\DifferenceHash());
        $uploadedHash = (string) $hasher->hash($uploadedPath);

        if (!$user->face_hash) {
            $user->face_hash = $uploadedHash;
            $user->save();
        }

        $distance = \Jenssegers\ImageHash\ImageHash::distance($uploadedHash, (string) $user->face_hash);
        if ($distance <= 10) { // loose threshold for demo
            session(['face_verified' => true]);
            return redirect()->route('voter.dashboard');
        }
        return back()->with('error', 'Face verification failed');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('voter.login');
    }
}
