<?php

namespace App\Services\UserManagement;

use App\Http\Requests\UserManagement\User\StoreUserRequest;
use Illuminate\Http\Request;

class UserFormRequestService
{
 public function getValidatedData(Request $request): array
 {
  /** @var \Illuminate\Foundation\Http\FormRequest $formRequest */
  $formRequest = app(StoreUserRequest::class);

  $formRequest->setContainer(app())
   ->setRedirector(app('redirect'))
   ->merge($request->all());

  return $formRequest->validated();
 }
}
