<?php

namespace App\Services\Master;

use Illuminate\Http\Request;

class MasterFormRequestService
{
  /**
   * Mapping type ke Form Request class
   */
  protected array $formRequests = [
    'role' => [
      'store' => \App\Http\Requests\Master\Role\StoreRoleRequest::class,
      'update' => \App\Http\Requests\Master\Role\UpdateRoleRequest::class
    ],
    'field' => [
      'store' => \App\Http\Requests\Master\Field\StoreFieldRequest::class,
      'update' => \App\Http\Requests\Master\Field\UpdateFieldRequest::class
    ],
    'category' => [
        'store' => \App\Http\Requests\Master\Category\StoreCategoryRequest::class,
        'update' => \App\Http\Requests\Master\Category\UpdateCategoryRequest::class
    ],
    'venue' => [
      'store' => \App\Http\Requests\Master\Venue\StoreVenueRequest::class,
      'update' => \App\Http\Requests\Master\Venue\UpdateVenueRequest::class,
    ],
  'venue-type' => [
    'store' => \App\Http\Requests\Master\Venue\StoreVenueTypeRequest::class,
    'update' => \App\Http\Requests\Master\Venue\UpdateVenueTypeRequest::class
   ],
  'camera' => [
    'store' => \App\Http\Requests\Master\Camera\StoreCameraRequest::class,
    'update' => \App\Http\Requests\Master\Camera\UpdateCameraRequest::class
  ],
  'nvr' => [
    'store' => \App\Http\Requests\Master\NVR\StoreNvrRequest::class,
    'update' => \App\Http\Requests\Master\NVR\UpdateNvrRequest::class
  ],
  'port' => [
    'store' => \App\Http\Requests\Master\Port\StorePortRequest::class,
    'update' => \App\Http\Requests\Master\Port\UpdatePortRequest::class
  ],
    'qr_code' => [
      'store' => \App\Http\Requests\Master\QrCode\StoreQrCodeRequest::class,
      'update' => \App\Http\Requests\Master\QrCode\UpdateQrCodeRequest::class,
    ],
    'api' => [
      'store' => \App\Http\Requests\Master\Api\StoreApiRequest::class,
      'update' => \App\Http\Requests\Master\Api\UpdateApiRequest::class
    ]
  ];

  /**
   * Get instance of Form Request and validate the request
   */
  public function getValidatedData(string $type, Request $request, string $mode): array
  {
    if (!isset($this->formRequests[$type][$mode])) {
      throw new \Exception("Form Request for {$type} not found");
    }

    $formRequestClass = $this->formRequests[$type][$mode];

    /** @var \Illuminate\Foundation\Http\FormRequest $formRequest */
    $formRequest = app($formRequestClass);
    // Merge request data and validate
    $formRequest->setContainer(app())
      ->setRedirector(app('redirect'))
      ->merge($request->all());

    return $formRequest->validated();
  }
}
