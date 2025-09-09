<?php

namespace App\Services\Master;

use Illuminate\Http\Request;

class MasterFormRequestService
{
 /**
  * Mapping type ke Form Request class
  */
 protected array $formRequests = [
  'role' => \App\Http\Requests\Master\Role\StoreRoleRequest::class,
  'field' => \App\Http\Requests\Master\Field\StoreFieldRequest::class,
  'category' => \App\Http\Requests\Master\Category\StoreCategoryRequest::class,
  'venue' => \App\Http\Requests\Master\Venue\StoreVenueRequest::class,
  'venue-type' => \App\Http\Requests\Master\Venue\StoreVenueTypeRequest::class,
  'camera' => \App\Http\Requests\Master\Camera\StoreCameraRequest::class,
  'nvr' => \App\Http\Requests\Master\NVR\StoreNvrRequest::class,
  'qr_code' => \App\Http\Requests\Master\QrCode\StoreQrCodeRequest::class,
 ];

 /**
  * Get instance of Form Request and validate the request
  */
 public function getValidatedData(string $type, Request $request): array
 {
  if (!isset($this->formRequests[$type])) {
   throw new \Exception("Form Request for {$type} not found");
  }

  $formRequestClass = $this->formRequests[$type];

  /** @var \Illuminate\Foundation\Http\FormRequest $formRequest */
  $formRequest = app($formRequestClass);

  // Merge request data and validate
  $formRequest->setContainer(app())
   ->setRedirector(app('redirect'))
   ->merge($request->all());

  return $formRequest->validated();
 }
}
