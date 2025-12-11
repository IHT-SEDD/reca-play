<?php

namespace App\Http\Controllers\MyProfile;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\Support\ResponseHelperService;

class MyProfileController extends Controller
{
    // ============================================================
    // Init services
    // ============================================================
    protected ResponseHelperService $responseHelperService;

    public function __construct(
        ResponseHelperService $responseHelperService
    ) {
        $this->responseHelperService = $responseHelperService;
    }

    // ============================================================
    // Main View
    // ============================================================
    public function index()
    {
        return view('pages.my-profile.index');
    }

    // ============================================================
    // Retrieve User Data
    // ============================================================
    public function userData()
    {
        try {
            $user = User::with(['venue', 'role'])->where('id', Auth::id())->first();

            return $this->responseHelperService->successResponse(
                'User data retrieved successfully',
                encryptData($user)
            );
        } catch (\Throwable $th) {
            return $this->responseHelperService->errorResponse(
                encryptData($th->getMessage()),
            );
        }
    }

    public function uploadPhoto(Request $request, $id)
    {
             try {

                $request->validate(
                [
                    'photo' => 'required|image|mimes:jpg,jpeg,png|max:1024',
                ],
                [
                    'photo.max' => 'photo profile must not be greater than 1024 kilobytes.',
                    'photo.mines' => 'photo profile mus be a jpg, jpeg or png.'
                ]
                );

                $user = User::findOrFail($id);

                if (Storage::disk('public')->exists('profile/'. $user->photo_profile)) {
                    // unlink($oldPath);
                        Storage::disk('public')->delete('profile/'. $user->photo_profile);
                }

                $filename = uniqid() . '.' . $request->photo->extension();
                $request->photo->storeAs('/profile', $filename);

                $user->photo_profile = $filename;
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Photo updated successfully.',
                    'url' => asset('storage/profile/' . $filename),
                ]);

            } catch (\Illuminate\Validation\ValidationException $e) {
                // Jika validasi gagal
                return response()->json([
                    'success' => false,
                    'message' => $e->validator->errors()->first(),
                ], 422);

            } catch (\Exception $e) {
                // Error general lainnya
                return response()->json([
                    'success' => false,
                    $e->getMessage()
                ], 500);
            }
    }

    public function update(Request $request)
    {
            try{

                  $request->validate(
                        [
                            'name' => 'required|min:3',
                            'username' => 'required|min:3',
                             'phone_number' => 'nullable|digits_between:10,15',
                        ],
                        [
                            'name.required' => 'name cannot be empty.',
                            'name.min' => 'name minimum is 3 characters.',
                            'username.required' => 'username cannot be empty.',
                            'username.min' => 'username minimum is 3 characters.',
                            'phone_number.integer' => 'phone number must be a number.',
                            'phone_number.digits_between' => 'phone number must be between 10 adn 15 .',
                        ]
                    );


                $data = $request->except(['id', '_token']);
                $user = User::find($request->id);

                if(is_null($user)){
                        return response()->json([
                        'status' => 'error',
                        'message' => 'User not found.'
                    ]);
                }

                $user->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully.'
            ]);

           } catch (\Illuminate\Validation\ValidationException $e) {
                // Jika validasi gagal
                return response()->json([
                    'success' => false,
                    'errors' => $e->validator->errors(),
                    'status' => 422
                ], 422);

            } catch (\Exception $e) {
                // Error general lainnya
                return response()->json([
                    'success' => false,
                    'errors' => $e->getMessage()
                ], 500);
            }
    }
}
